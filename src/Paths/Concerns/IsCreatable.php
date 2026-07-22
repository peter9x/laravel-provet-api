<?php

declare(strict_types=1);

namespace Mupy\ProvetApi\Paths\Concerns;

use Mupy\ProvetApi\Paths\Path;

trait IsCreatable
{
    abstract protected static function resource(): string;

    public static function create(): Path
    {
        return new Path([
            'endpoint' => '/'.static::resource().'/',
        ]);
    }
}
