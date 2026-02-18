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
            if (isset($param['getFromDb']) && $param['getFromDb'] === true) {
                $fields_value[$field] = $this->configuration->get($field);
            }

            if (isset($param['encrypted']) && EncryptionHelper::isEncryptionValue($param['encrypted'], $fields_value[$field])) {
                $fields_value[$field] = EncryptionHelper::OBSCURE_VALUE;
            }
        }

        return $fields_value;
    }

    /**
     * Get the API key value from the configuration, decrypt it if it's encrypted, and return it.
     * @return string
     */
    public function getApiKey(): string
    {
        $apiKey = $this->configuration->get('ALMA_TEST_API_KEY');
        if (empty($apiKey)) {
            return '';
        }

        if (EncryptionHelper::isEncryptionValue(true, $apiKey)) {
            return $this->encryptionHelper->decrypt($apiKey);
        }

        return $apiKey;
    }

    /**
     * Get the environment value from the configuration and return it.
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->configuration->get('ALMA_API_MODE');
    }

    /**
     * Save the fields values sent by the configuration form.
     * If the params of the field contains 'encrypted' with true value, the field value will be encrypted before saving it in the configuration.
     * @param array $fields
     * @param array $overrideValues
     * @return void
     */
    public function save(array $fields, array $overrideValues = []): void
    {
        foreach ($fields as $field => $param) {
            $value = $this->toolsProxy->getValue($field);
            if (isset($overrideValues[$field])) {
                $value = $overrideValues[$field];
            }

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
