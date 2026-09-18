<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Tests\Unit;

use CodeConjure\BarionPayum\Api\BarionApi;
use CodeConjure\BarionPayum\BarionGatewayFactory;
use LogicException;
use Payum\Core\Bridge\Spl\ArrayObject as PayumArrayObject;
use PHPUnit\Framework\TestCase;

final class BarionGatewayFactoryTest extends TestCase
{
    private function config(array $options): PayumArrayObject
    {
        $factory = new BarionGatewayFactory();

        return PayumArrayObject::ensureArrayObject($factory->createConfig($options));
    }

    public function testApiCarriesThePayee(): void
    {
        $config = $this->config(['pos_key' => 'pos', 'payee' => 'shop@example.com']);

        $api = $config['barion.api']($config);

        self::assertInstanceOf(BarionApi::class, $api);
        self::assertSame('shop@example.com', $api->payee);
        self::assertSame('pos', $api->posKey);
    }

    /** A gateway without a payee cannot start a payment, so say so early. */
    public function testMissingPayeeIsRefusedWithAClearMessage(): void
    {
        $config = $this->config(['pos_key' => 'pos']);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/payee/');

        $config['barion.api']($config);
    }
}
