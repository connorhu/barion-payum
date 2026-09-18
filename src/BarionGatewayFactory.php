<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum;

use CodeConjure\BarionPayum\Action\AuthorizeAction;
use CodeConjure\BarionPayum\Action\CancelAction;
use CodeConjure\BarionPayum\Action\CaptureAction;
use CodeConjure\BarionPayum\Action\ConvertPaymentAction;
use CodeConjure\BarionPayum\Action\NotifyAction;
use CodeConjure\BarionPayum\Action\RefundAction;
use CodeConjure\BarionPayum\Action\StatusAction;
use CodeConjure\BarionPayum\Api\BarionApi;
use CodeConjure\BarionPayum\Api\BarionClient;
use Payum\Core\Bridge\Spl\ArrayObject as PayumArrayObject;
use LogicException;
use Payum\Core\GatewayFactory;

final class BarionGatewayFactory extends GatewayFactory
{
    protected function populateConfig(PayumArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name'  => 'barion',
            'payum.factory_title' => 'Barion',

            'barion.api' => function (PayumArrayObject $c): BarionApi {
                $payee = (string) ($c['payee'] ?? '');

                // Fail here rather than at the API: Barion answers a missing
                // Payee with a generic error that is hard to trace back.
                if ('' === $payee) {
                    throw new LogicException(
                        'The "payee" option is required — it is the shop\'s own Barion e-mail address.'
                    );
                }

                return new BarionApi(
                    posKey: (string) $c['pos_key'],
                    payee: $payee,
                    sandbox: (bool) $c['sandbox'],
                    currency: (string) ($c['currency'] ?? 'HUF'),
                );
            },

            'barion.client' => fn(PayumArrayObject $c) => new BarionClient(
                api: $c['barion.api'],
                httpClient: $c['barion.http_client'],
                requestFactory: $c['barion.request_factory'],
                streamFactory: $c['barion.stream_factory'],
            ),

            'payum.action.capture' => fn(PayumArrayObject $c) => new CaptureAction(
                $c['barion.client'],
                $c['barion.api'],
            ),
            'payum.action.authorize' => fn(PayumArrayObject $c) => new AuthorizeAction(
                $c['barion.client'],
                $c['barion.api'],
            ),
            'payum.action.refund' => fn(PayumArrayObject $c) => new RefundAction(
                $c['barion.client'],
                $c['barion.api'],
            ),
            'payum.action.cancel' => fn(PayumArrayObject $c) => new CancelAction(
                $c['barion.client'],
                $c['barion.api'],
            ),
            'payum.action.status' => fn(PayumArrayObject $c) => new StatusAction(
                $c['barion.client'],
            ),
            'payum.action.notify' => fn(PayumArrayObject $c) => new NotifyAction(
                $c['barion.client'],
            ),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ]);

        $config->defaults([
            'pos_key'  => null,
            'payee'    => null,
            'sandbox'  => true,
            'currency' => 'HUF',
        ]);
    }
}
