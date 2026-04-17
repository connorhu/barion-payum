<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Tests\Unit\Action;

use ArrayObject;
use CodeConjure\BarionPayum\Action\CaptureAction;
use CodeConjure\BarionPayum\Api\BarionApi;
use CodeConjure\BarionPayum\Api\BarionClient;
use CodeConjure\BarionPayum\Api\Dto\PreparePaymentResponse;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CaptureActionTest extends TestCase
{
    private BarionClient&MockObject $client;
    private GatewayInterface&MockObject $gateway;
    private BarionApi $api;
    private CaptureAction $action;

    protected function setUp(): void
    {
        $this->client  = $this->createMock(BarionClient::class);
        $this->gateway = $this->createMock(GatewayInterface::class);
        $this->api     = new BarionApi(posKey: 'test-key', sandbox: true);
        $this->action  = new CaptureAction($this->client, $this->api);
        $this->action->setGateway($this->gateway);
    }

    public function testSupportsCaptureWithArrayAccess(): void
    {
        $request = new Capture(new ArrayObject());
        self::assertTrue($this->action->supports($request));
    }

    public function testDoesNotSupportOtherRequests(): void
    {
        self::assertFalse($this->action->supports(new \stdClass()));
    }

    public function testInitiatesPaymentAndStoresPaymentId(): void
    {
        $details = new ArrayObject([
            'order_number'  => 'order-42',
            'total_amount'  => 5000,
            'currency_code' => 'HUF',
            'redirect_url'  => 'https://shop.example.com/return',
            'callback_url'  => 'https://shop.example.com/notify',
        ]);

        $this->gateway->method('execute')->willReturnCallback(function ($request) {
            if ($request instanceof GetHttpRequest) {
                // No paymentId in query yet — first visit
            }
        });

        $this->client->expects(self::once())
            ->method('preparePayment')
            ->willReturn(PreparePaymentResponse::fromArray([
                'PaymentId' => 'pay-abc-123',
                'Status'    => 'Prepared',
                'Errors'    => [],
            ]));

        $request = new Capture($details);

        try {
            $this->action->execute($request);
        } catch (\Payum\Core\Reply\HttpRedirect $redirect) {
            self::assertStringContainsString('pay-abc-123', $redirect->getUrl());
        }

        self::assertSame('pay-abc-123', $details['barion_payment_id']);
    }

    public function testSkipsReprepareIfPaymentIdAlreadySet(): void
    {
        $details = new ArrayObject([
            'barion_payment_id' => 'pay-existing',
            'order_number'      => 'order-99',
        ]);

        $this->gateway->method('execute')->willReturnCallback(function ($request) {
            if ($request instanceof GetHttpRequest) {
                $request->query = ['paymentId' => 'pay-existing'];
            }
        });

        $this->client->expects(self::never())->method('preparePayment');

        $request = new Capture($details);
        $this->action->execute($request);
    }
}
