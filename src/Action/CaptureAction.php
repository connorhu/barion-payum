<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Action;

use ArrayAccess;
use CodeConjure\BarionPayum\Api\BarionApi;
use CodeConjure\BarionPayum\Api\BarionClient;
use CodeConjure\BarionPayum\Api\Dto\PreparePaymentRequest;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;

final class CaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function __construct(
        private readonly BarionClient $client,
        private readonly BarionApi $api,
    ) {}

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var Capture $request */
        $details = ArrayObject::ensureArrayObject($request->getModel());

        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        // Return from Barion — payment already prepared, just update state
        if (!empty($details['barion_payment_id']) && isset($httpRequest->query['paymentId'])) {
            return;
        }

        // Already has a payment ID but user hasn't returned yet — redirect again
        if (!empty($details['barion_payment_id'])) {
            throw new HttpRedirect($this->buildCheckoutUrl($details['barion_payment_id']));
        }

        // First capture: prepare payment
        $response = $this->client->preparePayment(new PreparePaymentRequest(
            posKey: $this->api->posKey,
            paymentType: 'Immediate',
            paymentRequestId: (string) $details['order_number'],
            total: (int) $details['total_amount'],
            currency: (string) ($details['currency_code'] ?? $this->api->currency),
            redirectUrl: (string) $details['redirect_url'],
            callbackUrl: (string) $details['callback_url'],
            orderNumber: (string) $details['order_number'],
        ));

        $details['barion_payment_id'] = $response->paymentId;

        throw new HttpRedirect($this->buildCheckoutUrl($response->paymentId));
    }

    public function supports($request): bool
    {
        return $request instanceof Capture
            && $request->getModel() instanceof ArrayAccess;
    }

    private function buildCheckoutUrl(string $paymentId): string
    {
        $base = $this->api->sandbox
            ? 'https://secure.test.barion.com'
            : 'https://secure.barion.com';

        return $base . '/Pay?id=' . urlencode($paymentId);
    }
}
