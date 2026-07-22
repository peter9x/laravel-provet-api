<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Http;
use Mupy\ProvetApi\Connection;
use Mupy\ProvetApi\ConnectionConfig;
use Mupy\ProvetApi\Exceptions\ProvetPaginationException;
use Mupy\ProvetApi\Paths\Invoice;
use Orchestra\Testbench\TestCase;

class PaginatorTest extends TestCase
{
    private function connection(): Connection
    {
        return new Connection(ConnectionConfig::fromArray('test', [
            'client_id' => 'id',
            'client_secret' => 'secret',
            'retries' => 3,
            'retry_delay_min' => 0,
            'retry_delay_max' => 0,
        ]));
    }

    public function test_it_streams_items_across_pages_by_following_next(): void
    {
        Http::fake([
            'provetcloud.com/test/oauth2/token/' => Http::response(['access_token' => 'tok']),
            'provetcloud.com/test/api/0.1/invoice/' => Http::response([
                'count' => 3,
                'num_pages' => 2,
                'next' => 'https://provetcloud.com/test/api/0.1/invoice/?page=2',
                'previous' => null,
                'results' => [['id' => 1], ['id' => 2]],
            ]),
            'provetcloud.com/test/api/0.1/invoice/?page=2' => Http::response([
                'count' => 3,
                'num_pages' => 2,
                'next' => null,
                'previous' => 'https://provetcloud.com/test/api/0.1/invoice/',
                'results' => [['id' => 3]],
            ]),
        ]);

        $ids = [];
        foreach ($this->connection()->paginate(Invoice::all()) as $item) {
            $ids[] = $item->id;
        }

        $this->assertSame([1, 2, 3], $ids);
    }

    public function test_each_stops_early_when_callback_returns_false(): void
    {
        Http::fake([
            'provetcloud.com/test/oauth2/token/' => Http::response(['access_token' => 'tok']),
            'provetcloud.com/test/api/0.1/invoice/' => Http::response([
                'count' => 3,
                'num_pages' => 1,
                'next' => null,
                'previous' => null,
                'results' => [['id' => 1], ['id' => 2], ['id' => 3]],
            ]),
        ]);

        $seen = [];
        $this->connection()->paginate(Invoice::all())->each(function ($item) use (&$seen) {
            $seen[] = $item->id;

            return $item->id < 2;
        });

        $this->assertSame([1, 2], $seen);
    }

    public function test_on_page_end_runs_after_every_page(): void
    {
        Http::fake([
            'provetcloud.com/test/oauth2/token/' => Http::response(['access_token' => 'tok']),
            'provetcloud.com/test/api/0.1/invoice/' => Http::response([
                'count' => 1, 'num_pages' => 1, 'next' => null, 'previous' => null,
                'results' => [['id' => 1]],
            ]),
        ]);

        $pagesSeen = 0;
        $this->connection()->paginate(Invoice::all())
            ->onPageEnd(function () use (&$pagesSeen) {
                $pagesSeen++;
            })
            ->each(fn () => null);

        $this->assertSame(1, $pagesSeen);
    }

    public function test_it_throws_when_the_api_returns_a_repeating_next_link(): void
    {
        Http::fake([
            'provetcloud.com/test/oauth2/token/' => Http::response(['access_token' => 'tok']),
            'provetcloud.com/test/api/0.1/invoice/' => Http::response([
                'count' => 2, 'num_pages' => 2,
                'next' => 'https://provetcloud.com/test/api/0.1/invoice/?page=2',
                'previous' => null,
                'results' => [['id' => 1]],
            ]),
            'provetcloud.com/test/api/0.1/invoice/?page=2' => Http::response([
                'count' => 2, 'num_pages' => 2,
                'next' => 'https://provetcloud.com/test/api/0.1/invoice/?page=2',
                'previous' => null,
                'results' => [['id' => 2]],
            ]),
        ]);

        $this->expectException(ProvetPaginationException::class);

        iterator_to_array($this->connection()->paginate(Invoice::all()));
    }
}
