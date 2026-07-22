<?php

declare(strict_types=1);

namespace Mupy\ProvetApi\Paths\Concerns;

use Mupy\ProvetApi\Paths\Path;
use Mupy\ProvetApi\Paths\Query;

trait IsListable
{
    abstract protected static function resource(): string;

    public static function all(?Query $query = null): Path
    {
        return new Path([
            'endpoint' => '/'.static::resource().'/',
            'query' => ($query ?? new Query)->toArray(),
        ]);
    }
}
