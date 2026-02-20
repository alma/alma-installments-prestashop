<?php

namespace PrestaShop\Module\Alma\Infrastructure\Factory;

use Alma\Client\Application\ClientConfiguration;
use Alma\Client\Application\CurlClient;
use Alma\Client\Domain\ValueObject\Environment;

class CurlClientFactory
{
    public function create(string $apiKey, string $mode): CurlClient
    {
        $clientConfiguration = new ClientConfiguration($apiKey, new Environment($mode));

        return new CurlClient($clientConfiguration);
    }
}
