<?php
/**
 * Unit tests for UserGuidePage.
 *
 * @package BuyGoFluentCart\PayUNi\Tests\Unit\Admin
 */

namespace BuyGoFluentCart\PayUNi\Tests\Unit\Admin;

use BuyGoFluentCart\PayUNi\Admin\UserGuidePage;
use PHPUnit\Framework\TestCase;

/**
 * Test case for UserGuidePage.
 */
class UserGuidePageTest extends TestCase
{
    /**
     * @var UserGuidePage
     */
    private $page;

    /**
     * Set up test fixtures.
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Create instance without registering WordPress hooks
        $this->page = new UserGuidePage(false);
    }

    /**
     * Test Quick Start section contains setup steps.
     */
    public function testQuickStartSectionContainsSetupSteps(): void
    {
        $html = $this->page->renderQuickStartSection();

        // Should contain numbered setup steps
        $this->assertStringContainsString('PayUNi', $html);
        $this->assertStringContainsString('MerID', $html);
        $this->assertStringContainsString('Hash Key', $html);
        $this->assertStringContainsString('Webhook', $html);
    }

    /**
     * Test Quick Start section contains quick links.
     */
    public function testQuickStartSectionContainsQuickLinks(): void
    {
        $html = $this->page->renderQuickStartSection();

        // Should contain links to PayUNi pages
        $this->assertStringContainsString('payuni-settings', $html);
        $this->assertStringContainsString('payuni-webhook-logs', $html);
        $this->assertStringContainsString('payuni-dashboard', $html);
    }

    /**
     * Test Feature Locations section contains all main features.
     */
    public function testFeatureLocationsSectionContainsAllFeatures(): void
    {
        $html = $this->page->renderFeatureLocationsSection();

        // Should contain all documented feature locations
        $this->assertStringContainsString('訂單', $html);
        $this->assertStringContainsString('訂閱', $html);
        $this->assertStringContainsString('Webhook', $html);
        $this->assertStringContainsString('Dashboard', $html);
    }

    /**
     * Test FAQ section contains all required categories.
     */
    public function testFaqSectionContainsAllCategories(): void
    {
        $html = $this->page->renderFAQSection();

        // Should contain all 4 FAQ categories from CONTEXT.md
        $this->assertStringContainsString('金流設定', $html);
        $this->assertStringContainsString('Webhook', $html);
        $this->assertStringContainsString('訂閱', $html);
        $this->assertStringContainsString('ATM', $html);
    }

    /**
     * Test FAQ section contains collapsible structure.
     */
    public function testFaqSectionHasCollapsibleStructure(): void
    {
        $html = $this->page->renderFAQSection();

        // Should contain accordion CSS classes
        $this->assertStringContainsString('faq-item', $html);
        $this->assertStringContainsString('faq-question', $html);
        $this->assertStringContainsString('faq-answer', $html);
    }

    /**
     * Test Troubleshooting section contains error table.
     */
    public function testTroubleshootingSectionContainsErrorTable(): void
    {
        $html = $this->page->renderTroubleshootingSection();

        // Should contain error reference table
        $this->assertStringContainsString('error-table', $html);
        $this->assertStringContainsString('Hash', $html);
        $this->assertStringContainsString('商店代號', $html);
    }

    /**
     * Test Troubleshooting section contains checklist.
     */
    public function testTroubleshootingSectionContainsChecklist(): void
    {
        $html = $this->page->renderTroubleshootingSection();

        // Should contain Webhook checklist
        $this->assertStringContainsString('checklist', $html);
        $this->assertStringContainsString('HTTPS', $html);
        $this->assertStringContainsString('防火牆', $html);
    }

    /**
     * Test sidebar navigation items match content sections.
     */
    public function testSidebarNavigationMatchesContentSections(): void
    {
        $navItems = $this->page->getNavigationItems();

        // Should have 4 navigation items
        $this->assertCount(4, $navItems);

        // Each item should have id and label
        foreach ($navItems as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('label', $item);
        }
    }

    /**
     * Test page slug is correctly defined.
     */
    public function testPageSlugConstant(): void
    {
        $reflection = new \ReflectionClass(UserGuidePage::class);
        $constant = $reflection->getConstant('PAGE_SLUG');

        $this->assertEquals('payuni-user-guide', $constant);
    }
}
