<?php

declare(strict_types=1);

namespace Mupy\ProvetApi;

/**
 * One page of a Provet Cloud list response.
 */
final class Page
{
    /**
     * @param  list<object>  $items
     */
    public function __construct(
        public readonly array $items,
        public readonly ?string $next,
        public readonly ?string $previous,
        public readonly ?int $count,
        public readonly ?int $numPages,
    ) {}

    public static function fromResult(object $result): self
    {
        return new self(
            items: $result->results ?? [],
            next: $result->next ?? null,
            previous: $result->previous ?? null,
            count: $result->count ?? null,
            numPages: $result->num_pages ?? null,
        );
    }
}
