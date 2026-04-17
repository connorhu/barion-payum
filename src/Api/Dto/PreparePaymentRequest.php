<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Api\Dto;

readonly class PreparePaymentRequest
{
    /**
     * @param array<array{Name: string, Description: string, Quantity: float, Unit: string, UnitPrice: float, ItemTotal: float, SKU?: string}> $items
     */
    public function __construct(
        public string $posKey,
        public string $paymentType,
        public string $paymentRequestId,
        public int $total,
        public string $currency,
        public string $redirectUrl,
        public string $callbackUrl,
        public string $orderNumber,
        public array $items = [],
        public ?string $locale = 'hu-HU',
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'POSKey'           => $this->posKey,
            'PaymentType'      => $this->paymentType,
            'PaymentRequestId' => $this->paymentRequestId,
            'Transactions'     => [
                [
                    'POSTransactionId' => $this->orderNumber,
                    'Payee'            => '',
                    'Total'            => $this->total,
                    'Items'            => $this->items,
                ],
            ],
            'Currency'         => $this->currency,
            'RedirectUrl'      => $this->redirectUrl,
            'CallbackUrl'      => $this->callbackUrl,
            'Locale'           => $this->locale,
        ];
    }
}
