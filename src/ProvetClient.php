<?php

declare(strict_types=1);

namespace Mupy\ProvetApi;

use InvalidArgumentException;
use Mupy\ProvetApi\Paths\Path;

/**
 * Resolves and caches named Provet Cloud connections (tenants) and exposes
 * the default connection's HTTP methods directly for single-tenant callers.
 */
final class ProvetClient
{
    /** @var array<string, Connection> */
    private array $connections = [];

    /**
     * @param  array{default?: string, connections: array<string, array<string, mixed>>}  $config
     */
    public function __construct(
        private readonly array $config,
    ) {
        if (! isset($config['connections']) || ! is_array($config['connections'])) {
            throw new InvalidArgumentException("Config must have a 'connections' array.");
        }
    }

    public function connection(?string $name = null): Connection
    {
        $name ??= (string) ($this->config['default'] ?? 'default');

        return $this->connections[$name] ??= $this->resolve($name);
    }

    public function get(Path $path): object
    {
        return $this->connection()->get($path);
    }

    public function post(Path $path, array $data = []): object
    {
        return $this->connection()->post($path, $data);
    }

    public function put(Path $path, array $data = []): object
    {
        return $this->connection()->put($path, $data);
    }

    public function patch(Path $path, array $data = []): object
    {
        return $this->connection()->patch($path, $data);
    }

    public function delete(Path $path): bool
    {
        return $this->connection()->delete($path);
    }

    public function paginate(Path $path): Paginator
    {
        return $this->connection()->paginate($path);
    }

    private function resolve(string $name): Connection
    {
        if (! isset($this->config['connections'][$name])) {
            throw new InvalidArgumentException("Provet connection [{$name}] is not configured.");
        }

        return new Connection(ConnectionConfig::fromArray($name, $this->config['connections'][$name]));
    }
}
