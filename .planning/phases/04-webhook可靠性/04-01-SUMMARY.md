---
phase: 04-webhook可靠性
plan: 01
status: completed
subsystem: webhook-infrastructure
tags: [database, deduplication, reliability, webhook]
completed: 2026-01-29

# Dependencies
requires:
  - phase: 00
    plan: 00
    reason: "基礎外掛架構"

provides:
  - artifact: "payuni_webhook_log 資料表"
    type: database-schema
    exports: [transaction_id, webhook_type, processed_at]
  - artifact: "WebhookDeduplicationService"
    type: service-class
    exports: [isProcessed, markProcessed, cleanup]
  - artifact: "Database::createTables()"
    type: migration-method
    exports: [資料表建立邏輯]

affects:
  - phase: 04
    plan: 02
    reason: "NotifyHandler 和 ReturnHandler 將使用去重服務"
  - phase: 04
    plan: 03
    reason: "排程任務將使用 cleanup() 清理舊記錄"

# Tech Stack
tech-stack:
  added:
    - "WordPress dbDelta (schema migration)"
    - "INSERT IGNORE pattern (deduplication)"
  patterns:
    - "Database versioning with wp_options"
    - "Activation hook table creation"
    - "Unique composite key (transaction_id + webhook_type)"

# Files
key-files:
  created:
    - path: "includes/class-database.php"
      purpose: "資料表 schema 定義和建立邏輯"
      exports: ["Database::createTables()", "Database::getWebhookLogTable()"]
    - path: "src/Services/WebhookDeduplicationService.php"
      purpose: "Webhook 去重服務"
      exports: ["isProcessed()", "markProcessed()", "cleanup()"]
  modified:
    - path: "fluentcart-payuni.php"
      changes: ["啟用 hook 呼叫 createTables()", "bootstrap 版本檢查邏輯"]

# Decisions
decisions:
  - id: DEDUP-01
    question: "如何確保 webhook 不重複處理？"
    decision: "使用資料庫 unique key (transaction_id + webhook_type)"
    alternatives:
      - option: "繼續使用 transient"
        rejected: "TTL 只有 10 分鐘，不可靠"
      - option: "使用 Redis/Memcached"
        rejected: "增加外部相依性"
    rationale: "資料庫可靠且無需額外相依性，24 小時 TTL 足夠應對重複通知"

  - id: DEDUP-02
    question: "是否需要記錄完整 payload？"
    decision: "只記錄 payload hash (SHA256)"
    alternatives:
      - option: "記錄完整 payload JSON"
        rejected: "浪費空間，不利於查詢"
      - option: "不記錄 payload 資訊"
        rejected: "無法識別相同 transaction 但不同 payload 的情況"
    rationale: "Hash 足夠識別不同 payload，且不會洩漏敏感資料"

  - id: DEDUP-03
    question: "如何處理舊記錄？"
    decision: "提供 cleanup() 方法，由排程任務呼叫"
    alternatives:
      - option: "自動清理（每次 markProcessed 時）"
        rejected: "影響 webhook 回應速度"
      - option: "不清理舊記錄"
        rejected: "資料表會無限增長"
    rationale: "排程清理不影響即時處理，且可控制執行頻率"

# Metrics
metrics:
  duration: "3 分鐘"
  tasks_completed: 3
  tasks_total: 3
  files_created: 2
  files_modified: 1
  commits: 3
  tests_added: 0
  test_coverage: "N/A（基礎設施，下階段整合測試）"
---

# Phase 04 Plan 01: Webhook 去重基礎設施 Summary

**One-liner:** 建立資料庫驅動的 webhook 去重機制，取代不可靠的 transient，確保 24 小時內不重複處理。

## What Was Built

### 1. Database Schema (payuni_webhook_log)

建立專用資料表記錄已處理的 webhook：

