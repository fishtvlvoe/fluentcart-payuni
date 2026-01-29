<?php

namespace BuyGoFluentCart\PayUNi\Tests\Unit\Services;

use BuyGoFluentCart\PayUNi\Services\IdempotencyService;
use PHPUnit\Framework\TestCase;

final class IdempotencyServiceTest extends TestCase
{
	public function testGenerateKeyReturnsStringUnder20Chars(): void
	{
		$key = IdempotencyService::generateKey();

		$this->assertIsString($key);
		$this->assertLessThanOrEqual(20, strlen($key));
		$this->assertGreaterThan(0, strlen($key));
	}

	public function testGenerateKeyIsUnique(): void
	{
		$keys = [];
		for ($i = 0; $i < 100; $i++) {
			$keys[] = IdempotencyService::generateKey();
		}

		$uniqueKeys = array_unique($keys);
		$this->assertCount(100, $uniqueKeys, 'All generated keys should be unique');
	}

	public function testGenerateKeyWithPrefix(): void
	{
		$key = IdempotencyService::generateKey('123');

		$this->assertStringStartsWith('123', $key);
		$this->assertLessThanOrEqual(20, strlen($key));
	}

	public function testGenerateKeyWithLongPrefixTruncates(): void
	{
		$key = IdempotencyService::generateKey('1234567890123');

		// 前綴應被截斷至 8 字元
		$this->assertLessThanOrEqual(20, strlen($key));
	}

	public function testGenerateUuidReturnsValidFormat(): void
	{
		$uuid = IdempotencyService::generateUuid();

		// UUID v4 格式：xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
		$pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
		$this->assertMatchesRegularExpression($pattern, $uuid);
	}

	public function testGenerateUuidIsUnique(): void
	{
		$uuids = [];
		for ($i = 0; $i < 100; $i++) {
			$uuids[] = IdempotencyService::generateUuid();
		}

		$uniqueUuids = array_unique($uuids);
		$this->assertCount(100, $uniqueUuids, 'All generated UUIDs should be unique');
	}

	public function testGenerateKeyContainsOnlyAlphanumeric(): void
	{
		$key = IdempotencyService::generateKey();

		$this->assertMatchesRegularExpression('/^[A-Z0-9]+$/', $key);
	}
}
