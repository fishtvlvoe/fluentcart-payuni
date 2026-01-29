<?php
/**
 * PayUNi User Guide Admin Page
 *
 * Provides a WordPress admin page for PayUNi user documentation,
 * including Quick Start guide, feature locations, FAQ, and troubleshooting.
 *
 * @package BuyGoFluentCart\PayUNi\Admin
 * @since 1.1.0
 */

namespace BuyGoFluentCart\PayUNi\Admin;

/**
 * UserGuidePage class.
 *
 * Handles PayUNi user guide page registration and rendering with sidebar navigation.
 */
class UserGuidePage
{
    /**
     * Page slug.
     *
     * @var string
     */
    private const PAGE_SLUG = 'payuni-user-guide';

    /**
     * Constructor.
     *
     * @param bool $registerHooks Whether to register WordPress hooks (default true).
     *                            Set to false for testing.
     */
    public function __construct(bool $registerHooks = true)
    {
        if ($registerHooks) {
            // Use priority 99 to ensure FluentCart menu exists first
            add_action('admin_menu', [$this, 'registerAdminPage'], 99);
            add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        }
    }

    /**
     * Register admin page under PayUNi menu.
     */
    public function registerAdminPage(): void
    {
        add_submenu_page(
            'payuni',
            __('PayUNi 使用指南', 'fluentcart-payuni'),
            __('PayUNi 使用指南', 'fluentcart-payuni'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'renderPage'],
            10  // Position after Dashboard (5) and Webhook Logs
        );
    }

    /**
     * Enqueue JavaScript and CSS assets.
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueueAssets(string $hook): void
    {
        // Only load on user guide page
        if (strpos($hook, self::PAGE_SLUG) === false) {
            return;
        }

        wp_enqueue_style(
            'payuni-user-guide',
            FLUENTCART_PAYUNI_PLUGIN_URL . 'assets/css/payuni-user-guide.css',
            [],
            FLUENTCART_PAYUNI_VERSION
        );

        wp_enqueue_script(
            'payuni-user-guide',
            FLUENTCART_PAYUNI_PLUGIN_URL . 'assets/js/payuni-user-guide.js',
            ['jquery'],
            FLUENTCART_PAYUNI_VERSION,
            true
        );

        // Localize script with section data
        wp_localize_script('payuni-user-guide', 'payuniUserGuide', [
            'sections' => [
                'quick-start' => __('快速開始', 'fluentcart-payuni'),
                'feature-locations' => __('功能位置', 'fluentcart-payuni'),
                'faq' => __('常見問題', 'fluentcart-payuni'),
                'troubleshooting' => __('疑難排解', 'fluentcart-payuni'),
            ],
        ]);
    }

    /**
     * Render admin page HTML.
     */
    public function renderPage(): void
    {
        ?>
        <div class="wrap payuni-user-guide">
            <h1><?php echo esc_html__('PayUNi 使用指南', 'fluentcart-payuni'); ?></h1>
            <div class="guide-container">
                <nav class="guide-sidebar">
                    <ul class="guide-nav">
                        <li><a href="#quick-start" class="active"><?php echo esc_html__('快速開始', 'fluentcart-payuni'); ?></a></li>
                        <li><a href="#feature-locations"><?php echo esc_html__('功能位置', 'fluentcart-payuni'); ?></a></li>
                        <li><a href="#faq"><?php echo esc_html__('常見問題', 'fluentcart-payuni'); ?></a></li>
                        <li><a href="#troubleshooting"><?php echo esc_html__('疑難排解', 'fluentcart-payuni'); ?></a></li>
                    </ul>
                </nav>
                <main class="guide-content">
                    <?php echo $this->renderQuickStartSection(); ?>
                    <?php echo $this->renderFeatureLocationsSection(); ?>
                    <?php echo $this->renderFAQSection(); ?>
                    <?php echo $this->renderTroubleshootingSection(); ?>
                </main>
            </div>
        </div>
        <?php
    }

    /**
     * Get navigation items for sidebar.
     *
     * @return array Array of navigation items with id and label.
     */
    public function getNavigationItems(): array
    {
        return [
            ['id' => 'quick-start', 'label' => __('快速開始', 'fluentcart-payuni')],
            ['id' => 'feature-locations', 'label' => __('功能位置', 'fluentcart-payuni')],
            ['id' => 'faq', 'label' => __('常見問題', 'fluentcart-payuni')],
            ['id' => 'troubleshooting', 'label' => __('疑難排解', 'fluentcart-payuni')],
        ];
    }

