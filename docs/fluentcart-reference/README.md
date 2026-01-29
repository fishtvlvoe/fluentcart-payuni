# FluentCart / PayUNi 開發參考（統一資料夾）

本資料夾彙整 FluentCart 與 PayUNi 外掛開發相關文件，供開發時優先查閱。

**來源**：老魚資料庫 `05_外掛開發文件`（VT工作流/_Archive/舊項目/doc/老魚資料庫/05_外掛開發文件）

**使用方式**：當你要做 FluentCart、PayUNi for FluentCart、訂閱、金流、訂單、REST API 等相關開發時，請先來此資料夾找參考，不要等到被問才查。

---

▋ 資料夾結構

- **根目錄**：FluentCart Customers API.md、FluentCart Orders API.md、FluentCart Products API.md（API 摘要）
- **fluentcart.com_doc/**：FluentCart 官方文件整理（入門、資料庫、Hooks、REST API、金流整合等，約 130 個 .md）
- **payuni-fluentcart/**：PayUNi 與 FluentCart 轉換／架構分析
  - FluentCart付款方式架構分析.md
  - woomp轉FluentCart轉換策略.md
  - woomp外掛架構分析與FluentCart轉換方案.md

---

▋ 依主題快速對照

訂閱（Subscription）
- fluentcart.com_doc/hooks_actions_subscriptions-and-licenses.md
- fluentcart.com_doc/hooks_filters_customers-and-subscriptions.md
- fluentcart.com_doc/database_models_subscription.md、database_models_subscription-meta.md
- fluentcart.com_doc/restapi_operations_subscriptions_get-subscription.md
- fluentcart.com_doc/restapi_operations_subscriptions_list-subscriptions.md
- fluentcart.com_doc/restapi_operations_subscriptions_cancel-subscription.md
- fluentcart.com_doc/restapi_operations_subscriptions_reactivate-subscription.md
- 本專案另見：docs/SUBSCRIPTION-MANAGEMENT.md、docs/PAYUNI-TOKEN-API-REFERENCE.md

金流／付款方式（Payment）
- fluentcart.com_doc/payment-methods-integration.md
- fluentcart.com_doc/payment-methods-integration_payment_setting_fields.md
- fluentcart.com_doc/payment-methods-integration_quick-implementation.md
- fluentcart.com_doc/hooks_actions_payments-and-integrations.md
- payuni-fluentcart/FluentCart付款方式架構分析.md

訂單（Orders）
- FluentCart Orders API.md（根目錄）
- fluentcart.com_doc/database_models_order.md、order-item、order-transaction 等
- fluentcart.com_doc/restapi_operations_orders_*.md
- fluentcart.com_doc/hooks_filters_orders-and-payments.md、hooks_actions_orders.md

客戶（Customers）
- FluentCart Customers API.md（根目錄）
- fluentcart.com_doc/restapi_operations_customers_*.md
- fluentcart.com_doc/hooks_actions_customers-and-users.md

商品（Products）
- FluentCart Products API.md（根目錄）
- fluentcart.com_doc/database_models_product.md 等
- fluentcart.com_doc/restapi_operations_products_*.md

入門與架構
- fluentcart.com_doc/getting-started.md
- fluentcart.com_doc/guides.md、guides_frontend.md、guides_integrations.md
- fluentcart.com_doc/database_schema.md、database_query-builder.md
- fluentcart.com_doc/hooks.md、hooks_actions.md、hooks_filters.md
- fluentcart.com_doc/restapi.md、modules.md

---

▋ 本專案其他文件（與此參考並用）

- docs/SUBSCRIPTION-MANAGEMENT.md：訂閱管理說明（取消按鈕位置、改日期／改方案規劃）
- docs/PAYUNI-TOKEN-API-REFERENCE.md：PayUNi Token API、免跳轉、woomp 對照
