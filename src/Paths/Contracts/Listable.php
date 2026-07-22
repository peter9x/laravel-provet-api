<?php

declare(strict_types=1);

namespace Mupy\ProvetApi\Paths\Contracts;

use Mupy\ProvetApi\Paths\Path;
use Mupy\ProvetApi\Paths\Query;

interface Listable
{
    public static function all(?Query $query = null): Path;
}
