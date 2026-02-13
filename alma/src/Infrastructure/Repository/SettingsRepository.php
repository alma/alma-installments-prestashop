<?php

namespace PrestaShop\Module\Alma\Infrastructure\Repository;

use PrestaShop\Module\Alma\Infrastructure\Form\SettingsCollectionForm;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ToolsProxy;

class SettingsRepository
{
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configuration;
    /**
     * @var ToolsProxy
     */
    private ToolsProxy $tools;
    /**
     * @var SettingsCollectionForm
     */
    private SettingsCollectionForm $settingsCollectionForm;

    public function __construct(
        SettingsCollectionForm $settingsCollectionForm,
        ConfigurationRepository $configuration,
        ToolsProxy $tools
    ) {
        $this->settingsCollectionForm = $settingsCollectionForm;
        $this->configuration = $configuration;
        $this->tools = $tools;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        $fields_value = [];

        foreach ($this->settingsCollectionForm->getAllFields(SettingsCollectionForm::SETTINGS_FORMS_CLASSES) as $field => $param) {
            $fields_value[$field] = $this->tools->getValue($field, $this->configuration->get($field));
        }

        return $fields_value;
    }

    /**
     * @return void
     */
    public function save()
    {
        foreach ($this->settingsCollectionForm->getAllFields(SettingsCollectionForm::SETTINGS_FORMS_CLASSES) as $field => $param) {
            $this->configuration->updateValue($field, $this->tools->getValue($field));
        }
    }
}
