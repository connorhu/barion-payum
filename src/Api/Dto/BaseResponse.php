<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Api\Dto;

readonly class BaseResponse
{
    /** @param BarionError[] $errors */
    public function __construct(
        public array $errors = [],
    ) {}

    public function isSuccessful(): bool
    {
        return $this->errors === [];
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): static
    {
        $errors = array_map(
            fn(array $e) => BarionError::fromArray($e),
            $data['Errors'] ?? [],
        );

        return new static(errors: $errors);
    }
}
