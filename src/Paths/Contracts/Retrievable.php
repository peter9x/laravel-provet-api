<?php

declare(strict_types=1);

namespace Mupy\ProvetApi\Paths\Contracts;

use Mupy\ProvetApi\Paths\Path;

interface Retrievable
{
    public static function get(int $id): Path;
}
