<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject as PayumArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;

final class ConvertPaymentAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var Convert $request */
        $payment = $request->getSource();
        $details = PayumArrayObject::ensureArrayObject($request->getResult() ?? []);

        $details['order_number']  = $payment->getNumber();
        $details['total_amount']  = $payment->getTotalAmount();
        $details['currency_code'] = $payment->getCurrencyCode();
        $details['description']   = $payment->getDescription();
        $details['client_email']  = $payment->getClientEmail();
        $details['client_id']     = $payment->getClientId();

        $request->setResult((array) $details);
    }

    public function supports($request): bool
    {
        return $request instanceof Convert
            && $request->getSource() instanceof PaymentInterface
            && $request->getTo() === 'array';
    }
}