```sql
CREATE TABLE wp_payuni_webhook_log (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    transaction_id VARCHAR(64) NOT NULL,      -- FluentCart transaction UUID
    trade_no VARCHAR(64) DEFAULT NULL,        -- PayUNi TradeNo（除錯用）
    webhook_type VARCHAR(32) NOT NULL,        -- 'notify' 或 'return'
    processed_at DATETIME NOT NULL,           -- 處理時間（用於清理）
    payload_hash VARCHAR(64) NOT NULL,        -- SHA256 hash（識別不同 payload）
    PRIMARY KEY (id),
    UNIQUE KEY unique_transaction (transaction_id, webhook_type),
    KEY idx_processed_at (processed_at),
    KEY idx_trade_no (trade_no)
)
```

**設計重點：**
- **Unique constraint**: `(transaction_id, webhook_type)` 確保同一交易的同類型 webhook 只記錄一次
- **Indexes**: `processed_at` 用於快速清理，`trade_no` 用於除錯查詢
- **Idempotent**: 使用 `dbDelta()` 確保可重複執行（升級時安全）

### 2. WebhookDeduplicationService

提供三個核心方法：

#### isProcessed(string $transactionId, string $webhookType): bool
- 檢查指定 transaction + type 組合是否已處理
- 只查詢過去 24 小時的記錄
- 用於 webhook handler 入口判斷

#### markProcessed(...): bool
- 使用 `INSERT IGNORE` 標記為已處理
- 回傳 `true` 表示首次處理，`false` 表示重複（已存在）
- 記錄 `trade_no` 和 `payload_hash` 供除錯

#### cleanup(): int
- 刪除超過 24 小時的舊記錄
- 回傳刪除數量
- 供排程任務呼叫

### 3. Activation Hooks

**啟用時建立資料表：**
```php
function buygo_fc_payuni_activate(): void {
    // ... dependency check ...
    \FluentcartPayuni\Database::createTables();
    update_option('buygo_fc_payuni_db_version', BUYGO_FC_PAYUNI_VERSION);
}
```

**升級時自動更新 schema：**
```php
function buygo_fc_payuni_bootstrap(): void {
    // ... updater init ...
    $current_db_version = get_option('buygo_fc_payuni_db_version', '0.0.0');
    if (version_compare($current_db_version, BUYGO_FC_PAYUNI_VERSION, '<')) {
        \FluentcartPayuni\Database::createTables();
        update_option('buygo_fc_payuni_db_version', BUYGO_FC_PAYUNI_VERSION);
    }
}
```

## Implementation Details

### 去重邏輯

**為什麼需要 transaction_id + webhook_type 組合？**

同一筆交易可能收到兩類 webhook：
1. **NotifyURL** (`webhook_type='notify'`): PayUNi 伺服器到伺服器通知
2. **ReturnURL** (`webhook_type='return'`): 瀏覽器導回（可能被刷新）

兩者都需要獨立去重，但不互相干擾。

**為什麼記錄 payload_hash？**

防止這種情況：
- Transaction A 首次通知：Status=SUCCESS
- Transaction A 重複通知（PayUNi bug）：Status=FAILED

如果只看 `transaction_id`，第二次通知會被跳過，但 payload 不同可能表示狀態變化。

（註：目前實作先記錄 hash，未來若發現此情況可擴展邏輯。）

### 清理策略

**為什麼 TTL 設為 24 小時？**

- PayUNi 文件未說明重複通知的間隔，實測觀察到幾分鐘內的重複
- 24 小時覆蓋任何合理的重試間隔
- 避免資料表無限增長（每天清理一次）

**為什麼不自動清理？**

清理是 `DELETE` 操作，在高流量時可能影響效能。由排程任務控制執行時機更安全。

### 錯誤處理

所有錯誤透過 `Logger::warning()` 記錄，**不拋出例外**：
- `isProcessed()` 失敗 → 回傳 `false`（允許繼續處理）
- `markProcessed()` 失敗 → 回傳 `false` + 記錄錯誤
- `cleanup()` 失敗 → 回傳 `0` + 記錄錯誤

