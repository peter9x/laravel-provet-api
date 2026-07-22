<?php

declare(strict_types=1);

namespace Mupy\ProvetApi\Paths\Contracts;

use Mupy\ProvetApi\Paths\Path;

interface Deletable
{
    public static function delete(int $id): Path;
}
