<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Api\Dto;

readonly class RefundRequest
{
    public function __construct(
        public string $posKey,
        public string $paymentId,
        public int $amount,
        public string $currency,
        public string $comment = '',
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'POSKey'               => $this->posKey,
            'PaymentId'            => $this->paymentId,
            'TransactionsToRefund' => [
                [
                    'POSTransactionId' => $this->paymentId,
                    'TransactionId'    => $this->paymentId,
                    'AmountToRefund'   => $this->amount,
                    'Comment'          => $this->comment,
                ],
            ],
        ];
    }
}
