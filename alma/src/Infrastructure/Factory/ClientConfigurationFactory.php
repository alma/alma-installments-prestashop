<?php

namespace PrestaShop\Module\Alma\Infrastructure\Factory;

use Alma\Client\Application\ClientConfiguration;
use Alma\Client\Domain\ValueObject\Environment;
use PrestaShop\Module\Alma\Application\Provider\SettingsProvider;

class ClientConfigurationFactory
{
    /**
     * @var SettingsProvider
     */
    private SettingsProvider $settingsProvider;
    /**
     * @var Environment
     */
    private Environment $environment;

    public function __construct(
        SettingsProvider $settingsProvider,
        Environment $environment
    ) {
        $this->settingsProvider = $settingsProvider;
        $this->environment = $environment;
    }

    /**
     * Create a ClientConfiguration instance using the API key from the settings repository or from the POST of Form if submitted.
     * The provider get the right value
     *
     * @return ClientConfiguration
     */
    public function create(): ClientConfiguration
    {
        $apiKeys = $this->settingsProvider->getApiKeys();

        return new ClientConfiguration($apiKeys[$this->environment->getMode()], $this->environment);
    }
}
