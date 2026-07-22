<?php

declare(strict_types=1);

namespace Mupy\ProvetApi\Paths;

use Mupy\ProvetApi\Paths\Concerns\IsListable;
use Mupy\ProvetApi\Paths\Concerns\IsRetrievable;
use Mupy\ProvetApi\Paths\Contracts\Listable;
use Mupy\ProvetApi\Paths\Contracts\Retrievable;

final class Department implements Listable, Retrievable
{
    use IsListable;
    use IsRetrievable;

    protected static function resource(): string
    {
        return 'department';
    }
}
