<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Tests\Unit\Action;

use ArrayObject;
use CodeConjure\BarionPayum\Action\ConvertPaymentAction;
use Payum\Core\Model\Payment;
use Payum\Core\Request\Convert;
use PHPUnit\Framework\TestCase;

final class ConvertPaymentActionTest extends TestCase
{
    private ConvertPaymentAction $action;

    protected function setUp(): void
    {
        $this->action = new ConvertPaymentAction();
    }

    public function testSupportsConvertWithPayment(): void
    {
        $request = new Convert(new Payment(), 'array');
        self::assertTrue($this->action->supports($request));
    }

    public function testDoesNotSupportOtherRequests(): void
    {
        self::assertFalse($this->action->supports(new \stdClass()));
    }

    public function testConvertsPaymentToDetails(): void
    {
        $payment = new Payment();
        $payment->setNumber('order-42');
        $payment->setTotalAmount(5000);
        $payment->setCurrencyCode('HUF');
        $payment->setDescription('Test order');
        $payment->setClientEmail('customer@example.com');
        $payment->setClientId('client-1');

        $request = new Convert($payment, 'array');
        $this->action->execute($request);
        $details = $request->getResult();

        self::assertSame('order-42', $details['order_number']);
        self::assertSame(5000, $details['total_amount']);
        self::assertSame('HUF', $details['currency_code']);
        self::assertSame('Test order', $details['description']);
        self::assertSame('customer@example.com', $details['client_email']);
        self::assertSame('client-1', $details['client_id']);
    }
}
