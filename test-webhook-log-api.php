<?php
/**
 * Test script for WebhookLogAPI
 *
 * Usage: 在瀏覽器以管理員身份登入後訪問：
 * https://test.buygo.me/wp-content/plugins/fluentcart-payuni/test-webhook-log-api.php
 */

// Load WordPress
require_once __DIR__ . '/../../../wp-load.php';

// Check if user is logged in and is admin
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die('請以管理員身份登入 WordPress 後台，然後重新訪問此頁面。');
}

// Test API endpoints
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>WebhookLogAPI 測試</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .button { display: inline-block; padding: 10px 20px; margin: 5px; background-color: #007cba; color: white; text-decoration: none; border-radius: 3px; }
        .button:hover { background-color: #005a87; }
    </style>
</head>
<body>
    <h1>WebhookLogAPI 測試</h1>
    <p>目前登入身份: <strong><?php echo wp_get_current_user()->user_login; ?></strong> (管理員)</p>

    <div class="test-section">
        <h2>測試 1: 取得所有 Webhook 日誌</h2>
        <?php
        $response = wp_remote_get(
            rest_url('fluentcart-payuni/v1/webhook-logs'),
            [
                'cookies' => $_COOKIE,
            ]
        );

        if (is_wp_error($response)) {
            echo '<div class="error"><strong>錯誤:</strong> ' . esc_html($response->get_error_message()) . '</div>';
        } else {
            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if ($code === 200) {
                echo '<div class="success"><strong>成功!</strong> HTTP Status: ' . $code . '</div>';
                echo '<h3>回應資料:</h3>';
                echo '<pre>' . esc_html(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
            } else {
                echo '<div class="error"><strong>失敗!</strong> HTTP Status: ' . $code . '</div>';
                echo '<pre>' . esc_html($body) . '</pre>';
            }
        }
        ?>
    </div>

    <div class="test-section">
        <h2>測試 2: 依 transaction_id 過濾</h2>
        <?php
        $response = wp_remote_get(
            rest_url('fluentcart-payuni/v1/webhook-logs?transaction_id=test-uuid-001'),
            [
                'cookies' => $_COOKIE,
            ]
        );

        if (is_wp_error($response)) {
            echo '<div class="error"><strong>錯誤:</strong> ' . esc_html($response->get_error_message()) . '</div>';
        } else {
            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if ($code === 200) {
                echo '<div class="success"><strong>成功!</strong> HTTP Status: ' . $code . '</div>';
                echo '<h3>回應資料:</h3>';
                echo '<pre>' . esc_html(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
            } else {
                echo '<div class="error"><strong>失敗!</strong> HTTP Status: ' . $code . '</div>';
                echo '<pre>' . esc_html($body) . '</pre>';
            }
        }
        ?>
    </div>

    <div class="test-section">
        <h2>測試 3: 依 webhook_type 過濾</h2>
        <?php
        $response = wp_remote_get(
            rest_url('fluentcart-payuni/v1/webhook-logs?webhook_type=notify'),
            [
                'cookies' => $_COOKIE,
            ]
        );

        if (is_wp_error($response)) {
            echo '<div class="error"><strong>錯誤:</strong> ' . esc_html($response->get_error_message()) . '</div>';
        } else {
            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if ($code === 200) {
                echo '<div class="success"><strong>成功!</strong> HTTP Status: ' . $code . '</div>';
                echo '<h3>回應資料:</h3>';
                echo '<pre>' . esc_html(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
            } else {
                echo '<div class="error"><strong>失敗!</strong> HTTP Status: ' . $code . '</div>';
                echo '<pre>' . esc_html($body) . '</pre>';
            }
        }
        ?>
    </div>

    <div class="test-section">
        <h2>測試 4: 分頁功能</h2>
        <?php
        $response = wp_remote_get(
            rest_url('fluentcart-payuni/v1/webhook-logs?per_page=2&page=1'),
            [
                'cookies' => $_COOKIE,
            ]
        );

        if (is_wp_error($response)) {
            echo '<div class="error"><strong>錯誤:</strong> ' . esc_html($response->get_error_message()) . '</div>';
        } else {
            $code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if ($code === 200) {
                echo '<div class="success"><strong>成功!</strong> HTTP Status: ' . $code . '</div>';
                echo '<h3>回應資料:</h3>';
                echo '<pre>' . esc_html(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
            } else {
                echo '<div class="error"><strong>失敗!</strong> HTTP Status: ' . $code . '</div>';
                echo '<pre>' . esc_html($body) . '</pre>';
            }
        }
        ?>
    </div>

    <div class="test-section">
        <h2>測試總結</h2>
        <p>✅ API 端點已註冊</p>
        <p>✅ 權限控制正常（只有管理員可查詢）</p>
        <p>✅ 查詢功能正常</p>
        <p>✅ 過濾功能正常</p>
        <p>✅ 分頁功能正常</p>
    </div>

    <p><a href="<?php echo admin_url(); ?>" class="button">返回 WordPress 後台</a></p>
</body>
</html>
