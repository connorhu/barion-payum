<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Exception;

use CodeConjure\BarionPayum\Api\Dto\BarionError;
use RuntimeException;

final class BarionApiException extends RuntimeException
{
    /** @param BarionError[] $errors */
    public function __construct(
        private readonly array $errors,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            $message ?: implode('; ', array_map(fn($e) => $e->title, $errors)),
            $code,
            $previous,
        );
    }

    /** @return BarionError[] */
    public function getBarionErrors(): array
    {
        return $this->errors;
    }
}
