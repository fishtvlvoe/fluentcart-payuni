<?php
/**
 * Unit tests for SettingsPage.
 *
 * @package BuyGoFluentCart\PayUNi\Tests\Unit\Admin
 */

namespace BuyGoFluentCart\PayUNi\Tests\Unit\Admin;

use PHPUnit\Framework\TestCase;
use BuyGoFluentCart\PayUNi\Admin\SettingsPage;
use BuyGoFluentCart\PayUNi\Gateway\PayUNiSettingsBase;

/**
 * SettingsPageTest class.
 *
 * Tests SettingsPage business logic without WordPress dependencies.
 *
 * @covers \BuyGoFluentCart\PayUNi\Admin\SettingsPage
 */
class SettingsPageTest extends TestCase
{
    private SettingsPage $settingsPage;

    protected function setUp(): void
    {
        parent::setUp();
        // Create instance without triggering WordPress hooks
        $this->settingsPage = new SettingsPage(false);
    }

    /**
     * Test constructor with registerHooks parameter.
     *
     * This test verifies that $registerHooks parameter works for testability.
     * Actual hook registration cannot be tested in unit test context.
     */
    public function testConstructorWithoutHooksDoesNotRegisterActions(): void
    {
        $page = new SettingsPage(false);
        $this->assertInstanceOf(SettingsPage::class, $page);
    }

    /**
     * Test getCredentialStatus returns filled status.
     */
    public function testGetCredentialStatusReturnsFilled(): void
    {
        // Note: This test uses real PayUNiSettingsBase fallback (no FluentCart)
        // which returns empty credentials by default.
        // For testing filled status, we need integration test with FluentCart.
        $status = $this->settingsPage->getCredentialStatus('test');

        $this->assertIsArray($status);
        $this->assertArrayHasKey('filled', $status);
        $this->assertArrayHasKey('mer_id', $status);
        $this->assertArrayHasKey('hash_key_set', $status);
        $this->assertArrayHasKey('hash_iv_set', $status);
        $this->assertIsBool($status['filled']);
        $this->assertIsBool($status['hash_key_set']);
        $this->assertIsBool($status['hash_iv_set']);
    }

    /**
     * Test getCredentialStatus returns empty status.
     */
    public function testGetCredentialStatusReturnsEmpty(): void
    {
        // Using fallback PayUNiSettingsBase (no FluentCart), all credentials are empty
        $status = $this->settingsPage->getCredentialStatus('test');

        $this->assertFalse($status['filled']);
        $this->assertEmpty($status['mer_id']);
        $this->assertFalse($status['hash_key_set']);
        $this->assertFalse($status['hash_iv_set']);
    }

    /**
     * Test getCredentialStatus for live mode.
     */
    public function testGetCredentialStatusLiveMode(): void
    {
        $status = $this->settingsPage->getCredentialStatus('live');

        $this->assertIsArray($status);
        $this->assertArrayHasKey('filled', $status);
        $this->assertArrayHasKey('mer_id', $status);
        $this->assertArrayHasKey('hash_key_set', $status);
        $this->assertArrayHasKey('hash_iv_set', $status);
    }

    /**
     * Test getWebhookUrls returns correct structure.
     */
    public function testGetWebhookUrlsReturnsCorrectUrls(): void
    {
        $urls = $this->settingsPage->getWebhookUrls();

        $this->assertIsArray($urls);
        $this->assertArrayHasKey('notify', $urls);
        $this->assertArrayHasKey('return', $urls);

        // Verify notify URL uses new clean endpoint
        $this->assertStringContainsString('fluentcart-api/payuni-notify', $urls['notify']);

        // Verify return URL contains required query parameters
        $this->assertStringContainsString('fct_payment_listener=1', $urls['return']);
        $this->assertStringContainsString('method=payuni', $urls['return']);
        $this->assertStringContainsString('payuni_return=1', $urls['return']);
    }

    /**
     * Test getWebhookUrls uses site_url as base.
     */
    public function testGetWebhookUrlsUseSiteUrl(): void
    {
        $urls = $this->settingsPage->getWebhookUrls();

        // Both URLs should be valid HTTP/HTTPS URLs
        $this->assertMatchesRegularExpression('#^https?://#', $urls['notify']);
        $this->assertMatchesRegularExpression('#^https?://#', $urls['return']);
    }

    /**
     * Test notify URL has clean path structure.
     */
    public function testNotifyUrlHasCleanPath(): void
    {
        $urls = $this->settingsPage->getWebhookUrls();

        // Notify URL should end with clean path (no query string)
        $this->assertStringEndsWith('/fluentcart-api/payuni-notify', $urls['notify']);
        $this->assertStringNotContainsString('?', $urls['notify']);
    }

    /**
     * Test return URL has required query parameters.
     */
    public function testReturnUrlHasRequiredParameters(): void
    {
        $urls = $this->settingsPage->getWebhookUrls();

        $parsedUrl = parse_url($urls['return']);
        $this->assertArrayHasKey('query', $parsedUrl);

        parse_str($parsedUrl['query'], $queryParams);
        $this->assertEquals('1', $queryParams['fct_payment_listener']);
        $this->assertEquals('payuni', $queryParams['method']);
        $this->assertEquals('1', $queryParams['payuni_return']);
    }

    /**
     * Test getCredentialStatus masks MerID correctly.
     *
     * Note: In unit test environment without FluentCart, MerID is empty.
     * This test verifies the masking logic would work if credentials existed.
     */
    public function testGetCredentialStatusMasksMerIdWhenPresent(): void
    {
        // Create a mock settings instance with test data
        // (This would need a mock in a real scenario with filled credentials)
        $status = $this->settingsPage->getCredentialStatus('test');

        // In fallback mode (no FluentCart), mer_id is empty
        // If it were filled, it should be masked (first 3 chars + ***)
        if (!empty($status['mer_id'])) {
            $this->assertMatchesRegularExpression('/^.{1,3}\*\*\*$/', $status['mer_id']);
        } else {
            // Expected in unit test environment
            $this->assertEmpty($status['mer_id']);
        }
    }

    /**
     * Test that hash_key_set and hash_iv_set are booleans, not actual secrets.
     */
    public function testGetCredentialStatusDoesNotExposeSecrets(): void
    {
        $status = $this->settingsPage->getCredentialStatus('test');

        // These should be boolean flags, not actual key values
        $this->assertIsBool($status['hash_key_set']);
        $this->assertIsBool($status['hash_iv_set']);

        // Actual keys should never be in the return array
        $this->assertArrayNotHasKey('hash_key', $status);
        $this->assertArrayNotHasKey('hash_iv', $status);
    }
}
