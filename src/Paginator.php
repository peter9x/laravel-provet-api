<?php

declare(strict_types=1);

namespace Mupy\ProvetApi;

use Generator;
use IteratorAggregate;
use Mupy\ProvetApi\Exceptions\ProvetPaginationException;
use Mupy\ProvetApi\Paths\Path;

/**
 * Lazily walks a Provet Cloud list endpoint's `next` links, one HTTP page at a
 * time, so callers can stream millions of records without buffering them all
 * in memory. Iterating it directly yields flattened items; pages() yields the
 * raw Page objects (counts, next/previous links) for progress tracking.
 *
 * @implements IteratorAggregate<int, object>
 */
final class Paginator implements IteratorAggregate
{
    /** @var (callable(Page): void)|null */
    private $onPageEnd = null;

    public function __construct(
        private readonly Connection $connection,
        private readonly Path $path,
    ) {}

    /**
     * Run a callback after every page finishes, e.g. to persist progress
     * (page count, next link) for resuming a large sync later.
     */
    public function onPageEnd(callable $callback): static
    {
        $this->onPageEnd = $callback;

        return $this;
    }

    /**
     * Iterate every item across all pages. Return false from the callback to
     * stop early.
     */
    public function each(callable $callback): void
    {
        foreach ($this as $item) {
            if ($callback($item) === false) {
                return;
            }
        }
    }

    public function getIterator(): Generator
    {
        foreach ($this->pages() as $page) {
            yield from $page->items;
        }
    }

    /**
     * @return Generator<int, Page>
     */
    public function pages(): Generator
    {
        $url = null;
        $previousNext = null;

        while (true) {
            $result = $url === null
                ? $this->connection->get($this->path)
                : $this->connection->getUrl($url);

            $page = Page::fromResult($result);

            yield $page;

            if ($this->onPageEnd !== null) {
                ($this->onPageEnd)($page);
            }

            if ($page->next === null) {
                return;
            }

            if ($page->next === $previousNext) {
                throw new ProvetPaginationException(
                    "Provet API returned a repeating 'next' page URL, stopping to avoid an infinite loop: {$page->next}"
                );
            }

            $previousNext = $page->next;
            $url = $page->next;
        }
    }
}
