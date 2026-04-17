<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Tests\Unit\Action;

use ArrayObject;
use CodeConjure\BarionPayum\Action\RefundAction;
use CodeConjure\BarionPayum\Api\BarionApi;
use CodeConjure\BarionPayum\Api\BarionClient;
use CodeConjure\BarionPayum\Api\Dto\RefundResponse;
use Payum\Core\Request\Refund;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RefundActionTest extends TestCase
{
    private BarionClient&MockObject $client;
    private RefundAction $action;

    protected function setUp(): void
    {
        $this->client = $this->createMock(BarionClient::class);
        $this->action = new RefundAction(
            $this->client,
            new BarionApi(posKey: 'test-key', sandbox: true),
        );
    }

    public function testSupportsRefundWithArrayAccess(): void
    {
        self::assertTrue($this->action->supports(new Refund(new ArrayObject())));
    }

    public function testCallsRefundPaymentWithCorrectAmount(): void
    {
        $details = new ArrayObject([
            'barion_payment_id' => 'pay-abc-123',
            'total_amount'      => 5000,
            'currency_code'     => 'HUF',
        ]);

        $this->client->expects(self::once())
            ->method('refundPayment')
            ->with(self::callback(fn($req) =>
                $req->paymentId === 'pay-abc-123'
                && $req->amount === 5000
                && $req->currency === 'HUF'
            ))
            ->willReturn(RefundResponse::fromArray(['PaymentId' => 'pay-abc-123', 'Errors' => []]));

        $this->action->execute(new Refund($details));
    }
}
