<?php

declare(strict_types=1);

namespace Mupy\ProvetApi\Paths\Concerns;

use Mupy\ProvetApi\Paths\Path;

trait IsDeletable
{
    abstract protected static function resource(): string;

    public static function delete(int $id): Path
    {
        return new Path([
            'endpoint' => '/'.static::resource().'/'.$id.'/',
        ]);
    }
}
