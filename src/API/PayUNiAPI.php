<?php

namespace BuyGoFluentCart\PayUNi\API;

use BuyGoFluentCart\PayUNi\Gateway\PayUNiSettingsBase;
use BuyGoFluentCart\PayUNi\Services\PayUNiCryptoService;
use BuyGoFluentCart\PayUNi\Services\IdempotencyService;
use BuyGoFluentCart\PayUNi\Utils\Logger;

/**
 * PayUNiAPI
 *
 * 白話：封裝呼叫 PayUNi endpoint（或在這裡包官方 SDK）。
 */
final class PayUNiAPI
{
    private PayUNiSettingsBase $settings;

    public function __construct(PayUNiSettingsBase $settings)
    {
        $this->settings = $settings;
    }

    public function getBaseUrl(string $mode = ''): string
    {
        $mode = $mode ?: $this->settings->getMode();

        // PayUNi SDK behavior: https://(sandbox-)api.payuni.com.tw/api/
        $prefix = 'https://';
        if ($mode === 'test') {
            $prefix .= 'sandbox-';
        }

        return $prefix . 'api.payuni.com.tw/api/';
    }

    /**
     * Build parameters to POST to PayUNi (MerID, Version, EncryptInfo, HashInfo).
     */
    public function buildParams(array $encryptInfo, string $tradeType, string $version = '1.0', string $mode = ''): array
    {
        $mode = $mode ?: $this->settings->getMode();

        $crypto = new PayUNiCryptoService($this->settings);

        $encrypted = $crypto->encryptInfo($encryptInfo, $mode);

        return [
            'MerID' => (string) ($encryptInfo['MerID'] ?? $this->settings->getMerId($mode)),
            'Version' => $version,
            'EncryptInfo' => $encrypted,
            'HashInfo' => $crypto->hashInfo($encrypted, $mode),
            '_trade_type' => $tradeType,
            '_mode' => $mode,
        ];
    }

    public function getEndpointUrl(string $tradeType, string $mode = ''): string
    {
        $mode = $mode ?: $this->settings->getMode();

        $map = [
            'upp' => 'upp',
            'atm' => 'atm',
            'cvs' => 'cvs',
            'credit' => 'credit',
            'trade_query' => 'trade/query',
            'trade_close' => 'trade/close',
            'trade_cancel' => 'trade/cancel',
            'credit_bind_query' => 'credit_bind/query',
            'credit_bind_cancel' => 'credit_bind/cancel',
        ];

        $path = $map[$tradeType] ?? $tradeType;

        return $this->getBaseUrl($mode) . ltrim($path, '/');
    }

    /**
     * Call PayUNi (server-to-server) and return decoded array, or WP_Error.
     */
    public function post(string $tradeType, array $encryptInfo, string $version = '1.0', string $mode = '')
    {
        $mode = $mode ?: $this->settings->getMode();

        // 記錄 idempotency key（MerTradeNo）
        $merTradeNo = $encryptInfo['MerTradeNo'] ?? '';
        $idempotencyKey = IdempotencyService::generateUuid();

        Logger::info('PayUNi API call initiated', [
            'trade_type' => $tradeType,
            'mer_trade_no' => $merTradeNo,
            'idempotency_key' => $idempotencyKey,
            'mode' => $mode,
        ]);

        $params = $this->buildParams($encryptInfo, $tradeType, $version, $mode);

        // _trade_type/_mode are internal only
        unset($params['_trade_type'], $params['_mode']);

        $url = $this->getEndpointUrl($tradeType, $mode);

        $resp = wp_remote_post($url, [
            'timeout' => 60,
            'body' => $params,
            'user-agent' => 'buygo-fluentcart-payuni',
        ]);

        if (is_wp_error($resp)) {
            Logger::error('PayUNi API request failed (WP_Error)', [
                'trade_type' => $tradeType,
                'url' => $url,
                'mode' => $mode,
                'mer_trade_no' => $merTradeNo,
                'idempotency_key' => $idempotencyKey,
                'error_message' => $resp->get_error_message(),
                'error_code' => $resp->get_error_code(),
            ]);
            return $resp;
        }

        $httpCode = wp_remote_retrieve_response_code($resp);
        $httpMessage = wp_remote_retrieve_response_message($resp);
        $body = wp_remote_retrieve_body($resp);

        if ($httpCode !== 200) {
            $errorMessage = sprintf(
                'PayUNi API returned HTTP %d: %s',
                $httpCode,
                $httpMessage ?: 'Unknown error'
            );

            Logger::error('PayUNi API request failed (HTTP error)', [
                'trade_type' => $tradeType,
                'url' => $url,
                'mode' => $mode,
                'mer_trade_no' => $merTradeNo,
                'idempotency_key' => $idempotencyKey,
                'http_code' => $httpCode,
                'http_message' => $httpMessage,
                'response_body' => $body,
            ]);

            return new \WP_Error(
                'payuni_http_error',
                $errorMessage,
                [
                    'url' => $url,
                    'http_code' => $httpCode,
                    'http_message' => $httpMessage,
                    'body' => $body,
                ]
            );
        }

        $decoded = json_decode($body, true);

        if (!is_array($decoded)) {
            Logger::error('PayUNi API invalid response format', [
                'trade_type' => $tradeType,
                'url' => $url,
                'mode' => $mode,
                'response_body' => $body,
            ]);

            return new \WP_Error('payuni_invalid_response', 'Invalid PayUNi response', [
                'url' => $url,
                'body' => $body,
            ]);
        }

        // 成功時記錄
        Logger::info('PayUNi API call succeeded', [
            'trade_type' => $tradeType,
            'mer_trade_no' => $merTradeNo,
            'idempotency_key' => $idempotencyKey,
            'response_status' => $decoded['Status'] ?? 'unknown',
        ]);

        return $decoded;
    }
}

