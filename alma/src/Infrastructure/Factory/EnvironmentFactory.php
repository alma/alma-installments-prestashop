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
     * Create an Environment instance using the mode from the settings repository or from the POST of Form if submitted.
     * The provider get the right value
     *
     * @return Environment
     */
    public function create(): Environment
    {
        $environment = $this->settingsProvider->getEnvironment();

        return new Environment($environment);
    }
}
