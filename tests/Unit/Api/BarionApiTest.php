<?php
// tests/Unit/Api/BarionApiTest.php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Tests\Unit\Api;

use CodeConjure\BarionPayum\Api\BarionApi;
use PHPUnit\Framework\TestCase;

final class BarionApiTest extends TestCase
{
    public function testSandboxBaseUrl(): void
    {
        $api = new BarionApi(posKey: 'test-key', sandbox: true);
        self::assertSame('https://api.test.barion.com', $api->getBaseUrl());
    }

    public function testProductionBaseUrl(): void
    {
        $api = new BarionApi(posKey: 'prod-key', sandbox: false);
        self::assertSame('https://api.barion.com', $api->getBaseUrl());
    }

    public function testDefaultCurrency(): void
    {
        $api = new BarionApi(posKey: 'key', sandbox: true);
        self::assertSame('HUF', $api->currency);
    }

    public function testCustomCurrency(): void
    {
        $api = new BarionApi(posKey: 'key', sandbox: true, currency: 'EUR');
        self::assertSame('EUR', $api->currency);
    }
}
