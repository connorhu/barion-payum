<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Tests\Unit\Action;

use ArrayObject;
use CodeConjure\BarionPayum\Action\NotifyAction;
use CodeConjure\BarionPayum\Api\BarionClient;
use CodeConjure\BarionPayum\Api\Dto\PaymentStateResponse;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class NotifyActionTest extends TestCase
{
    private BarionClient&MockObject $client;
    private GatewayInterface&MockObject $gateway;
    private NotifyAction $action;

    protected function setUp(): void
    {
        $this->client  = $this->createMock(BarionClient::class);
        $this->gateway = $this->createMock(GatewayInterface::class);
        $this->action  = new NotifyAction($this->client);
        $this->action->setGateway($this->gateway);
    }

    public function testSupportsNotifyWithArrayAccess(): void
    {
        self::assertTrue($this->action->supports(new Notify(new ArrayObject())));
    }

    public function testFetchesPaymentStateFromCallbackQueryParam(): void
    {
        $details = new ArrayObject(['barion_payment_id' => 'pay-abc-123']);

        $this->gateway->method('execute')->willReturnCallback(function ($request) {
            if ($request instanceof GetHttpRequest) {
                $request->query = ['paymentId' => 'pay-abc-123'];
            }
        });

        $this->client->expects(self::once())
            ->method('getPaymentState')
            ->with('pay-abc-123')
            ->willReturn(PaymentStateResponse::fromArray([
                'PaymentId' => 'pay-abc-123',
                'Status'    => 'Succeeded',
                'Errors'    => [],
            ]));

        $this->action->execute(new Notify($details));

        self::assertSame('Succeeded', $details['barion_status']);
    }
}
