<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use TapPay\Tap\Enums\AuthorizeStatus;
use TapPay\Tap\Enums\RefundStatus;
use TapPay\Tap\Enums\SourceObject;
use TapPay\Tap\Tests\TestCase;

class EnumTest extends TestCase
{
    // ==================== RefundStatus Tests ====================

    #[Test]
    public function refund_status_initiated_is_pending(): void
    {
        $status = RefundStatus::INITIATED;

        $this->assertTrue($status->isPending());
        $this->assertFalse($status->isSuccessful());
        $this->assertFalse($status->hasFailed());
        $this->assertSame('Initiated', $status->label());
    }

    #[Test]
    public function refund_status_pending_is_pending(): void
    {
        $status = RefundStatus::PENDING;

        $this->assertTrue($status->isPending());
        $this->assertFalse($status->isSuccessful());
        $this->assertFalse($status->hasFailed());
        $this->assertSame('Pending', $status->label());
    }

    #[Test]
    public function refund_status_succeeded_is_successful(): void
    {
        $status = RefundStatus::SUCCEEDED;

        $this->assertTrue($status->isSuccessful());
        $this->assertFalse($status->isPending());
        $this->assertFalse($status->hasFailed());
        $this->assertSame('Succeeded', $status->label());
    }

    #[Test]
    public function refund_status_failed_has_failed(): void
    {
        $status = RefundStatus::FAILED;

        $this->assertTrue($status->hasFailed());
        $this->assertFalse($status->isSuccessful());
        $this->assertFalse($status->isPending());
        $this->assertSame('Failed', $status->label());
    }

    #[Test]
    public function refund_status_cancelled_has_failed(): void
    {
        $status = RefundStatus::CANCELLED;

        $this->assertTrue($status->hasFailed());
        $this->assertFalse($status->isSuccessful());
        $this->assertFalse($status->isPending());
        $this->assertSame('Cancelled', $status->label());
    }

    // ==================== AuthorizeStatus Tests ====================

    #[Test]
    public function authorize_status_initiated_is_pending(): void
    {
        $status = AuthorizeStatus::INITIATED;

        $this->assertTrue($status->isPending());
        $this->assertFalse($status->isSuccessful());
        $this->assertFalse($status->hasFailed());
        $this->assertSame('Initiated', $status->label());
    }

    #[Test]
    public function authorize_status_authorized_is_successful(): void
    {
        $status = AuthorizeStatus::AUTHORIZED;

        $this->assertTrue($status->isSuccessful());
        $this->assertFalse($status->isPending());
        $this->assertFalse($status->hasFailed());
        $this->assertSame('Authorized', $status->label());
    }

    #[Test]
    public function authorize_status_captured_is_neither_successful_nor_failed(): void
    {
        $status = AuthorizeStatus::CAPTURED;

        $this->assertFalse($status->isSuccessful());
        $this->assertFalse($status->isPending());
        $this->assertFalse($status->hasFailed());
        $this->assertSame('Captured', $status->label());
    }

    #[Test]
    public function authorize_status_cancelled_has_failed(): void
    {
        $status = AuthorizeStatus::CANCELLED;

        $this->assertTrue($status->hasFailed());
        $this->assertFalse($status->isSuccessful());
        $this->assertFalse($status->isPending());
        $this->assertSame('Cancelled', $status->label());
    }

    #[Test]
    public function authorize_status_failed_has_failed(): void
    {
        $status = AuthorizeStatus::FAILED;

        $this->assertTrue($status->hasFailed());
        $this->assertFalse($status->isSuccessful());
        $this->assertFalse($status->isPending());
        $this->assertSame('Failed', $status->label());
    }

    #[Test]
    public function authorize_status_declined_has_failed(): void
    {
        $status = AuthorizeStatus::DECLINED;

        $this->assertTrue($status->hasFailed());
        $this->assertFalse($status->isSuccessful());
        $this->assertFalse($status->isPending());
        $this->assertSame('Declined', $status->label());
    }

    #[Test]
    public function authorize_status_restricted_has_failed(): void
    {
        $status = AuthorizeStatus::RESTRICTED;

        $this->assertTrue($status->hasFailed());
        $this->assertFalse($status->isSuccessful());
        $this->assertFalse($status->isPending());
        $this->assertSame('Restricted', $status->label());
    }

    #[Test]
    public function authorize_status_void_has_failed(): void
    {
        $status = AuthorizeStatus::VOID;

        $this->assertTrue($status->hasFailed());
        $this->assertFalse($status->isSuccessful());
        $this->assertFalse($status->isPending());
        $this->assertSame('Void', $status->label());
    }

    #[Test]
    public function authorize_status_unknown_is_neither_successful_nor_failed(): void
    {
        $status = AuthorizeStatus::UNKNOWN;

        $this->assertFalse($status->isSuccessful());
        $this->assertFalse($status->isPending());
        $this->assertFalse($status->hasFailed());
        $this->assertSame('Unknown', $status->label());
    }

    // ==================== SourceObject Tests ====================

    #[Test]
    public function source_card_requires_redirect(): void
    {
        $source = SourceObject::SRC_CARD;

        $this->assertTrue($source->requiresRedirect());
        $this->assertFalse($source->isRegionalMethod());
        $this->assertFalse($source->isDigitalWallet());
        $this->assertFalse($source->isBNPL());
        $this->assertFalse($source->isToken());
        $this->assertFalse($source->isAuthorization());
        $this->assertNull($source->getCountry());
        $this->assertSame('Card Payment', $source->label());
    }

