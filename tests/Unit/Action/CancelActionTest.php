<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Tests\Unit\Action;

use ArrayObject;
use CodeConjure\BarionPayum\Action\CancelAction;
use CodeConjure\BarionPayum\Api\BarionApi;
use CodeConjure\BarionPayum\Api\BarionClient;
use CodeConjure\BarionPayum\Api\Dto\BaseResponse;
use Payum\Core\Request\Cancel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CancelActionTest extends TestCase
{
    private BarionClient&MockObject $client;
    private CancelAction $action;

    protected function setUp(): void
    {
        $this->client = $this->createMock(BarionClient::class);
        $this->action = new CancelAction(
            $this->client,
            new BarionApi(posKey: 'test-key', sandbox: true),
        );
    }

    public function testSupportsCancelWithArrayAccess(): void
    {
        self::assertTrue($this->action->supports(new Cancel(new ArrayObject())));
    }

    public function testCallsCancelAuthorization(): void
    {
        $details = new ArrayObject(['barion_payment_id' => 'pay-rsv-1']);

        $this->client->expects(self::once())
            ->method('cancelAuthorization')
            ->with('pay-rsv-1')
            ->willReturn(BaseResponse::fromArray(['Errors' => []]));

        $this->action->execute(new Cancel($details));
    }
}
