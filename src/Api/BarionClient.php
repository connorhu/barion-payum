<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Api;

use CodeConjure\BarionPayum\Api\Dto\BaseResponse;
use CodeConjure\BarionPayum\Api\Dto\PaymentStateResponse;
use CodeConjure\BarionPayum\Api\Dto\PreparePaymentRequest;
use CodeConjure\BarionPayum\Api\Dto\PreparePaymentResponse;
use CodeConjure\BarionPayum\Api\Dto\RefundRequest;
use CodeConjure\BarionPayum\Api\Dto\RefundResponse;
use CodeConjure\BarionPayum\Exception\BarionApiException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class BarionClient
{
    public function __construct(
        private readonly BarionApi $api,
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {}

    public function preparePayment(PreparePaymentRequest $dto): PreparePaymentResponse
    {
        $data = $this->post('/v2/Payment/Start', $dto->toArray());
        $response = PreparePaymentResponse::fromArray($data);
        if (!$response->isSuccessful()) {
            throw new BarionApiException($response->errors);
        }
        return $response;
    }

    public function getPaymentState(string $paymentId): PaymentStateResponse
    {
        $data = $this->get('/v2/Payment/GetPaymentState', [
            'POSKey'    => $this->api->posKey,
            'PaymentId' => $paymentId,
        ]);
        $response = PaymentStateResponse::fromArray($data);
        if (!$response->isSuccessful()) {
            throw new BarionApiException($response->errors);
        }
        return $response;
    }

    public function refundPayment(RefundRequest $dto): RefundResponse
    {
        $data = $this->post('/v2/Payment/Refund', $dto->toArray());
        $response = RefundResponse::fromArray($data);
        if (!$response->isSuccessful()) {
            throw new BarionApiException($response->errors);
        }
        return $response;
    }

    public function cancelAuthorization(string $paymentId): BaseResponse
    {
        $data = $this->post('/v2/Payment/CancelAuthorization', [
            'POSKey'    => $this->api->posKey,
            'PaymentId' => $paymentId,
        ]);
        $response = BaseResponse::fromArray($data);
        if (!$response->isSuccessful()) {
            throw new BarionApiException($response->errors);
        }
        return $response;
    }

    /**
     * @param array<array{POSTransactionId: string, Total: int}> $transactions
     */
    public function finishReservation(string $paymentId, array $transactions): BaseResponse
    {
        $data = $this->post('/v2/Payment/FinishReservation', [
            'POSKey'       => $this->api->posKey,
            'PaymentId'    => $paymentId,
            'Transactions' => $transactions,
        ]);
        $response = BaseResponse::fromArray($data);
        if (!$response->isSuccessful()) {
            throw new BarionApiException($response->errors);
        }
        return $response;
    }

    /** @param array<string, mixed> $body @return array<string, mixed> */
    private function post(string $path, array $body): array
    {
        $json = json_encode($body, JSON_THROW_ON_ERROR);
        $stream = $this->streamFactory->createStream($json);
        $request = $this->requestFactory
            ->createRequest('POST', $this->api->getBaseUrl() . $path)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($stream);
        $response = $this->httpClient->sendRequest($request);
        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    /** @param array<string, string> $query @return array<string, mixed> */
    private function get(string $path, array $query): array
    {
        $url = $this->api->getBaseUrl() . $path . '?' . http_build_query($query);
        $request = $this->requestFactory->createRequest('GET', $url);
        $response = $this->httpClient->sendRequest($request);
        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }
}
