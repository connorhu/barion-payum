<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Action;

use ArrayAccess;
use CodeConjure\BarionPayum\Api\BarionApi;
use CodeConjure\BarionPayum\Api\BarionClient;
use CodeConjure\BarionPayum\Api\Dto\RefundRequest;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Refund;

final class RefundAction implements ActionInterface
{
    public function __construct(
        private readonly BarionClient $client,
        private readonly BarionApi $api,
    ) {}

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var Refund $request */
        $details = ArrayObject::ensureArrayObject($request->getModel());

        $this->client->refundPayment(new RefundRequest(
            posKey: $this->api->posKey,
            paymentId: (string) $details['barion_payment_id'],
            amount: (int) $details['total_amount'],
            currency: (string) ($details['currency_code'] ?? $this->api->currency),
        ));
    }

    public function supports($request): bool
    {
        return $request instanceof Refund
            && $request->getModel() instanceof ArrayAccess;
    }
}
