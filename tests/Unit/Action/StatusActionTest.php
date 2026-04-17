<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Tests\Unit\Action;

use ArrayObject;
use CodeConjure\BarionPayum\Action\StatusAction;
use CodeConjure\BarionPayum\Api\BarionClient;
use CodeConjure\BarionPayum\Api\Dto\PaymentStateResponse;
use Payum\Core\Request\GetHumanStatus;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
final class StatusActionTest extends TestCase
{
    private BarionClient&MockObject $client;
    private StatusAction $action;

    protected function setUp(): void
    {
        $this->client = $this->createMock(BarionClient::class);
        $this->action = new StatusAction($this->client);
    }

    private function makeRequest(array $details): GetHumanStatus
    {
        return new GetHumanStatus(new ArrayObject($details));
    }

    public function testNewWhenNoPaymentId(): void
    {
        $request = $this->makeRequest([]);
        $this->action->execute($request);
        self::assertTrue($request->isNew());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('barionStatusProvider')]
    public function testStatusMapping(string $barionStatus, string $assertMethod): void
    {
        $this->client->method('getPaymentState')->willReturn(
            PaymentStateResponse::fromArray(['Status' => $barionStatus, 'Errors' => []])
        );

        $request = $this->makeRequest(['barion_payment_id' => 'pay-123']);
        $this->action->execute($request);

        self::assertTrue($request->$assertMethod(), "Expected $assertMethod() for Barion status '$barionStatus'");
    }

    public static function barionStatusProvider(): array
    {
        return [
            'Prepared → pending'            => ['Prepared', 'isPending'],
            'Started → pending'             => ['Started', 'isPending'],
            'InProgress → pending'          => ['InProgress', 'isPending'],
            'Succeeded → captured'          => ['Succeeded', 'isCaptured'],
            'PartiallySucceeded → captured' => ['PartiallySucceeded', 'isCaptured'],
            'Reserved → authorized'         => ['Reserved', 'isAuthorized'],
            'Canceled → canceled'           => ['Canceled', 'isCanceled'],
            'Failed → failed'               => ['Failed', 'isFailed'],
            'Expired → expired'             => ['Expired', 'isExpired'],
        ];
    }
}
