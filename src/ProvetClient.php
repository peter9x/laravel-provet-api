<?php

namespace Mupy\ProvetApi;

use InvalidArgumentException;

class ProvetClient
{
    private array $config;

    public function __construct(array $config)
    {
        if (! isset($config['connections']) || ! is_array($config['connections'])) {
            throw new InvalidArgumentException("Config must have a 'connections' array.");
        }
        if (! isset($config['api_url'])) {
            throw new InvalidArgumentException("Config must have an 'api_url' defined.");
        }

        $this->config = $config;
    }
}
