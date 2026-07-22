<?php

namespace Mupy\ProvetApi\Facades;

use Illuminate\Support\Facades\Facade;

class Provet extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'provet';
    }
}
