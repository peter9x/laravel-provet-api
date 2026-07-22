<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Mupy\ProvetApi\Connection;
use Mupy\ProvetApi\Paths\Client as ClientPath;
use Mupy\ProvetApi\ProvetClient;
use Orchestra\Testbench\TestCase;

class ProvetClientTest extends TestCase
{
    private function client(): ProvetClient
    {
        return new ProvetClient([
            'default' => '5200',
            'connections' => [
                '5200' => ['client_id' => 'a', 'client_secret' => 'b'],
                '5444' => ['client_id' => 'c', 'client_secret' => 'd'],
            ],
        ]);
    }

    public function test_it_resolves_the_default_connection_when_none_named(): void
    {
        $client = $this->client();

        $this->assertInstanceOf(Connection::class, $client->connection());
        $this->assertSame($client->connection(), $client->connection('5200'));
    }

    public function test_it_resolves_named_connections_independently(): void
    {
        $client = $this->client();

        $this->assertNotSame($client->connection('5200'), $client->connection('5444'));
    }

    public function test_it_throws_for_an_unconfigured_connection(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->client()->connection('unknown');
    }

    public function test_convenience_methods_use_the_default_connection(): void
    {
        Http::fake([
            'provetcloud.com/5200/oauth2/token/' => Http::response(['access_token' => 'tok']),
            'provetcloud.com/5200/api/0.1/client/1/' => Http::response(['id' => 1]),
        ]);

        $result = $this->client()->get(ClientPath::get(1));

        $this->assertSame(1, $result->id);
    }
}
