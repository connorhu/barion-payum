<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Action;

use ArrayAccess;
use CodeConjure\BarionPayum\Api\BarionClient;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHumanStatus;

final class StatusAction implements ActionInterface
{
    public function __construct(private readonly BarionClient $client) {}

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var GetHumanStatus $request */
        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (empty($details['barion_payment_id'])) {
            $request->markNew();
            return;
        }

        $state = $this->client->getPaymentState($details['barion_payment_id']);

        match ($state->status) {
            'Prepared', 'Started', 'InProgress' => $request->markPending(),
            'Succeeded', 'PartiallySucceeded'   => $request->markCaptured(),
            'Reserved'                           => $request->markAuthorized(),
            'Canceled'                           => $request->markCanceled(),
            'Failed'                             => $request->markFailed(),
            'Expired'                            => $request->markExpired(),
            default                              => $request->markUnknown(),
        };
    }

    public function supports($request): bool
    {
        return $request instanceof GetHumanStatus
            && $request->getModel() instanceof ArrayAccess;
    }
}
