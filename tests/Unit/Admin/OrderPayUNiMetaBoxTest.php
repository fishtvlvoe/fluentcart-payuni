<?php
/**
 * Unit tests for OrderPayUNiMetaBox.
 *
 * @package BuyGoFluentCart\PayUNi\Tests\Unit\Admin
 */

namespace BuyGoFluentCart\PayUNi\Tests\Unit\Admin;

use PHPUnit\Framework\TestCase;
use BuyGoFluentCart\PayUNi\Admin\OrderPayUNiMetaBox;

class OrderPayUNiMetaBoxTest extends TestCase
{
    private OrderPayUNiMetaBox $metaBox;

    protected function setUp(): void
    {
        parent::setUp();
        // Create instance without triggering WordPress hooks
        $this->metaBox = new OrderPayUNiMetaBox(false);
    }

    // getBankName tests
    public function testGetBankNameReturnsKnownBankName(): void
    {
        $this->assertEquals('中國信託', $this->metaBox->getBankName('822'));
        $this->assertEquals('玉山銀行', $this->metaBox->getBankName('808'));
        $this->assertEquals('台新銀行', $this->metaBox->getBankName('812'));
    }

    public function testGetBankNameReturnsCodeForUnknownBank(): void
    {
        $this->assertEquals('999', $this->metaBox->getBankName('999'));
        $this->assertEquals('ABC', $this->metaBox->getBankName('ABC'));
    }

    public function testGetBankNameHandlesEmptyString(): void
    {
        $this->assertEquals('', $this->metaBox->getBankName(''));
    }

    // getStoreName tests
    public function testGetStoreNameReturnsKnownStoreName(): void
    {
        $this->assertEquals('7-ELEVEN', $this->metaBox->getStoreName('1'));
        $this->assertEquals('全家 FamilyMart', $this->metaBox->getStoreName('2'));
        $this->assertEquals('萊爾富 Hi-Life', $this->metaBox->getStoreName('3'));
        $this->assertEquals('OK 超商', $this->metaBox->getStoreName('4'));
    }

    public function testGetStoreNameReturnsTypeForUnknownStore(): void
    {
        $this->assertEquals('5', $this->metaBox->getStoreName('5'));
        $this->assertEquals('unknown', $this->metaBox->getStoreName('unknown'));
    }

    public function testGetStoreNameHandlesEmptyString(): void
    {
        $this->assertEquals('', $this->metaBox->getStoreName(''));
    }

    // formatExpireDate tests
    public function testFormatExpireDateFormatsValidDate(): void
    {
        $this->assertEquals('2025/02/15 14:30', $this->metaBox->formatExpireDate('2025-02-15 14:30:00'));
        $this->assertEquals('2025/12/31 23:59', $this->metaBox->formatExpireDate('2025/12/31 23:59:59'));
    }

    public function testFormatExpireDateHandlesEmptyString(): void
    {
        $this->assertEquals('', $this->metaBox->formatExpireDate(''));
    }

    public function testFormatExpireDateReturnsOriginalForInvalidDate(): void
    {
        $this->assertEquals('invalid-date', $this->metaBox->formatExpireDate('invalid-date'));
    }

    // detectCardBrand tests
    public function testDetectCardBrandDetectsVisa(): void
    {
        $this->assertEquals('Visa', $this->metaBox->detectCardBrand('4123'));
        $this->assertEquals('Visa', $this->metaBox->detectCardBrand('4'));
    }

    public function testDetectCardBrandDetectsMastercard(): void
    {
        $this->assertEquals('Mastercard', $this->metaBox->detectCardBrand('5123'));
        $this->assertEquals('Mastercard', $this->metaBox->detectCardBrand('51'));
        $this->assertEquals('Mastercard', $this->metaBox->detectCardBrand('55'));
    }

    public function testDetectCardBrandDetectsJCB(): void
    {
        $this->assertEquals('JCB', $this->metaBox->detectCardBrand('3512'));
        $this->assertEquals('JCB', $this->metaBox->detectCardBrand('35'));
    }

    public function testDetectCardBrandDetectsAmex(): void
    {
        $this->assertEquals('American Express', $this->metaBox->detectCardBrand('3456'));
        $this->assertEquals('American Express', $this->metaBox->detectCardBrand('37'));
    }

    public function testDetectCardBrandDetectsUnionPay(): void
    {
        $this->assertEquals('UnionPay', $this->metaBox->detectCardBrand('6212'));
        $this->assertEquals('UnionPay', $this->metaBox->detectCardBrand('62'));
    }

    public function testDetectCardBrandReturnsDefaultForUnknown(): void
    {
        $this->assertEquals('信用卡', $this->metaBox->detectCardBrand('9999'));
        $this->assertEquals('信用卡', $this->metaBox->detectCardBrand(''));
        $this->assertEquals('信用卡', $this->metaBox->detectCardBrand('1'));
    }

    // getStatusLabel tests
    public function testGetStatusLabelReturnsKnownLabels(): void
    {
        $this->assertEquals('成功', $this->metaBox->getStatusLabel('succeeded'));
        $this->assertEquals('失敗', $this->metaBox->getStatusLabel('failed'));
        $this->assertEquals('處理中', $this->metaBox->getStatusLabel('pending'));
        $this->assertEquals('已取消', $this->metaBox->getStatusLabel('cancelled'));
        $this->assertEquals('已退款', $this->metaBox->getStatusLabel('refunded'));
    }

    public function testGetStatusLabelReturnsOriginalForUnknown(): void
    {
        $this->assertEquals('unknown', $this->metaBox->getStatusLabel('unknown'));
    }

    // getPaymentTypeLabel tests
    public function testGetPaymentTypeLabelReturnsKnownLabels(): void
    {
        $this->assertEquals('信用卡', $this->metaBox->getPaymentTypeLabel('credit'));
        $this->assertEquals('ATM 轉帳', $this->metaBox->getPaymentTypeLabel('atm'));
        $this->assertEquals('超商代碼', $this->metaBox->getPaymentTypeLabel('cvs'));
    }

    public function testGetPaymentTypeLabelReturnsOriginalForUnknown(): void
    {
        $this->assertEquals('unknown', $this->metaBox->getPaymentTypeLabel('unknown'));
    }
}
