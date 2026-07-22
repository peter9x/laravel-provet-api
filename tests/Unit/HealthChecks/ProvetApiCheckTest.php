<?php

namespace Tests\Unit\HealthChecks;

use Illuminate\Support\Facades\Http;
use Mupy\ProvetApi\Facades\Provet;
use Mupy\ProvetApi\HealthChecks\ProvetApiCheck;
use Mupy\ProvetApi\ProvetServiceProvider;
use Orchestra\Testbench\TestCase;

class ProvetApiCheckTest extends TestCase
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

    protected function defineEnvironment($app): void
    {
        $app['config']->set('provet.connections', [
            '5200' => ['client_id' => 'a', 'client_secret' => 'b'],
            '5444' => ['client_id' => 'c', 'client_secret' => 'd'],
        ]);
    }

    public function test_it_passes_when_every_connection_authenticates(): void
    {
        Http::fake([
            'provetcloud.com/5200/oauth2/token/' => Http::response(['access_token' => 'tok-5200']),
            'provetcloud.com/5444/oauth2/token/' => Http::response(['access_token' => 'tok-5444']),
        ]);

        $result = (new ProvetApiCheck)->run();

        $this->assertTrue($result->status->value === 'ok');
        $this->assertStringContainsString('5200', $result->notificationMessage);
        $this->assertStringContainsString('5444', $result->notificationMessage);
    }

    public function test_it_fails_when_a_connection_cannot_authenticate(): void
    {
        Http::fake([
            'provetcloud.com/5200/oauth2/token/' => Http::response(['access_token' => 'tok-5200']),
            'provetcloud.com/5444/oauth2/token/' => Http::response(['error' => 'invalid_client'], 400),
        ]);

        $result = (new ProvetApiCheck)->run();

        $this->assertTrue($result->status->value === 'failed');
        $this->assertStringContainsString('5444', $result->notificationMessage);
    }

    public function test_it_fails_when_no_connections_are_configured(): void
    {
        $this->app['config']->set('provet.connections', []);

        $result = (new ProvetApiCheck)->run();

        $this->assertTrue($result->status->value === 'failed');
        $this->assertSame('No Provet connections are configured.', $result->notificationMessage);
    }

    public function test_it_only_checks_the_connections_it_is_scoped_to(): void
    {
        Http::fake([
            'provetcloud.com/5200/oauth2/token/' => Http::response(['access_token' => 'tok-5200']),
        ]);

        $result = (new ProvetApiCheck)->connections(['5200'])->run();

        $this->assertTrue($result->status->value === 'ok');
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '5444'));
    }
}