    /**
     * Render Quick Start section.
     *
     * @return string HTML content.
     */
    public function renderQuickStartSection(): string
    {
        ob_start();
        ?>
        <section id="quick-start" class="guide-section active">
            <h2><?php echo esc_html__('快速開始', 'fluentcart-payuni'); ?></h2>

            <h3><?php echo esc_html__('首次設定步驟', 'fluentcart-payuni'); ?></h3>
            <ol class="setup-steps">
                <li>
                    <strong><?php echo esc_html__('取得 PayUNi 商店代號 (MerID)', 'fluentcart-payuni'); ?></strong>
                    <p><?php echo esc_html__('登入 PayUNi 商店後台，在「API 串接」→「商店資訊」中複製您的商店代號。', 'fluentcart-payuni'); ?></p>
                </li>
                <li>
                    <strong><?php echo esc_html__('設定 Hash Key 和 Hash IV', 'fluentcart-payuni'); ?></strong>
                    <p><?php echo esc_html__('在同一頁面複製 Hash Key 和 Hash IV，前往 FluentCart 支付方式設定頁面貼上這些憑證。', 'fluentcart-payuni'); ?></p>
                </li>
                <li>
                    <strong><?php echo esc_html__('在 PayUNi 後台設定 Webhook URL', 'fluentcart-payuni'); ?></strong>
                    <p><?php echo esc_html__('將 NotifyURL 複製到 PayUNi 後台的 Webhook 設定中，確保付款通知能正確送達。', 'fluentcart-payuni'); ?></p>
                </li>
                <li>
                    <strong><?php echo esc_html__('測試交易', 'fluentcart-payuni'); ?></strong>
                    <p><?php echo esc_html__('使用測試環境建立一筆測試訂單，確認付款流程和訂單狀態更新正常運作。', 'fluentcart-payuni'); ?></p>
                </li>
            </ol>

            <h3><?php echo esc_html__('快速連結', 'fluentcart-payuni'); ?></h3>
            <div class="quick-links-grid">
                <a href="<?php echo esc_url(admin_url('admin.php?page=payuni-settings')); ?>" class="quick-link-card">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <div>
                        <strong><?php echo esc_html__('PayUNi 設定', 'fluentcart-payuni'); ?></strong>
                        <p><?php echo esc_html__('查看憑證狀態、Webhook URL', 'fluentcart-payuni'); ?></p>
                    </div>
                </a>

                <a href="<?php echo esc_url(admin_url('admin.php?page=payuni-webhook-logs')); ?>" class="quick-link-card">
                    <span class="dashicons dashicons-list-view"></span>
                    <div>
                        <strong><?php echo esc_html__('Webhook 記錄', 'fluentcart-payuni'); ?></strong>
                        <p><?php echo esc_html__('查看付款通知記錄', 'fluentcart-payuni'); ?></p>
                    </div>
                </a>

                <a href="<?php echo esc_url(admin_url('admin.php?page=payuni-dashboard')); ?>" class="quick-link-card">
                    <span class="dashicons dashicons-chart-bar"></span>
                    <div>
                        <strong><?php echo esc_html__('PayUNi Dashboard', 'fluentcart-payuni'); ?></strong>
                        <p><?php echo esc_html__('查看統計資料和圖表', 'fluentcart-payuni'); ?></p>
                    </div>
                </a>

                <a href="<?php echo esc_url(admin_url('admin.php?page=fluent-cart#/orders')); ?>" class="quick-link-card">
                    <span class="dashicons dashicons-cart"></span>
                    <div>
                        <strong><?php echo esc_html__('FluentCart 訂單', 'fluentcart-payuni'); ?></strong>
                        <p><?php echo esc_html__('管理訂單和交易', 'fluentcart-payuni'); ?></p>
                    </div>
                </a>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Feature Locations section.
     *
     * @return string HTML content.
     */
    public function renderFeatureLocationsSection(): string
    {
        ob_start();
        ?>
        <section id="feature-locations" class="guide-section">
            <h2><?php echo esc_html__('功能位置', 'fluentcart-payuni'); ?></h2>
            <p><?php echo esc_html__('以下是 PayUNi 各項功能在系統中的位置參考：', 'fluentcart-payuni'); ?></p>

            <table class="feature-table">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('功能名稱', 'fluentcart-payuni'); ?></th>
                        <th><?php echo esc_html__('位置路徑', 'fluentcart-payuni'); ?></th>
                        <th><?php echo esc_html__('說明', 'fluentcart-payuni'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong><?php echo esc_html__('訂單交易資訊', 'fluentcart-payuni'); ?></strong></td>
                        <td><?php echo esc_html__('FluentCart → 訂單 → 訂單詳情', 'fluentcart-payuni'); ?></td>
                        <td><?php echo esc_html__('在訂單詳情頁面可查看 PayUNi 交易資訊區塊，包含交易編號、付款方式、卡號等資料', 'fluentcart-payuni'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php echo esc_html__('訂閱續扣歷史', 'fluentcart-payuni'); ?></strong></td>
                        <td><?php echo esc_html__('FluentCart → 訂閱 → 訂閱詳情', 'fluentcart-payuni'); ?></td>
                        <td><?php echo esc_html__('在訂閱詳情頁面可查看 PayUNi 訂閱資訊區塊，包含續扣歷史、信用卡資訊、下次扣款日期', 'fluentcart-payuni'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php echo esc_html__('Webhook 記錄', 'fluentcart-payuni'); ?></strong></td>
                        <td><?php echo esc_html__('WordPress 後台 → FluentCart → Webhook 記錄', 'fluentcart-payuni'); ?></td>
                        <td><?php echo esc_html__('查看所有 PayUNi 發送的付款通知記錄，包含處理狀態、錯誤訊息等', 'fluentcart-payuni'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php echo esc_html__('Dashboard 統計', 'fluentcart-payuni'); ?></strong></td>
                        <td><?php echo esc_html__('WordPress 後台 → FluentCart → PayUNi Dashboard', 'fluentcart-payuni'); ?></td>
                        <td><?php echo esc_html__('查看支付方式分布、訂閱續扣成功率、最近 Webhook 事件等統計資料', 'fluentcart-payuni'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php echo esc_html__('憑證設定', 'fluentcart-payuni'); ?></strong></td>
                        <td><?php echo esc_html__('WordPress 後台 → FluentCart → 付款設定 → PayUNi', 'fluentcart-payuni'); ?></td>
                        <td><?php echo esc_html__('設定 PayUNi 商店代號、Hash Key、Hash IV 等憑證資料', 'fluentcart-payuni'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php echo esc_html__('憑證狀態檢視', 'fluentcart-payuni'); ?></strong></td>
                        <td><?php echo esc_html__('WordPress 後台 → FluentCart → PayUNi 設定', 'fluentcart-payuni'); ?></td>
                        <td><?php echo esc_html__('檢視目前憑證狀態、Webhook URL、測試連線等資訊', 'fluentcart-payuni'); ?></td>
                    </tr>
                </tbody>
            </table>

            <p class="description" style="margin-top: 20px;">
                <?php echo esc_html__('💡 提示：未來版本將加入功能截圖，讓您更容易找到這些功能位置。', 'fluentcart-payuni'); ?>
            </p>
        </section>
        <?php
        return ob_get_clean();
    }

    /**
     * Render FAQ section.
     *
     * @return string HTML content.
     */
    public function renderFAQSection(): string
    {
        ob_start();
        ?>
        <section id="faq" class="guide-section">
            <h2><?php echo esc_html__('常見問題', 'fluentcart-payuni'); ?></h2>

            <!-- Category 1: 金流設定 -->
            <div class="faq-category">
                <h3><?php echo esc_html__('金流設定', 'fluentcart-payuni'); ?></h3>
                <div class="faq-item">
                    <button class="faq-question"><?php echo esc_html__('如何獲取 PayUNi 商店代號 (MerID)?', 'fluentcart-payuni'); ?></button>
                    <div class="faq-answer">
                        <ol>
                            <li><?php echo sprintf(
                                esc_html__('登入 %s', 'fluentcart-payuni'),
                                '<a href="https://www.payuni.com.tw" target="_blank">PayUNi 商戶後台</a>'
                            ); ?></li>
                            <li><?php echo esc_html__('進入「商店管理」→「商店設定」', 'fluentcart-payuni'); ?></li>
                            <li><?php echo esc_html__('複製「商店代號」欄位的值', 'fluentcart-payuni'); ?></li>
                            <li><?php echo esc_html__('在 FluentCart 付款設定中填入此代號', 'fluentcart-payuni'); ?></li>
                        </ol>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question"><?php echo esc_html__('如何切換測試/正式環境?', 'fluentcart-payuni'); ?></button>
                    <div class="faq-answer">
                        <p><?php echo esc_html__('PayUNi 使用不同的 API 端點和憑證區分測試/正式環境：', 'fluentcart-payuni'); ?></p>
                        <ol>
                            <li><?php echo esc_html__('進入 FluentCart → 付款設定 → PayUNi 信用卡', 'fluentcart-payuni'); ?></li>
                            <li><?php echo esc_html__('選擇「環境」欄位 (測試/正式)', 'fluentcart-payuni'); ?></li>
                            <li><?php echo esc_html__('填入對應環境的 MerID、Hash Key、Hash IV', 'fluentcart-payuni'); ?></li>
                            <li><strong><?php echo esc_html__('注意：', 'fluentcart-payuni'); ?></strong><?php echo esc_html__('測試環境使用測試用憑證，正式環境使用正式憑證', 'fluentcart-payuni'); ?></li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Category 2: Webhook 調試 -->
            <div class="faq-category">
                <h3><?php echo esc_html__('Webhook 調試', 'fluentcart-payuni'); ?></h3>
                <div class="faq-item">
                    <button class="faq-question"><?php echo esc_html__('Webhook 沒有觸發怎麼辦?', 'fluentcart-payuni'); ?></button>
                    <div class="faq-answer">
                        <p><?php echo esc_html__('請依序檢查以下項目：', 'fluentcart-payuni'); ?></p>
                        <ol>
                            <li><strong><?php echo esc_html__('確認 URL 設定', 'fluentcart-payuni'); ?></strong><?php echo esc_html__('：在 PayUNi 後台確認 NotifyURL 已正確設定', 'fluentcart-payuni'); ?></li>
                            <li><strong><?php echo esc_html__('SSL 憑證', 'fluentcart-payuni'); ?></strong><?php echo esc_html__('：PayUNi 要求 HTTPS，確認網站有有效的 SSL 憑證', 'fluentcart-payuni'); ?></li>
                            <li><strong><?php echo esc_html__('防火牆', 'fluentcart-payuni'); ?></strong><?php echo esc_html__('：確認伺服器防火牆允許 PayUNi IP 範圍的請求', 'fluentcart-payuni'); ?></li>
                            <li><strong><?php echo esc_html__('查看日誌', 'fluentcart-payuni'); ?></strong><?php echo esc_html__('：進入 PayUNi Webhook 記錄頁面檢視是否有接收到請求', 'fluentcart-payuni'); ?></li>
                        </ol>
                        <p><?php echo sprintf(
                            esc_html__('詳細排查步驟請參閱 %s 區塊。', 'fluentcart-payuni'),
                            '<a href="#troubleshooting">' . esc_html__('疑難排解', 'fluentcart-payuni') . '</a>'
                        ); ?></p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question"><?php echo esc_html__('如何驗證 Webhook 運作正常?', 'fluentcart-payuni'); ?></button>
                    <div class="faq-answer">
                        <ol>
                            <li><?php echo esc_html__('進入 PayUNi 設定頁面', 'fluentcart-payuni'); ?></li>
                            <li><?php echo esc_html__('點擊「測試連線」按鈕', 'fluentcart-payuni'); ?></li>
                            <li><?php echo esc_html__('如顯示「Webhook URL 可連線」表示基本連線正常', 'fluentcart-payuni'); ?></li>
                            <li><?php echo esc_html__('實際付款測試：完成一筆測試交易後，檢查 Webhook 記錄是否有新紀錄', 'fluentcart-payuni'); ?></li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Category 3: 訂閱續扣問題 -->
            <div class="faq-category">
                <h3><?php echo esc_html__('訂閱續扣問題', 'fluentcart-payuni'); ?></h3>
                <div class="faq-item">
                    <button class="faq-question"><?php echo esc_html__('訂閱續扣失敗怎麼辦?', 'fluentcart-payuni'); ?></button>
                    <div class="faq-answer">
                        <p><?php echo esc_html__('續扣失敗通常有以下原因：', 'fluentcart-payuni'); ?></p>
                        <ul>
                            <li><strong><?php echo esc_html__('餘額不足', 'fluentcart-payuni'); ?></strong><?php echo esc_html__('：提醒客戶補充信用卡額度', 'fluentcart-payuni'); ?></li>
                            <li><strong><?php echo esc_html__('卡片過期', 'fluentcart-payuni'); ?></strong><?php echo esc_html__('：請客戶更新付款卡片', 'fluentcart-payuni'); ?></li>
                            <li><strong><?php echo esc_html__('銀行拒絕', 'fluentcart-payuni'); ?></strong><?php echo esc_html__('：請客戶聯繫發卡銀行', 'fluentcart-payuni'); ?></li>
                        </ul>
                        <p><strong><?php echo esc_html__('系統自動重試機制：', 'fluentcart-payuni'); ?></strong></p>
                        <ul>
                            <li><?php echo esc_html__('首次失敗後 24 小時重試', 'fluentcart-payuni'); ?></li>
                            <li><?php echo esc_html__('第二次失敗後 48 小時重試', 'fluentcart-payuni'); ?></li>
                            <li><?php echo esc_html__('第三次失敗後 72 小時重試', 'fluentcart-payuni'); ?></li>
                        </ul>
                        <p><?php echo esc_html__('如需手動處理，請在訂閱詳情頁查看失敗原因。', 'fluentcart-payuni'); ?></p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question"><?php echo esc_html__('如何更新信用卡資訊?', 'fluentcart-payuni'); ?></button>
                    <div class="faq-answer">
                        <p><?php echo esc_html__('目前版本客戶需透過以下方式更新：', 'fluentcart-payuni'); ?></p>
                        <ol>
                            <li><?php echo esc_html__('客戶登入網站進入「我的帳戶」→「訂閱」', 'fluentcart-payuni'); ?></li>
                            <li><?php echo esc_html__('選擇要更新的訂閱', 'fluentcart-payuni'); ?></li>
                            <li><?php echo esc_html__('點擊「更新付款方式」', 'fluentcart-payuni'); ?></li>
                            <li><?php echo esc_html__('輸入新的信用卡資訊並完成 3D 驗證', 'fluentcart-payuni'); ?></li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Category 4: ATM 虛擬帳號 -->
            <div class="faq-category">
                <h3><?php echo esc_html__('ATM 虛擬帳號', 'fluentcart-payuni'); ?></h3>
                <div class="faq-item">
                    <button class="faq-question"><?php echo esc_html__('客戶問我 ATM 虛擬帳號在哪裡?', 'fluentcart-payuni'); ?></button>
                    <div class="faq-answer">
                        <p><?php echo esc_html__('ATM 虛擬帳號可在以下位置查看：', 'fluentcart-payuni'); ?></p>
                        <ol>
                            <li><strong><?php echo esc_html__('訂單確認郵件', 'fluentcart-payuni'); ?></strong><?php echo esc_html__('：客戶下單後會收到包含帳號的確認信', 'fluentcart-payuni'); ?></li>
                            <li><strong><?php echo esc_html__('訂單詳情頁', 'fluentcart-payuni'); ?></strong><?php echo esc_html__('：客戶登入網站 →「我的訂單」→ 點擊該訂單', 'fluentcart-payuni'); ?></li>
                            <li><strong><?php echo esc_html__('後台查詢', 'fluentcart-payuni'); ?></strong><?php echo esc_html__('：FluentCart 訂單 → 點擊訂單 → PayUNi 交易資訊區塊', 'fluentcart-payuni'); ?></li>
                        </ol>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question"><?php echo esc_html__('ATM 付款期限多久?', 'fluentcart-payuni'); ?></button>
                    <div class="faq-answer">
                        <p><?php echo sprintf(
                            esc_html__('ATM 虛擬帳號預設有效期限為 %s（PayUNi 預設值）。', 'fluentcart-payuni'),
                            '<strong>' . esc_html__('7 天', 'fluentcart-payuni') . '</strong>'
                        ); ?></p>
                        <p><?php echo esc_html__('過期後帳號失效，訂單會自動標記為「已取消」。', 'fluentcart-payuni'); ?></p>
                        <p><?php echo esc_html__('如需調整期限，請聯繫 PayUNi 客服設定。', 'fluentcart-payuni'); ?></p>
                    </div>
                </div>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Troubleshooting section.
     *
     * @return string HTML content.
     */
    public function renderTroubleshootingSection(): string
    {
        ob_start();
        ?>
        <section id="troubleshooting" class="guide-section">
            <h2><?php echo esc_html__('疑難排解', 'fluentcart-payuni'); ?></h2>

            <!-- 1. 錯誤訊息對照表 -->
            <div class="troubleshooting-section">
                <h3><?php echo esc_html__('常見錯誤訊息對照表', 'fluentcart-payuni'); ?></h3>
                <table class="error-table">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('錯誤代碼/訊息', 'fluentcart-payuni'); ?></th>
                            <th><?php echo esc_html__('可能原因', 'fluentcart-payuni'); ?></th>
                            <th><?php echo esc_html__('解決方法', 'fluentcart-payuni'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code><?php echo esc_html__('Hash 驗證失敗', 'fluentcart-payuni'); ?></code></td>
                            <td><?php echo esc_html__('Hash Key 或 Hash IV 設定錯誤', 'fluentcart-payuni'); ?></td>
                            <td><?php echo esc_html__('確認 FluentCart 中的 Hash Key/IV 與 PayUNi 後台一致', 'fluentcart-payuni'); ?></td>
                        </tr>
                        <tr>
                            <td><code><?php echo esc_html__('商店代號不存在', 'fluentcart-payuni'); ?></code></td>
                            <td><?php echo esc_html__('MerID 設定錯誤或環境不匹配', 'fluentcart-payuni'); ?></td>
                            <td><?php echo esc_html__('確認使用正確環境的 MerID（測試/正式）', 'fluentcart-payuni'); ?></td>
                        </tr>
                        <tr>
                            <td><code><?php echo esc_html__('交易金額錯誤', 'fluentcart-payuni'); ?></code></td>
                            <td><?php echo esc_html__('金額格式不正確或超出限制', 'fluentcart-payuni'); ?></td>
                            <td><?php echo esc_html__('確認金額為整數且在 PayUNi 允許範圍內', 'fluentcart-payuni'); ?></td>
                        </tr>
                        <tr>
                            <td><code><?php echo esc_html__('信用卡授權失敗', 'fluentcart-payuni'); ?></code></td>
                            <td><?php echo esc_html__('卡片問題（餘額不足、過期、被鎖）', 'fluentcart-payuni'); ?></td>
                            <td><?php echo esc_html__('請客戶確認卡片狀態或使用其他卡片', 'fluentcart-payuni'); ?></td>
                        </tr>
                        <tr>
                            <td><code><?php echo esc_html__('3D 驗證失敗', 'fluentcart-payuni'); ?></code></td>
                            <td><?php echo esc_html__('客戶未完成 3D 驗證或驗證超時', 'fluentcart-payuni'); ?></td>
                            <td><?php echo esc_html__('請客戶重新付款並完成 3D 驗證', 'fluentcart-payuni'); ?></td>
                        </tr>
                        <tr>
                            <td><code><?php echo esc_html__('Webhook 簽章驗證失敗', 'fluentcart-payuni'); ?></code></td>
                            <td><?php echo esc_html__('Webhook 資料被竄改或 Key 不正確', 'fluentcart-payuni'); ?></td>
                            <td><?php echo esc_html__('確認 Hash Key 正確，檢查伺服器時間是否同步', 'fluentcart-payuni'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 2. Webhook 排查檢查清單 -->
            <div class="troubleshooting-section">
                <h3><?php echo esc_html__('Webhook 無法運作檢查清單', 'fluentcart-payuni'); ?></h3>
                <div class="checklist">
                    <div class="checklist-intro">
                        <p><?php echo esc_html__('如果 Webhook 無法正常接收，請依序檢查以下項目：', 'fluentcart-payuni'); ?></p>
                    </div>
                    <ul class="checklist-items">
                        <li>
                            <span class="checklist-checkbox">☐</span>
                            <span class="checklist-text">
                                <strong><?php echo esc_html__('URL 是否正確', 'fluentcart-payuni'); ?></strong><?php echo esc_html__(' - 在 PayUNi 後台確認 NotifyURL 已正確設定', 'fluentcart-payuni'); ?>
                                <br><small><?php echo esc_html__('可在「PayUNi 設定」頁面複製正確的 Webhook URL', 'fluentcart-payuni'); ?></small>
                            </span>
                        </li>
                        <li>
                            <span class="checklist-checkbox">☐</span>
                            <span class="checklist-text">
                                <strong><?php echo esc_html__('HTTPS 是否有效', 'fluentcart-payuni'); ?></strong><?php echo esc_html__(' - PayUNi 要求使用有效的 SSL 憑證', 'fluentcart-payuni'); ?>
                                <br><small><?php echo esc_html__('使用瀏覽器確認網站顯示安全鎖頭圖示', 'fluentcart-payuni'); ?></small>
                            </span>
                        </li>
                        <li>
                            <span class="checklist-checkbox">☐</span>
                            <span class="checklist-text">
                                <strong><?php echo esc_html__('防火牆是否允許', 'fluentcart-payuni'); ?></strong><?php echo esc_html__(' - 確認伺服器防火牆允許外部 POST 請求', 'fluentcart-payuni'); ?>
                                <br><small><?php echo esc_html__('聯繫主機商確認 PayUNi 的 IP 範圍可以存取', 'fluentcart-payuni'); ?></small>
                            </span>
                        </li>
                        <li>
                            <span class="checklist-checkbox">☐</span>
                            <span class="checklist-text">
                                <strong><?php echo esc_html__('網站是否可公開訪問', 'fluentcart-payuni'); ?></strong><?php echo esc_html__(' - Webhook 只能發送到公開網站', 'fluentcart-payuni'); ?>
                                <br><small><?php echo esc_html__('localhost 或內網環境無法接收 Webhook', 'fluentcart-payuni'); ?></small>
                            </span>
                        </li>
                        <li>
                            <span class="checklist-checkbox">☐</span>
                            <span class="checklist-text">
                                <strong><?php echo esc_html__('伺服器日誌有無錯誤', 'fluentcart-payuni'); ?></strong><?php echo esc_html__(' - 檢查 PHP 錯誤日誌', 'fluentcart-payuni'); ?>
                                <br><small><?php echo esc_html__('查看 wp-content/debug.log 或主機 error_log', 'fluentcart-payuni'); ?></small>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- 3. 訂單未付款排查流程圖 -->
            <div class="troubleshooting-section">
                <h3><?php echo esc_html__('訂單未付款排查流程', 'fluentcart-payuni'); ?></h3>
                <div class="flowchart">
                    <div class="flowchart-step start">
                        <span><?php echo esc_html__('訂單狀態為「待付款」', 'fluentcart-payuni'); ?></span>
                    </div>
                    <div class="flowchart-arrow">↓</div>
                    <div class="flowchart-decision">
                        <span><?php echo esc_html__('客戶有收到付款資訊嗎？', 'fluentcart-payuni'); ?></span>
                    </div>
                    <div class="flowchart-branches">
                        <div class="flowchart-branch">
                            <span class="branch-label"><?php echo esc_html__('否', 'fluentcart-payuni'); ?></span>
                            <div class="flowchart-arrow">↓</div>
                            <div class="flowchart-action">
                                <?php echo esc_html__('檢查訂單確認郵件是否成功發送', 'fluentcart-payuni'); ?>
                            </div>
                        </div>
                        <div class="flowchart-branch">
                            <span class="branch-label"><?php echo esc_html__('是', 'fluentcart-payuni'); ?></span>
                            <div class="flowchart-arrow">↓</div>
                            <div class="flowchart-decision">
                                <span><?php echo esc_html__('客戶有完成付款嗎？', 'fluentcart-payuni'); ?></span>
                            </div>
                            <div class="flowchart-branches nested">
                                <div class="flowchart-branch">
                                    <span class="branch-label"><?php echo esc_html__('否', 'fluentcart-payuni'); ?></span>
                                    <div class="flowchart-arrow">↓</div>
                                    <div class="flowchart-action">
                                        <?php echo esc_html__('提醒客戶付款期限', 'fluentcart-payuni'); ?>
                                    </div>
                                </div>
                                <div class="flowchart-branch">
                                    <span class="branch-label"><?php echo esc_html__('是', 'fluentcart-payuni'); ?></span>
                                    <div class="flowchart-arrow">↓</div>
                                    <div class="flowchart-action">
                                        <?php echo esc_html__('檢查 Webhook 記錄是否有收到通知', 'fluentcart-payuni'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }
}
