<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Infrastructure\Form\FormCollection;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ToolsProxy;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;

class SettingsService
{
    /**
     * @var SettingsRepository
     */
    private SettingsRepository $settingsRepository;
    /**
     * @var ToolsProxy
     */
    private ToolsProxy $toolsProxy;
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configurationRepository;

    public function __construct(
        SettingsRepository $settingsRepository,
        ToolsProxy $toolsProxy,
        ConfigurationRepository $configurationRepository
    ) {
        $this->settingsRepository = $settingsRepository;
        $this->toolsProxy = $toolsProxy;
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * Get the configuration form fields values from POST.
     *
     * @return array
     */
    public function getFieldsValue(): array
    {
        $fieldsValue = [];

        foreach (FormCollection::getAllFields(FormCollection::SETTINGS_FORMS_CLASSES) as $field => $param) {
            $fieldsValue[$field] = $this->toolsProxy->getValue($field, $this->configurationRepository->get($field));
        }

        return $fieldsValue;
    }

    /**
     * Save the configuration form from all fields values.
     */
    public function save(): void
    {
        $this->settingsRepository->save(FormCollection::getAllFields(FormCollection::SETTINGS_FORMS_CLASSES));
    }
}
