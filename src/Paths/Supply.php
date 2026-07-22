<?php

declare(strict_types=1);

namespace Mupy\ProvetApi\Paths;

use Mupy\ProvetApi\Paths\Concerns\IsCreatable;
use Mupy\ProvetApi\Paths\Concerns\IsDeletable;
use Mupy\ProvetApi\Paths\Concerns\IsListable;
use Mupy\ProvetApi\Paths\Concerns\IsRetrievable;
use Mupy\ProvetApi\Paths\Concerns\IsUpdatable;
use Mupy\ProvetApi\Paths\Contracts\Creatable;
use Mupy\ProvetApi\Paths\Contracts\Deletable;
use Mupy\ProvetApi\Paths\Contracts\Listable;
use Mupy\ProvetApi\Paths\Contracts\Retrievable;
use Mupy\ProvetApi\Paths\Contracts\Updatable;

final class Supply implements Creatable, Deletable, Listable, Retrievable, Updatable
{
    use IsCreatable;
    use IsDeletable;
    use IsListable;
    use IsRetrievable;
    use IsUpdatable;

    protected static function resource(): string
    {
        return 'supply';
    }
}
