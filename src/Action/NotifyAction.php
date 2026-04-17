<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Action;

use ArrayAccess;
use CodeConjure\BarionPayum\Api\BarionClient;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;

final class NotifyAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function __construct(private readonly BarionClient $client) {}

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var Notify $request */
        $details = ArrayObject::ensureArrayObject($request->getModel());

        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        $paymentId = $httpRequest->query['paymentId'] ?? (string) $details['barion_payment_id'];

        $state = $this->client->getPaymentState($paymentId);

        $details['barion_status']     = $state->status;
        $details['barion_payment_id'] = $state->paymentId ?? $paymentId;
    }

    public function supports($request): bool
    {
        return $request instanceof Notify
            && $request->getModel() instanceof ArrayAccess;
    }
}
