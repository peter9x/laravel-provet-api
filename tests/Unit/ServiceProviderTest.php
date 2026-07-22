<?php

namespace Tests\Unit;

use Mupy\ProvetApi\Facades\Provet;
use Mupy\ProvetApi\ProvetClient;
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

    public function test_it_merges_the_package_config(): void
    {
        $this->assertSame('default', config('provet.default'));
        $this->assertArrayHasKey('connections', config('provet'));
    }

    public function test_it_binds_provet_client_as_a_singleton(): void
    {
        $this->assertTrue($this->app->bound(ProvetClient::class));
        $this->assertSame(
            $this->app->make(ProvetClient::class),
            $this->app->make(ProvetClient::class),
        );
        $this->assertInstanceOf(ProvetClient::class, $this->app->make('provet'));
    }

    public function test_the_facade_resolves_to_the_provet_client(): void
    {
        $this->assertInstanceOf(ProvetClient::class, Provet::getFacadeRoot());
    }
}
