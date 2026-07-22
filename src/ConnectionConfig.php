<?php

declare(strict_types=1);

namespace Mupy\ProvetApi;

use InvalidArgumentException;

/**
 * Resolved, typed configuration for a single Provet Cloud connection (tenant).
 */
final class ConnectionConfig
{
    public function __construct(
        public readonly string $name,
        public readonly string $provetId,
        public readonly string $clientId,
        public readonly string $clientSecret,
        public readonly string $domain = 'provetcloud.com',
        public readonly bool $useCache = false,
        public readonly int $cacheTtl = 3600,
        public readonly int $tokenTtl = 3600,
        public readonly int $timeout = 60,
        public readonly int $retries = 5,
        public readonly int $retryDelayMin = 1,
        public readonly int $retryDelayMax = 5,
    ) {}

    /**
     * @param  array<string, mixed>  $config
     */
    public static function fromArray(string $name, array $config): self
    {
        return new self(
            name: $name,
            provetId: (string) ($config['provet_id'] ?? $name),
            clientId: (string) ($config['client_id'] ?? throw new InvalidArgumentException("Provet connection [{$name}] is missing a 'client_id'.")),
            clientSecret: (string) ($config['client_secret'] ?? throw new InvalidArgumentException("Provet connection [{$name}] is missing a 'client_secret'.")),
            domain: (string) ($config['domain'] ?? 'provetcloud.com'),
            useCache: (bool) ($config['use_cache'] ?? false),
            cacheTtl: (int) ($config['cache_ttl'] ?? 3600),
            tokenTtl: (int) ($config['token_ttl'] ?? 3600),
            timeout: (int) ($config['timeout'] ?? 60),
            retries: (int) ($config['retries'] ?? 5),
            retryDelayMin: (int) ($config['retry_delay_min'] ?? 1),
            retryDelayMax: (int) ($config['retry_delay_max'] ?? 5),
        );
    }

    public function baseUrl(): string
    {
        return "https://{$this->domain}/{$this->provetId}/api/0.1/";
    }

    public function authUrl(): string
    {
        return "https://{$this->domain}/{$this->provetId}/oauth2/token/";
    }
}
