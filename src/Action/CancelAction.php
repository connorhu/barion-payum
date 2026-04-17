<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Action;

use ArrayAccess;
use CodeConjure\BarionPayum\Api\BarionApi;
use CodeConjure\BarionPayum\Api\BarionClient;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Cancel;

final class CancelAction implements ActionInterface
{
    public function __construct(
        private readonly BarionClient $client,
        private readonly BarionApi $api,
    ) {}

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var Cancel $request */
        $details = ArrayObject::ensureArrayObject($request->getModel());

        $this->client->cancelAuthorization((string) $details['barion_payment_id']);
    }

    public function supports($request): bool
    {
        return $request instanceof Cancel
            && $request->getModel() instanceof ArrayAccess;
    }
}
