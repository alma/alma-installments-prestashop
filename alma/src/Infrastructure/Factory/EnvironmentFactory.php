<?php

namespace PrestaShop\Module\Alma\Infrastructure\Factory;

use Alma\Client\Domain\ValueObject\Environment;
use PrestaShop\Module\Alma\Application\Provider\SettingsProvider;

class EnvironmentFactory
{
    /**
     * @var SettingsProvider
     */
    private SettingsProvider $settingsProvider;

    public function __construct(SettingsProvider $settingsProvider)
    {
        $this->settingsProvider = $settingsProvider;
    }

    /**
     * Create an Environment instance based on the settings repository.
     *
     * @return Environment
     */
    public function create(): Environment
    {
        $environment = $this->settingsProvider->getEnvironment();

        return new Environment($environment);
    }
}
