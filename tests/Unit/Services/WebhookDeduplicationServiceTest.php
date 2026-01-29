<?php

namespace BuyGoFluentCart\PayUNi\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;

/**
 * WebhookDeduplicationServiceTest
 *
 * 注意：由於 WebhookDeduplicationService 依賴 $wpdb，
 * 完整的功能測試需要 WordPress 整合測試環境。
 * 這裡只測試不依賴資料庫的邏輯。
 */
final class WebhookDeduplicationServiceTest extends TestCase
{
	/**
	 * 測試類別可以被實例化（基本冒煙測試）
	 *
	 * 完整功能測試需要整合測試環境。
	 */
	public function testClassExists(): void
	{
		$this->assertTrue(
			class_exists(\BuyGoFluentCart\PayUNi\Services\WebhookDeduplicationService::class)
		);
	}

	/**
	 * 測試類別有必要的方法
	 */
	public function testRequiredMethodsExist(): void
	{
		$class = new \ReflectionClass(\BuyGoFluentCart\PayUNi\Services\WebhookDeduplicationService::class);

		$this->assertTrue($class->hasMethod('isProcessed'));
		$this->assertTrue($class->hasMethod('markProcessed'));
		$this->assertTrue($class->hasMethod('cleanup'));
	}

	/**
	 * 測試方法簽章正確
	 */
	public function testIsProcessedMethodSignature(): void
	{
		$method = new \ReflectionMethod(
			\BuyGoFluentCart\PayUNi\Services\WebhookDeduplicationService::class,
			'isProcessed'
		);

		$params = $method->getParameters();
		$this->assertCount(2, $params);
		$this->assertEquals('transactionId', $params[0]->getName());
		$this->assertEquals('webhookType', $params[1]->getName());
	}

	/**
	 * 測試 markProcessed 方法簽章
	 */
	public function testMarkProcessedMethodSignature(): void
	{
		$method = new \ReflectionMethod(
			\BuyGoFluentCart\PayUNi\Services\WebhookDeduplicationService::class,
			'markProcessed'
		);

		$params = $method->getParameters();
		$this->assertGreaterThanOrEqual(2, count($params));
		$this->assertEquals('transactionId', $params[0]->getName());
		$this->assertEquals('webhookType', $params[1]->getName());
	}

	/**
	 * 測試 cleanup 方法存在且無必要參數
	 */
	public function testCleanupMethodSignature(): void
	{
		$method = new \ReflectionMethod(
			\BuyGoFluentCart\PayUNi\Services\WebhookDeduplicationService::class,
			'cleanup'
		);

		$params = $method->getParameters();

		// 確保 cleanup 方法存在（已透過上面的 ReflectionMethod 驗證）
		$this->assertIsArray($params);

		// cleanup 應該沒有必要參數
		foreach ($params as $param) {
			$this->assertTrue($param->isOptional() || $param->isDefaultValueAvailable());
		}
	}
}
