<?php

namespace PrestaShop\Module\Alma\Application\Provider;

use PrestaShop\Module\Alma\Application\Helper\EncryptionHelper;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
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
     * @return array
     */
    public function getApiKeys(): array
    {
        $apiKeys = $this->settingsRepository->getApiKeys();

        if ($this->toolsProxy->isSubmit('submit' . $this->module->name)) {
            foreach ($apiKeys as $environment => $apiKey) {
                if ($this->toolsProxy->getValue(ApiAdminForm::KEY_FIELDS_API_KEYS[$environment]) !== EncryptionHelper::OBSCURE_VALUE) {
                    $apiKeys[$environment] = $this->toolsProxy->getValue(ApiAdminForm::KEY_FIELDS_API_KEYS[$environment], $apiKey);
                }
            }
        }

        return $apiKeys;
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
