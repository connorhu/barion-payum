<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Tests\Unit\Api\Dto;

use CodeConjure\BarionPayum\Api\Dto\PreparePaymentRequest;
use PHPUnit\Framework\TestCase;

final class PreparePaymentRequestTest extends TestCase
{
    private function make(array $overrides = []): PreparePaymentRequest
    {
        return new PreparePaymentRequest(
            posKey:           $overrides['posKey'] ?? 'pos-key',
            paymentType:      $overrides['paymentType'] ?? 'Immediate',
            paymentRequestId: $overrides['paymentRequestId'] ?? 'order-42',
            total:            $overrides['total'] ?? 5000,
            currency:         $overrides['currency'] ?? 'HUF',
            redirectUrl:      $overrides['redirectUrl'] ?? 'https://shop.example.com/return',
            callbackUrl:      $overrides['callbackUrl'] ?? 'https://shop.example.com/notify',
            orderNumber:      $overrides['orderNumber'] ?? 'order-42',
            payee:            $overrides['payee'] ?? 'shop@example.com',
            items:            $overrides['items'] ?? [],
            guestCheckOut:    $overrides['guestCheckOut'] ?? true,
            fundingSources:   $overrides['fundingSources'] ?? ['All'],
        );
    }

    /**
     * Barion rejects a Payment/Start call that is missing any of these, so the
     * payload must carry all of them. Without this test the omission is
     * invisible: every other test in the suite runs against a canned success
     * response, which is returned no matter what we send.
     */
    public function testPayloadCarriesEveryFieldBarionRequires(): void
    {
        $payload = $this->make()->toArray();

        foreach (['POSKey', 'PaymentType', 'GuestCheckOut', 'FundingSources', 'PaymentRequestId', 'Transactions', 'Currency', 'RedirectUrl', 'CallbackUrl', 'Locale'] as $key) {
            self::assertArrayHasKey($key, $payload, sprintf('Missing required field "%s".', $key));
        }

        self::assertTrue($payload['GuestCheckOut']);
        self::assertSame(['All'], $payload['FundingSources']);
    }

    public function testPayeeReachesTheTransaction(): void
    {
        $payload = $this->make(['payee' => 'boltom@example.com'])->toArray();

        self::assertSame('boltom@example.com', $payload['Transactions'][0]['Payee']);
    }

    public function testGuestCheckOutAndFundingSourcesAreOverridable(): void
    {
        $payload = $this->make([
            'guestCheckOut'  => false,
            'fundingSources' => ['Balance'],
        ])->toArray();

        self::assertFalse($payload['GuestCheckOut']);
        self::assertSame(['Balance'], $payload['FundingSources']);
    }

    public function testTransactionKeepsItsOtherFields(): void
    {
        $payload = $this->make(['orderNumber' => 'order-7', 'total' => 1234])->toArray();
        $tx      = $payload['Transactions'][0];

        self::assertSame('order-7', $tx['POSTransactionId']);
        self::assertSame(1234, $tx['Total']);
        self::assertSame([], $tx['Items']);
    }
}
