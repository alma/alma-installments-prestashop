<?php

namespace PrestaShop\Module\Alma\Application\Provider;

use PrestaShop\Module\Alma\Application\Helper\EncryptionHelper;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ToolsProxy;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;

class SettingsProvider
{
    private \Module $module;
    /**
     * @var SettingsRepository
     */
    private SettingsRepository $settingsRepository;
    /**
     * @var ToolsProxy
     */
    private ToolsProxy $toolsProxy;

    public function __construct(
        \Module $module,
        SettingsRepository $settingsRepository,
        ToolsProxy $toolsProxy
    ) {
        $this->module = $module;
        $this->settingsRepository = $settingsRepository;
        $this->toolsProxy = $toolsProxy;
    }

    /**
     * Get the API key from the POST if we submit Form or GET from Repository.
     * @return string
     */
    public function getApiKey(): string
    {
        $apiKey = $this->settingsRepository->getApiKey();

        if (
            $this->toolsProxy->isSubmit('submit' . $this->module->name)
            && $this->toolsProxy->getValue('ALMA_TEST_API_KEY') !== EncryptionHelper::OBSCURE_VALUE
        ) {
            $apiKey = $this->toolsProxy->getValue('ALMA_TEST_API_KEY', $apiKey);
        }

        return $apiKey;
    }

    /**
     * Get the environment from the POST if we submit Form or GET from Repository.
     * @return string
     */
    public function getEnvironment(): string
    {
        $environment = $this->settingsRepository->getEnvironment();

        if ($this->toolsProxy->isSubmit('submit' . $this->module->name)) {
            $environment = $this->toolsProxy->getValue('ALMA_API_MODE', $environment);
        }

        return $environment;
    }
}
