<?php

declare(strict_types=1);

namespace Mupy\ProvetApi\Exceptions;

use Illuminate\Http\Client\Response;
use RuntimeException;

final class ProvetRequestException extends RuntimeException
{
    private function __construct(
        string $message,
        public readonly int $status,
        public readonly string $method,
        public readonly string $url,
        public readonly ?string $responseBody,
    ) {
        parent::__construct($message, $this->status);
    }

    public static function fromResponse(?Response $response, string $method, string $url): self
    {
        if (! $response instanceof Response) {
            return new self(
                "Provet API request failed: no response received for {$method} {$url}.",
                0,
                $method,
                $url,
                null,
            );
        }

        return new self(
            "Provet API request failed with status {$response->status()} for {$method} {$url}.",
            $response->status(),
            $method,
            $url,
            $response->body(),
        );
    }
}
