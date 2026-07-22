<?php

declare(strict_types=1);

namespace Mupy\ProvetApi\Paths;

use Mupy\ProvetApi\Enums\Operator;

/**
 * Fluent builder for the filter, pagination and ordering query parameters
 * accepted by Provet Cloud list endpoints (`field__operator=value`).
 */
final class Query
{
    /** @var array<string, string> */
    private array $filters = [];

    private ?int $page = null;

    private ?int $perPage = null;

    /** @var list<string> */
    private array $ordering = [];

    private ?bool $withArchived = null;

    private bool $matchAny = false;

    public function where(string $field, Operator $operator, mixed $value): static
    {
        $this->filters[$field.'__'.$operator->value] = $this->formatValue($value);

        return $this;
    }

    public function page(int $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function perPage(int $perPage): static
    {
        $this->perPage = $perPage;

        return $this;
    }

    public function orderBy(string $field): static
    {
        $this->ordering[] = $field;

        return $this;
    }

    public function orderByDesc(string $field): static
    {
        $this->ordering[] = '-'.$field;

        return $this;
    }

    public function withArchived(bool $withArchived = true): static
    {
        $this->withArchived = $withArchived;

        return $this;
    }

    /**
     * Combine filters with OR instead of the API's default AND.
     */
    public function matchAny(): static
    {
        $this->matchAny = true;

        return $this;
    }

    public function toArray(): array
    {
        $params = $this->filters;

        if ($this->page !== null) {
            $params['page'] = $this->page;
        }

        if ($this->perPage !== null) {
            $params['page_size'] = $this->perPage;
        }

        if ($this->ordering !== []) {
            $params['ordering'] = implode(',', $this->ordering);
        }

        if ($this->withArchived !== null) {
            $params['include_archived'] = $this->withArchived ? 'true' : 'false';
        }

        if ($this->matchAny) {
            $params['filter_type'] = 'or';
        }

        return $params;
    }

    private function formatValue(mixed $value): string
    {
        if (is_array($value)) {
            return implode(',', array_map($this->formatValue(...), $value));
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }
}
