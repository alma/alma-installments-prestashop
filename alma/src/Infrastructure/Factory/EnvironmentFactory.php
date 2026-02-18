<?php

namespace PrestaShop\Module\Alma\Infrastructure\Factory;

use Alma\Client\Domain\ValueObject\Environment;
use PrestaShop\Module\Alma\Application\Service\SettingsService;

class EnvironmentFactory
{
    /**
     * @var SettingsService
     */
    private SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Create an Environment instance based on the settings repository.
     *
     * @return Environment
     */
    public function create(): Environment
    {
        $environment = $this->settingsService->getEnvironment();

        return new Environment($environment);
    }
}
