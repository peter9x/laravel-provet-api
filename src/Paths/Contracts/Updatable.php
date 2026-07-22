<?php

declare(strict_types=1);

namespace Mupy\ProvetApi\Paths\Contracts;

use Mupy\ProvetApi\Paths\Path;

interface Updatable
{
    public static function update(int $id): Path;
}
