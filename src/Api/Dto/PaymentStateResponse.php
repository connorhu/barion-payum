<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Api\Dto;

readonly class PaymentStateResponse extends BaseResponse
{
    /** @param TransactionDetails[] $transactions */
    public function __construct(
        public ?string $paymentId = null,
        public ?string $paymentRequestId = null,
        public ?string $status = null,
        public ?string $currency = null,
        public ?int $total = null,
        public array $transactions = [],
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

        $transactions = array_map(
            fn(array $t) => TransactionDetails::fromArray($t),
            $data['Transactions'] ?? [],
        );

        return new static(
            paymentId: $data['PaymentId'] ?? null,
            paymentRequestId: $data['PaymentRequestId'] ?? null,
            status: $data['Status'] ?? null,
            currency: $data['Currency'] ?? null,
            total: isset($data['Total']) ? (int) $data['Total'] : null,
            transactions: $transactions,
            errors: $errors,
        );
    }
}
