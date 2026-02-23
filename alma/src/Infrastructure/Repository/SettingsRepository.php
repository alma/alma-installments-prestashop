<?php

namespace PrestaShop\Module\Alma\Infrastructure\Repository;

use Alma\Client\Domain\ValueObject\Environment;
use PrestaShop\Module\Alma\Application\Helper\EncryptorHelper;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
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
     * @var EncryptorHelper
     */
    private EncryptorHelper $encryptorHelper;

    public function __construct(
        ConfigurationRepository $configurationRepository,
        ToolsProxy $toolsProxy,
        EncryptorHelper $encryptorHelper
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->toolsProxy = $toolsProxy;
        $this->encryptorHelper = $encryptorHelper;
    }

    /**
     * Get the API keys values from the configuration in array with key 'test' ans 'live'
     * Decrypt it if it's encrypted, and return it.
     * @return array
     */
    public function getApiKeys(): array
    {
        $apiKeys = [
            Environment::TEST_MODE => $this->configurationRepository->get(ApiAdminForm::KEY_FIELD_TEST_API_KEY),
            Environment::LIVE_MODE => $this->configurationRepository->get(ApiAdminForm::KEY_FIELD_LIVE_API_KEY),
        ];
        foreach ($apiKeys as $mode => $value) {
            $apiKeys[$mode] = '';

            if (EncryptorHelper::isEncryptionValue(true, $value)) {
                $apiKeys[$mode] = $this->encryptorHelper->decrypt($value);
            }
        }

        return $apiKeys;
    }

    /**
     * Get the environment value from the configuration and return it.
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->configurationRepository->get(ApiAdminForm::KEY_FIELD_MODE);
    }

    /**
     * Save the fields values sent by the configuration form.
     * If the params of the field contains 'encrypted' with true value, the field value will be encrypted before saving it in the configuration.
     * If the type of the field is 'html', we will not save it in the configuration because it's not a real field, it's just for display.
     * We can override the value of the field with the param $overrideValues to set the value from another service, like API to update the value.
     *
     * @param array $fields
     * @param array $overrideValues
     * @return void
     */
    public function save(array $fields, array $overrideValues = []): void
    {
        foreach ($fields as $keyField => $paramField) {
            $value = $this->toolsProxy->getValue($keyField);
            if (isset($overrideValues[$keyField])) {
                $value = $overrideValues[$keyField];
            }

            if (isset($paramField['type']) && $paramField['type'] === 'html') {
                continue;
            }

            if ($value === EncryptorHelper::OBSCURE_VALUE) {
                continue;
            }

            if (isset($paramField['encrypted']) && EncryptorHelper::isEncryptionValue($paramField['encrypted'], $value)) {
                $value = $this->encryptorHelper->encrypt($value);
            }
            $this->configurationRepository->updateValue($keyField, $value);
        }
    }
}
