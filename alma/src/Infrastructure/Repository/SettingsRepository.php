<?php

namespace PrestaShop\Module\Alma\Infrastructure\Repository;

use PrestaShop\Module\Alma\Infrastructure\Proxy\ToolsProxy;

class SettingsRepository
{
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configurationRepository;
    /**
     * @var ToolsProxy
     */
    private ToolsProxy $toolsProxy;

    public function __construct(
        ConfigurationRepository $configurationRepository,
        ToolsProxy $toolsProxy
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->toolsProxy = $toolsProxy;
    }

    /**
     * Save the configuration form fields values.
     * @param array $fields
     * @return void
     */
    public function save(array $fields): void
    {
        foreach ($fields as $field => $param) {
            $this->configurationRepository->updateValue($field, $this->toolsProxy->getValue($field));
        }
    }
}
