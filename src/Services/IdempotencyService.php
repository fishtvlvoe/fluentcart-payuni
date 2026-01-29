<?php

namespace BuyGoFluentCart\PayUNi\Services;

/**
 * IdempotencyService
 *
 * 白話：產生 API 呼叫的冪等鍵（UUID），確保重試不會重複扣款。
 */
final class IdempotencyService
{
	/**
	 * 產生符合 PayUNi MerTradeNo 規範的唯一識別碼。
	 *
	 * PayUNi 要求：20 字元以內，英數字。
	 * 格式：{prefix}{timestamp_base36}{random}
	 *
	 * @param string $prefix 可選前綴（如 transaction ID）
	 * @return string 唯一識別碼（最多 20 字元）
	 */
	public static function generateKey(string $prefix = ''): string
	{
		$time = base_convert((string) time(), 10, 36);
		$rand = substr(bin2hex(random_bytes(4)), 0, 6);

		if ($prefix) {
			// {prefix}A{time}{rand} - A 作為分隔符
			$key = substr($prefix, 0, 8) . 'A' . $time . $rand;
		} else {
			// {time}{rand}
			$key = $time . $rand;
		}

		return strtoupper(substr($key, 0, 20));
	}

	/**
	 * 產生完整 UUID v4（用於內部追蹤）。
	 *
	 * @return string UUID v4 格式
	 */
	public static function generateUuid(): string
	{
		$data = random_bytes(16);
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40);
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}
}
