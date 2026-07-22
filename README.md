<p align="center">
    <a href="https://github.com/peter9x/laravel-provet-api/actions"><img src="https://github.com/peter9x/laravel-provet-api/actions/workflows/php.yml/badge.svg" alt="Build Status"></a>
    <a href="https://packagist.org/packages/peter9x/laravel-provet-api"><img src="https://img.shields.io/packagist/v/peter9x/laravel-provet-api" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/peter9x/laravel-provet-api"><img src="https://img.shields.io/packagist/l/peter9x/laravel-provet-api" alt="License"></a>
</p>

# Laravel Provet API

A Laravel package for communicating with the [Provet Cloud API](https://developers.provetcloud.com/restapi/0.1/).

## Features

- OAuth2 client-credentials authentication with token caching
- Multiple named connections (multi-tenant / multi-organization support)
- Typed, capability-safe resource path builders (`Client::all()`, `Patient::get(42)`, ...)
- Fluent filter/pagination query builder
- Automatic retries with 401/429 handling
- Lazy, memory-safe pagination for large datasets
- Laravel Facade

## Installation

```bash
composer require peter9x/laravel-provet-api
php artisan vendor:publish --provider="Mupy\\ProvetApi\\ProvetServiceProvider" --tag=config
```

## Configuration

Set your Provet credentials in `.env`:

```env
PROVET_ID=7456
PROVET_CLIENT_ID=your-client-id
PROVET_CLIENT_SECRET=your-client-secret
```

For multiple tenants/organizations, add one entry per connection in `config/provet.php`:

```php
'default' => env('PROVET_CONNECTION', '7456'),

'connections' => [
    '7456' => [
        'client_id' => env('PROVET_API_7456_ID'),
        'client_secret' => env('PROVET_API_7456_SECRET'),
    ],
    '2449' => [
        'client_id' => env('PROVET_API_2449_ID'),
        'client_secret' => env('PROVET_API_2449_SECRET'),
    ],
],
```

## Usage

```php
use Mupy\ProvetApi\Facades\Provet;
use Mupy\ProvetApi\Paths\Client;
use Mupy\ProvetApi\Paths\Query;
use Mupy\ProvetApi\Enums\Operator;

// Retrieve
$client = Provet::get(Client::get(42));

// List, with filters, ordering and pagination
$query = (new Query())
    ->where('email', Operator::ICONTAINS, 'acme')
    ->orderByDesc('created')
    ->perPage(50);

$clients = Provet::get(Client::all($query));

// Create / update / delete (only exposed on resources the API actually supports)
Provet::post(Client::create(), ['firstname' => 'Ada']);
Provet::patch(Client::update(42), ['firstname' => 'Ada Lovelace']);
Provet::delete(Client::delete(42));
```

### Dependency injection

`ProvetClient` is bound as a singleton, so it can be injected instead of using the facade:

```php
use Mupy\ProvetApi\ProvetClient;
use Mupy\ProvetApi\Paths\Client;

class SyncClientsController
{
    public function __construct(
        private readonly ProvetClient $provet,
    ) {}

    public function __invoke(int $id)
    {
        // Pass a connection name to use a tenant other than the default one.
        return $this->provet->connection('2449')->get(Client::get($id));
    }
}
```

### Multiple connections

```php
Provet::connection('2449')->get(Client::all());
```

### Pagination

`paginate()` streams results lazily, following the API's `next` links, so it's safe for endpoints with millions of records:

```php
use Mupy\ProvetApi\Paths\Invoice;

foreach (Provet::paginate(Invoice::all()) as $invoice) {
    // one invoice at a time, no buffering
}

// Or with a stop condition and progress tracking:
Provet::paginate(Invoice::all())
    ->onPageEnd(fn ($page) => logger()->info("page done, next: {$page->next}"))
    ->each(function ($invoice) {
        // return false to stop early
    });
```
