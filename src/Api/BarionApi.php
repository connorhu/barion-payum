<?php
// src/Api/BarionApi.php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Api;

readonly class BarionApi
{
    public function __construct(
        public string $posKey,
        /**
         * The shop's own Barion e-mail address. Barion requires a Payee on
         * every transaction of a Payment/Start call, so a gateway without it
         * cannot start a payment at all.
         */
        public string $payee,
        public bool $sandbox,
        public string $currency = 'HUF',
    ) {}

    public function getBaseUrl(): string
    {
        return $this->sandbox
            ? 'https://api.test.barion.com'
            : 'https://api.barion.com';
    }
}
