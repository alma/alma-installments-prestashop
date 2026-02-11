<?php

namespace PrestaShop\Module\Alma\Infrastructure\Repository;

use PrestaShop\Module\Alma\Infrastructure\Form\AbstractAdminForm;

class SettingsRepository
{
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configuration;
    /**
     * @var ToolsRepository
     */
    private ToolsRepository $tools;

    public function __construct(ConfigurationRepository $configuration, ToolsRepository $tools)
    {
        $this->configuration = $configuration;
        $this->tools = $tools;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        $fields_value = [];

        foreach (AbstractAdminForm::getAllFieldsFromNamespace() as $field => $param) {
            $fields_value[$field] = $this->tools->getValue($field, $this->configuration->get($field));
        }

        return $fields_value;
    }

    /**
     * @return void
     */
    public function save()
    {
        foreach (AbstractAdminForm::getAllFieldsFromNamespace() as $field => $param) {
            $this->configuration->updateValue($field, $this->tools->getValue($field));
        }
    }
}
