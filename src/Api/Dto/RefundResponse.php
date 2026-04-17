<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Api\Dto;

readonly class RefundResponse extends BaseResponse
{
    public function __construct(
        public ?string $paymentId = null,
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
            errors: $errors,
        );
    }
}
