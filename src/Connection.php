<?php

declare(strict_types=1);

namespace Mupy\ProvetApi;

use Illuminate\Http\Client\ConnectionException as HttpConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Mupy\ProvetApi\Exceptions\ProvetAuthenticationException;
use Mupy\ProvetApi\Exceptions\ProvetRequestException;
use Mupy\ProvetApi\Paths\Path;

/**
 * A single, authenticated connection to one Provet Cloud organization (tenant).
 * Handles OAuth2 client-credentials auth, token caching, retries and rate limiting.
 */
final class Connection
{
    public function __construct(
        private readonly ConnectionConfig $config,
    ) {}

    public function get(Path $path): object
    {
        if (! $this->config->useCache) {
            return $this->send('GET', $path)->object();
        }

        return Cache::remember(
            $this->responseCacheKey($path),
            $this->config->cacheTtl,
            fn (): object => $this->send('GET', $path)->object(),
        );
    }

    /**
     * Fetch an already-resolved absolute URL directly, e.g. a pagination `next` link.
     */
    public function getUrl(string $url): object
    {
        return $this->send('GET', $url)->object();
    }

    public function post(Path $path, array $data = []): object
    {
        return $this->send('POST', $path, ['json' => $data])->object();
    }

    public function put(Path $path, array $data = []): object
    {
        return $this->send('PUT', $path, ['json' => $data])->object();
    }

    public function patch(Path $path, array $data = []): object
    {
        return $this->send('PATCH', $path, ['json' => $data])->object();
    }

    public function delete(Path $path): bool
    {
        return $this->send('DELETE', $path)->successful();
    }

    public function paginate(Path $path): Paginator
    {
        return new Paginator($this, $path);
    }

    /**
     * The current bearer token, fetching (and caching) a new one if needed.
     */
    public function token(bool $fresh = false): string
    {
        if ($fresh) {
            Cache::forget($this->tokenCacheKey());
        }

        return Cache::remember(
            $this->tokenCacheKey(),
            $this->config->tokenTtl,
            fn (): string => $this->requestToken(),
        );
    }

    private function requestToken(): string
    {
        $response = Http::asForm()
            ->timeout($this->config->timeout)
            ->post($this->config->authUrl(), [
                'grant_type' => 'client_credentials',
                'client_id' => $this->config->clientId,
                'client_secret' => $this->config->clientSecret,
            ]);

        if ($response->failed()) {
            throw ProvetAuthenticationException::fromResponse($response, $this->config->name);
        }

        $token = $response->json('access_token');

        if (! is_string($token) || $token === '') {
            throw ProvetAuthenticationException::invalidTokenResponse($this->config->name);
        }

        return $token;
    }

    private function send(string $method, Path|string $target, array $options = []): Response
    {
        $url = $this->urlFor($target);
        $response = null;

        for ($attempt = 1; $attempt <= $this->config->retries; $attempt++) {
            try {
                $response = Http::withToken($this->token())
                    ->timeout($this->config->timeout)
                    ->send($method, $url, $options);
            } catch (HttpConnectionException) {
                $this->sleep();

                continue;
            }

            if ($response->successful() || $response->status() === 204) {
                return $response;
            }

            if ($response->status() === 401) {
                $this->token(fresh: true);
                $this->sleep();

                continue;
            }

            if ($response->status() === 429) {
                $this->sleep($response->header('Retry-After'));

                continue;
            }

            if ($response->status() >= 500) {
                $this->sleep();

                continue;
            }

            break;
        }

        throw ProvetRequestException::fromResponse($response, $method, $url);
    }

    private function urlFor(Path|string $target): string
    {
        if (is_string($target)) {
            return $target;
        }

        return $this->config->baseUrl().ltrim((string) $target, '/');
    }

    private function sleep(?string $retryAfter = null): void
    {
        $seconds = is_numeric($retryAfter)
            ? (int) $retryAfter
            : random_int($this->config->retryDelayMin, $this->config->retryDelayMax);

        Sleep::sleep($seconds);
    }

    private function tokenCacheKey(): string
    {
        return "mupy:provet-api:token:{$this->config->name}";
    }

    private function responseCacheKey(Path $path): string
    {
        return 'mupy:provet-api:response:'.$this->config->name.':'.sha1((string) $path);
    }
}
