<?php

namespace PrestaShop\Module\Alma\Infrastructure\Repository;

use PrestaShop\Module\Alma\Application\Helper\EncryptionHelper;
use PrestaShop\Module\Alma\Infrastructure\Form\FormCollection;
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
    /**
     * @var EncryptionHelper
     */
    private EncryptionHelper $encryptionHelper;

    public function __construct(
        ConfigurationRepository $configurationRepository,
        ToolsProxy $toolsProxy,
        EncryptionHelper $encryptionHelper
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->toolsProxy = $toolsProxy;
        $this->encryptionHelper = $encryptionHelper;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        $fields_value = [];

        foreach (FormCollection::getAllFields(FormCollection::SETTINGS_FORMS_CLASSES) as $field => $param) {
            $fields_value[$field] = $this->toolsProxy->getValue($field, $this->configurationRepository->get($field));

            if (isset($param['encrypted']) && EncryptionHelper::isEncryptionValue($param['encrypted'], $fields_value[$field])) {
                $fields_value[$field] = EncryptionHelper::OBSCURE_VALUE;
            }
        }

        return $fields_value;
    }

    /**
     * Save the fields values sent by the configuration form.
     * If the params of the field contains 'encrypted' with true value, the field value will be encrypted before saving it in the configuration.
     * @param array $fields
     * @return void
     */
    public function save(array $fields): void
    {
        foreach ($fields as $field => $param) {
            $value = $this->toolsProxy->getValue($field);

            if ($value === EncryptionHelper::OBSCURE_VALUE) {
                continue;
            }
            if (isset($param['encrypted']) && EncryptionHelper::isEncryptionValue($param['encrypted'], $value)) {
                $value = $this->encryptionHelper->encrypt($value);
            }
            $this->configurationRepository->updateValue($field, $value);
        }
    }
}
