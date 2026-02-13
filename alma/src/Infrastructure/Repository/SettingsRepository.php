<?php

namespace PrestaShop\Module\Alma\Infrastructure\Repository;

use PrestaShop\Module\Alma\Infrastructure\Form\FormCollection;
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

    public function __construct(
        ConfigurationRepository $configuration,
        ToolsProxy $tools
    ) {
        $this->configuration = $configuration;
        $this->tools = $tools;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        $fields_value = [];

        foreach (FormCollection::getAllFields(FormCollection::SETTINGS_FORMS_CLASSES) as $field => $param) {
            $fields_value[$field] = $this->tools->getValue($field, $this->configuration->get($field));
        }

        return $fields_value;
    }

    /**
     * @return void
     */
    public function save()
    {
        foreach (FormCollection::getAllFields(FormCollection::SETTINGS_FORMS_CLASSES) as $field => $param) {
            $this->configuration->updateValue($field, $this->tools->getValue($field));
        }
    }
}
