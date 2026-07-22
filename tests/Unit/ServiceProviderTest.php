<?php

namespace Tests\Unit;

use Mupy\ProvetApi\Facades\Provet;
use Mupy\ProvetApi\ProvetServiceProvider;
use Orchestra\Testbench\TestCase;

class ServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [ProvetServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Provet' => Provet::class,
        ];
    }
}
