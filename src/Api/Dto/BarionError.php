<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Api\Dto;

readonly class BarionError
{
    public function __construct(
        public string $errorCode,
        public string $title,
        public string $description,
        public ?string $posKey = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            errorCode: $data['ErrorCode'] ?? '',
            title: $data['Title'] ?? '',
            description: $data['Description'] ?? '',
            posKey: $data['PosKey'] ?? null,
        );
    }
}
