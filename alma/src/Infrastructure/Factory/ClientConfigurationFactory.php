<?php

namespace PrestaShop\Module\Alma\Infrastructure\Factory;

use Alma\Client\Application\ClientConfiguration;
use Alma\Client\Domain\ValueObject\Environment;
use PrestaShop\Module\Alma\Application\Service\SettingsService;

class ClientConfigurationFactory
{
    /**
     * @var SettingsService
     */
    private SettingsService $settingsService;
    /**
     * @var Environment
     */
    private Environment $environment;

    public function __construct(
        SettingsService $settingsService,
        Environment $environment
    ) {
        $this->settingsService = $settingsService;
        $this->environment = $environment;
    }

    /**
     * Create a ClientConfiguration instance using the API key from the settings repository or from the POST of Form if submitted.
     * @return ClientConfiguration
     */
    public function create(): ClientConfiguration
    {
        $apiKey = $this->settingsService->getApiKey();

        return new ClientConfiguration($apiKey, $this->environment);
    }
}
