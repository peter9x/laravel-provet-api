<?php

declare(strict_types=1);

namespace Mupy\ProvetApi\Paths;

use Stringable;

/**
 * Class Path
 * Represents a path with associated query parameters.
 */
class Path implements Stringable
{
    /**
     * The endpoint of the path.
     */
    private ?string $endpoint = null;

    /**
     * The query parameters for the path.
     */
    private array $query = [];

    /**
     * Path constructor.
     *
     * @param  array  $params  An array containing endpoint and query parameters.
     */
    public function __construct(array $params)
    {
        $this->endpoint = $params['endpoint'] ?? null;

        if (isset($params['query']) && is_array($params['query'])) {
            foreach ($params['query'] as $key => $value) {
                if ($value !== null) {
                    $this->query[$key] = $value;
                }
            }
        }
    }

    /**
     * Convert the path to a string representation.
     * This includes the endpoint and query parameters if available.
     *
     * @return string The string representation of the path with query parameters.
     */
    public function __toString(): string
    {
        return $this->endpoint.(count($this->query) > 0 ? '?'.http_build_query($this->query) : '');
    }

    /**
     * Get the endpoint.
     *
     * @return string|null The endpoint.
     */
    public function getEndPoint(): ?string
    {
        return $this->endpoint;
    }

    /**
     * Get the query parameters.
     *
     * @return array The query parameters.
     */
    public function getQuery(): array
    {
        return $this->query;
    }
}