原則：**去重失敗不應阻斷 webhook 處理**（寧可重複處理也不要遺漏）。

## Testing Strategy

### Phase 1（本階段）：基礎驗證
- ✅ PHP 語法檢查通過
- ✅ Database class 可載入
- ✅ WebhookDeduplicationService 介面正確
- ✅ SQL schema 結構正確

### Phase 2（下階段）：整合測試
- [ ] NotifyHandler 使用去重服務
- [ ] ReturnHandler 使用去重服務
- [ ] 實際 webhook 測試（沙盒環境）
- [ ] 重複 webhook 驗證（手動觸發）

### Phase 3（未來）：單元測試
- [ ] WebhookDeduplicationService::isProcessed() 測試
- [ ] WebhookDeduplicationService::markProcessed() 測試
- [ ] WebhookDeduplicationService::cleanup() 測試
- [ ] 邊界案例（空參數、NULL、SQL injection 防護）

## Deviations from Plan

無 - 計畫完全依照執行。

## Risks & Mitigations

### Risk 1: 資料庫寫入效能
**風險：** 每個 webhook 都需要一次 `INSERT IGNORE`
**影響：** 高流量時可能增加資料庫負載
**緩解：**
- 使用 `INSERT IGNORE`（比 `SELECT` + `INSERT` 快）
- Unique key 確保寫入快速失敗
- 未來可考慮 Redis 快取（若效能成為瓶頸）

### Risk 2: 時鐘偏移
**風險：** 伺服器時鐘不同步可能影響 TTL 判斷
**影響：** 可能提前或延後清理記錄
**緩解：**
- 使用 `gmdate()` 確保 UTC 時間
- 24 小時 TTL 有足夠容錯空間

### Risk 3: 資料表升級
**風險：** 未來需要修改 schema（新增欄位等）
**影響：** 可能需要資料遷移
**緩解：**
- 使用 `dbDelta()` 支援增量更新
- 版本號記錄在 `wp_options` 可追蹤歷史
- 未來可新增 migration 邏輯

## Next Phase Readiness

### Blockers
無

### Dependencies for Next Plan
下階段 (04-02) 需要：
1. ✅ `WebhookDeduplicationService` 可用
2. ✅ `payuni_webhook_log` 資料表存在
3. ⏳ 了解現有 `NotifyHandler` 和 `ReturnHandler` 結構（已有 `@context`）

### Open Questions
無

### Recommendations
1. **立即行動：** 停用並重新啟用外掛，確認資料表建立成功
2. **下階段優先：** 整合到 `NotifyHandler::processNotify()` 的 deduplication 邏輯
3. **未來考慮：** 新增 WP-CLI 指令查詢 webhook log（`wp payuni webhook-log list`）

## Performance Notes

### 資料表成長估算
假設：
- 每日 100 筆交易
- 每筆 2 次 webhook (notify + return)
- 資料保留 24 小時

**資料量：** 200 筆/日 ≈ 6000 筆/月（清理後）

**空間需求：** 約 1-2 MB/月（可忽略）

### 查詢效能
- `isProcessed()`: 使用 unique key，O(log n) 查詢
- `markProcessed()`: INSERT IGNORE，O(1) 寫入
- `cleanup()`: 使用 `processed_at` index，O(log n) + O(m) 刪除

**結論：** 效能不是瓶頸，資料庫可輕鬆處理。

## Documentation Updates Needed

### 未來需要新增的文件
1. **管理員文件：** 如何查詢 webhook log（SQL 範例）
2. **開發者文件：** 如何在其他 handler 中使用去重服務
3. **故障排除：** Webhook 重複處理的診斷步驟

### API 文件
未來若開放 REST API 查詢 webhook log，需要文件化：
- `GET /wp-json/buygo-fc-payuni/v1/webhook-logs`
- 權限要求：`manage_options`

---

**Status:** ✅ Completed
**Next:** Phase 04 Plan 02 - 整合去重服務到 webhook handlers
