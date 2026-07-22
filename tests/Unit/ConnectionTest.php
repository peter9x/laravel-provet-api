<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Http;
use Mupy\ProvetApi\Connection;
use Mupy\ProvetApi\ConnectionConfig;
use Mupy\ProvetApi\Exceptions\ProvetAuthenticationException;
use Mupy\ProvetApi\Exceptions\ProvetRequestException;
use Mupy\ProvetApi\Paths\Client;
use Orchestra\Testbench\TestCase;

class ConnectionTest extends TestCase
{
    private function connection(array $overrides = []): Connection
    {
        return new Connection(ConnectionConfig::fromArray('test', array_merge([
            'client_id' => 'id',
            'client_secret' => 'secret',
            'retries' => 3,
            'retry_delay_min' => 0,
            'retry_delay_max' => 0,
        ], $overrides)));
    }

    public function test_it_authenticates_and_fetches_a_resource(): void
    {
        Http::fake([
            'provetcloud.com/test/oauth2/token/' => Http::response(['access_token' => 'tok-123']),
            'provetcloud.com/test/api/0.1/client/42/' => Http::response(['id' => 42, 'firstname' => 'Ada']),
        ]);

        $result = $this->connection()->get(Client::get(42));

        $this->assertSame(42, $result->id);
        $this->assertSame('Ada', $result->firstname);

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer tok-123'));
    }

    public function test_it_reuses_the_cached_token_across_requests(): void
    {
        Http::fake([
            'provetcloud.com/test/oauth2/token/' => Http::response(['access_token' => 'tok-123']),
            'provetcloud.com/test/api/0.1/client/*' => Http::response(['id' => 1]),
        ]);

        $connection = $this->connection();
        $connection->get(Client::get(1));
        $connection->get(Client::get(2));

        Http::assertSentCount(3); // 1 token request + 2 resource requests
    }

    public function test_it_refreshes_the_token_and_retries_on_401(): void
    {
        Http::fakeSequence('provetcloud.com/test/oauth2/token/')
            ->push(['access_token' => 'stale'])
            ->push(['access_token' => 'fresh']);

        Http::fakeSequence('provetcloud.com/test/api/0.1/client/42/')
            ->push(['detail' => 'Unauthorized'], 401)
            ->push(['id' => 42]);

        $result = $this->connection()->get(Client::get(42));

        $this->assertSame(42, $result->id);
    }

    public function test_it_retries_on_429_and_honours_retry_after(): void
    {
        Http::fake([
            'provetcloud.com/test/oauth2/token/' => Http::response(['access_token' => 'tok-123']),
        ]);

        Http::fakeSequence('provetcloud.com/test/api/0.1/client/42/')
            ->push(['detail' => 'Too Many Requests'], 429, ['Retry-After' => '0'])
            ->push(['id' => 42]);

        $result = $this->connection()->get(Client::get(42));

        $this->assertSame(42, $result->id);
    }

    public function test_it_throws_without_retrying_on_a_client_error(): void
    {
        Http::fake([
            'provetcloud.com/test/oauth2/token/' => Http::response(['access_token' => 'tok-123']),
            'provetcloud.com/test/api/0.1/client/999/' => Http::response(['detail' => 'Not found'], 404),
        ]);

        $this->expectException(ProvetRequestException::class);

        $this->connection()->get(Client::get(999));

        Http::assertSentCount(2); // token + single failed attempt, no retry loop for 404
    }

    public function test_it_throws_a_provet_authentication_exception_when_the_token_request_fails(): void
    {
        Http::fake([
            'provetcloud.com/test/oauth2/token/' => Http::response(['error' => 'invalid_client'], 400),
        ]);

        $this->expectException(ProvetAuthenticationException::class);

        $this->connection()->get(Client::get(1));
    }
}
