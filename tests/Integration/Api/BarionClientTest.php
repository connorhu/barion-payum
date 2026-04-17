<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Tests\Integration\Api;

use CodeConjure\BarionPayum\Api\BarionApi;
use CodeConjure\BarionPayum\Api\BarionClient;
use CodeConjure\BarionPayum\Api\Dto\PreparePaymentRequest;
use CodeConjure\BarionPayum\Api\Dto\RefundRequest;
use CodeConjure\BarionPayum\Exception\BarionApiException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

final class BarionClientTest extends TestCase
{
    private function makeClient(MockResponse ...$responses): BarionClient
    {
        $api = new BarionApi(posKey: 'test-pos-key', sandbox: true);
        $mock = new MockHttpClient($responses);
        $psr18 = new Psr18Client($mock);

        return new BarionClient($api, $psr18, $psr18, $psr18);
    }

    private function fixture(string $name): string
    {
        return file_get_contents(__DIR__ . '/fixtures/' . $name . '.json');
    }

    public function testPreparePaymentReturnsPaymentId(): void
    {
        $client = $this->makeClient(
            new MockResponse($this->fixture('prepare_payment_success'), ['http_code' => 200])
        );

        $response = $client->preparePayment(new PreparePaymentRequest(
            posKey: 'test-pos-key',
            paymentType: 'Immediate',
            paymentRequestId: 'order-42',
            total: 5000,
            currency: 'HUF',
            redirectUrl: 'https://shop.example.com/return',
            callbackUrl: 'https://shop.example.com/notify',
            orderNumber: 'order-42',
        ));

        self::assertSame('pay-abc-123', $response->paymentId);
        self::assertTrue($response->isSuccessful());
    }

    public function testPreparePaymentThrowsOnBarionError(): void
    {
        $client = $this->makeClient(
            new MockResponse($this->fixture('prepare_payment_error'), ['http_code' => 200])
        );

        $this->expectException(BarionApiException::class);

        $client->preparePayment(new PreparePaymentRequest(
            posKey: 'bad-key',
            paymentType: 'Immediate',
            paymentRequestId: 'order-1',
            total: 1000,
            currency: 'HUF',
            redirectUrl: 'https://shop.example.com/return',
            callbackUrl: 'https://shop.example.com/notify',
            orderNumber: 'order-1',
        ));
    }

    public function testGetPaymentStateReturnsStatus(): void
    {
        $client = $this->makeClient(
            new MockResponse($this->fixture('payment_state_succeeded'), ['http_code' => 200])
        );

        $response = $client->getPaymentState('pay-abc-123');

        self::assertSame('Succeeded', $response->status);
        self::assertSame(5000, $response->total);
        self::assertCount(1, $response->transactions);
    }

    public function testRefundPaymentReturnsPaymentId(): void
    {
        $client = $this->makeClient(
            new MockResponse($this->fixture('refund_success'), ['http_code' => 200])
        );

        $response = $client->refundPayment(new RefundRequest(
            posKey: 'test-pos-key',
            paymentId: 'pay-abc-123',
            amount: 1000,
            currency: 'HUF',
        ));

        self::assertSame('pay-abc-123', $response->paymentId);
        self::assertTrue($response->isSuccessful());
    }

    public function testCancelAuthorizationSucceeds(): void
    {
        $client = $this->makeClient(
            new MockResponse($this->fixture('base_success'), ['http_code' => 200])
        );

        $response = $client->cancelAuthorization('pay-abc-123');

        self::assertTrue($response->isSuccessful());
    }

    public function testFinishReservationSucceeds(): void
    {
        $client = $this->makeClient(
            new MockResponse($this->fixture('base_success'), ['http_code' => 200])
        );

        $response = $client->finishReservation('pay-abc-123', [
            ['POSTransactionId' => 'order-42', 'Total' => 5000],
        ]);

        self::assertTrue($response->isSuccessful());
    }
}
