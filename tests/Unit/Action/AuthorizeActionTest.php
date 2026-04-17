<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Tests\Unit\Action;

use ArrayObject;
use CodeConjure\BarionPayum\Action\AuthorizeAction;
use CodeConjure\BarionPayum\Api\BarionApi;
use CodeConjure\BarionPayum\Api\BarionClient;
use CodeConjure\BarionPayum\Api\Dto\PreparePaymentResponse;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\GetHttpRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AuthorizeActionTest extends TestCase
{
    private BarionClient&MockObject $client;
    private GatewayInterface&MockObject $gateway;
    private BarionApi $api;
    private AuthorizeAction $action;

    protected function setUp(): void
    {
        $this->client  = $this->createMock(BarionClient::class);
        $this->gateway = $this->createMock(GatewayInterface::class);
        $this->api     = new BarionApi(posKey: 'test-key', sandbox: true);
        $this->action  = new AuthorizeAction($this->client, $this->api);
        $this->action->setGateway($this->gateway);
    }

    public function testSupportsAuthorizeWithArrayAccess(): void
    {
        self::assertTrue($this->action->supports(new Authorize(new ArrayObject())));
    }

    public function testSendsReservationPaymentType(): void
    {
        $details = new ArrayObject([
            'order_number'  => 'order-55',
            'total_amount'  => 3000,
            'currency_code' => 'HUF',
            'redirect_url'  => 'https://shop.example.com/return',
            'callback_url'  => 'https://shop.example.com/notify',
        ]);

        $this->gateway->method('execute')->willReturnCallback(function ($r) {});

        $capturedRequest = null;
        $this->client->expects(self::once())
            ->method('preparePayment')
            ->willReturnCallback(function ($req) use (&$capturedRequest) {
                $capturedRequest = $req;
                return PreparePaymentResponse::fromArray([
                    'PaymentId' => 'pay-rsv-1',
                    'Status'    => 'Prepared',
                    'Errors'    => [],
                ]);
            });

        try {
            $this->action->execute(new Authorize($details));
        } catch (\Payum\Core\Reply\HttpRedirect) {}

        self::assertSame('Reservation', $capturedRequest->paymentType);
    }
}
