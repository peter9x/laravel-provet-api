<?php

namespace Mupy\ProvetApi;

use Illuminate\Support\ServiceProvider;

class ProvetServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/provet.php' => config_path('provet.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/provet.php',
            'provet'
        );

        $this->app->singleton(ProvetClient::class, function ($app) {
            /** @var array{
             *     connections: array<string, array{client_id: string, secret: string}>,
             *     api_url: string
             * } $config */
            $config = config('provet');

            return new ProvetClient($config);
        });

        $this->app->alias(ProvetClient::class, 'provet');
    }
}
