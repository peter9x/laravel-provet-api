<?php

declare(strict_types=1);

namespace Mupy\ProvetApi\Exceptions;

use Illuminate\Http\Client\Response;
use RuntimeException;

final class ProvetAuthenticationException extends RuntimeException
{
    public static function fromResponse(Response $response, string $connection): self
    {
        return new self("Failed to authenticate Provet connection [{$connection}]: HTTP {$response->status()} - {$response->body()}");
    }

    public static function invalidTokenResponse(string $connection): self
    {
        return new self("Provet connection [{$connection}] returned a token response without an access_token.");
    }
}
