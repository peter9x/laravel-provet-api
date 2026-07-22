<?php

declare(strict_types=1);

namespace Mupy\ProvetApi\Paths\Concerns;

use Mupy\ProvetApi\Paths\Path;

trait IsRetrievable
{
    abstract protected static function resource(): string;

    public static function get(int $id): Path
    {
        return new Path([
            'endpoint' => '/'.static::resource().'/'.$id.'/',
        ]);
    }
}
