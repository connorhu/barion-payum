<?php
// src/Api/BarionApi.php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Api;

readonly class BarionApi
{
    public function __construct(
        public string $posKey,
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
