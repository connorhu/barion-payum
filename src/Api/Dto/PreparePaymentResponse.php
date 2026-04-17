<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Api\Dto;

readonly class PreparePaymentResponse extends BaseResponse
{
    public function __construct(
        public ?string $paymentId = null,
        public ?string $paymentRequestId = null,
        public ?string $status = null,
        public ?string $qrUrl = null,
        array $errors = [],
    ) {
        parent::__construct(errors: $errors);
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        $errors = array_map(
            fn(array $e) => BarionError::fromArray($e),
            $data['Errors'] ?? [],
        );

        return new static(
            paymentId: $data['PaymentId'] ?? null,
            paymentRequestId: $data['PaymentRequestId'] ?? null,
            status: $data['Status'] ?? null,
            qrUrl: $data['QRUrl'] ?? null,
            errors: $errors,
        );
    }
}
