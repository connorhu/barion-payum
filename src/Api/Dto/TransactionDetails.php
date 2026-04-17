<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Api\Dto;

readonly class TransactionDetails
{
    public function __construct(
        public string $transactionId,
        public string $posTransactionId,
        public string $status,
        public int $total,
        public string $currency,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            transactionId: $data['TransactionId'] ?? '',
            posTransactionId: $data['POSTransactionId'] ?? '',
            status: $data['Status'] ?? '',
            total: (int) ($data['Total'] ?? 0),
            currency: $data['Currency'] ?? '',
        );
    }
}