    #[Test]
    public function source_all_requires_redirect(): void
    {
        $source = SourceObject::SRC_ALL;

        $this->assertTrue($source->requiresRedirect());
        $this->assertSame('All Payment Methods', $source->label());
    }

    #[Test]
    public function source_knet_is_regional_method_from_kuwait(): void
    {
        $source = SourceObject::SRC_KNET;

        $this->assertTrue($source->requiresRedirect());
        $this->assertTrue($source->isRegionalMethod());
        $this->assertFalse($source->isDigitalWallet());
        $this->assertFalse($source->isBNPL());
        $this->assertSame('KW', $source->getCountry());
        $this->assertSame('KNET', $source->label());
    }

    #[Test]
    public function source_kfast_is_regional_method_from_kuwait(): void
    {
        $source = SourceObject::SRC_KFAST;

        $this->assertTrue($source->requiresRedirect());
        $this->assertTrue($source->isRegionalMethod());
        $this->assertSame('KW', $source->getCountry());
        $this->assertSame('KFAST', $source->label());
    }

    #[Test]
    public function source_mada_is_regional_method_from_saudi_arabia(): void
    {
        $source = SourceObject::SRC_MADA;

        $this->assertTrue($source->requiresRedirect());
        $this->assertTrue($source->isRegionalMethod());
        $this->assertSame('SA', $source->getCountry());
        $this->assertSame('Mada', $source->label());
    }

    #[Test]
    public function source_benefit_is_regional_method_from_bahrain(): void
    {
        $source = SourceObject::SRC_BENEFIT;

        $this->assertTrue($source->requiresRedirect());
        $this->assertTrue($source->isRegionalMethod());
        $this->assertSame('BH', $source->getCountry());
        $this->assertSame('Benefit', $source->label());
    }

    #[Test]
    public function source_omannet_is_regional_method_from_oman(): void
    {
        $source = SourceObject::SRC_OMANNET;

        $this->assertTrue($source->requiresRedirect());
        $this->assertTrue($source->isRegionalMethod());
        $this->assertSame('OM', $source->getCountry());
        $this->assertSame('OmanNet', $source->label());
    }

    #[Test]
    public function source_naps_is_regional_method_from_qatar(): void
    {
        $source = SourceObject::SRC_NAPS;

        $this->assertTrue($source->requiresRedirect());
        $this->assertTrue($source->isRegionalMethod());
        $this->assertSame('QA', $source->getCountry());
        $this->assertSame('NAPS', $source->label());
    }

    #[Test]
    public function source_fawry_is_regional_method_from_egypt(): void
    {
        $source = SourceObject::SRC_FAWRY;

        $this->assertTrue($source->requiresRedirect());
        $this->assertTrue($source->isRegionalMethod());
        $this->assertSame('EG', $source->getCountry());
        $this->assertSame('Fawry', $source->label());
    }

    #[Test]
    public function source_stc_pay_is_digital_wallet_from_saudi_arabia(): void
    {
        $source = SourceObject::SRC_STC_PAY;

        $this->assertTrue($source->requiresRedirect());
        $this->assertFalse($source->isRegionalMethod());
        $this->assertTrue($source->isDigitalWallet());
        $this->assertFalse($source->isBNPL());
        $this->assertSame('SA', $source->getCountry());
        $this->assertSame('STC Pay', $source->label());
    }

    #[Test]
    public function source_tabby_is_bnpl(): void
    {
        $source = SourceObject::SRC_TABBY;

        $this->assertTrue($source->requiresRedirect());
        $this->assertFalse($source->isRegionalMethod());
        $this->assertFalse($source->isDigitalWallet());
        $this->assertTrue($source->isBNPL());
        $this->assertNull($source->getCountry());
        $this->assertSame('Tabby', $source->label());
    }

    #[Test]
    public function source_deema_is_bnpl(): void
    {
        $source = SourceObject::SRC_DEEMA;

        $this->assertTrue($source->requiresRedirect());
        $this->assertFalse($source->isRegionalMethod());
        $this->assertFalse($source->isDigitalWallet());
        $this->assertTrue($source->isBNPL());
        $this->assertNull($source->getCountry());
        $this->assertSame('Deema', $source->label());
    }

    #[Test]
    public function source_token_does_not_require_redirect(): void
    {
        $source = SourceObject::TOKEN;

        $this->assertFalse($source->requiresRedirect());
        $this->assertFalse($source->isRegionalMethod());
        $this->assertFalse($source->isDigitalWallet());
        $this->assertFalse($source->isBNPL());
        $this->assertTrue($source->isToken());
        $this->assertFalse($source->isAuthorization());
        $this->assertNull($source->getCountry());
        $this->assertSame('Token', $source->label());
    }

    #[Test]
    public function source_auth_does_not_require_redirect(): void
    {
        $source = SourceObject::AUTH;

        $this->assertFalse($source->requiresRedirect());
        $this->assertFalse($source->isRegionalMethod());
        $this->assertFalse($source->isDigitalWallet());
        $this->assertFalse($source->isBNPL());
        $this->assertFalse($source->isToken());
        $this->assertTrue($source->isAuthorization());
        $this->assertNull($source->getCountry());
        $this->assertSame('Authorization', $source->label());
    }
}