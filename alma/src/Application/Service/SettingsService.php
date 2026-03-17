<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Application\Helper\EncryptorHelper;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FormCollection;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ToolsProxy;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;

class SettingsService
{
    /**
     * @var AuthenticationService
     */
    private AuthenticationService $authenticationService;
    /**
     * @var SettingsRepository
     */
    private SettingsRepository $settingsRepository;
    /**
     * @var ConfigurationRepository
     */
    private ConfigurationRepository $configurationRepository;
    /**
     * @var ToolsProxy
     */
    private ToolsProxy $toolsProxy;
    /**
     * @var \PrestaShop\Module\Alma\Application\Service\FeePlansService
     */
    private FeePlansService $feePlansService;

    public function __construct(
        AuthenticationService $authenticationService,
        FeePlansService $feePlansService,
        SettingsRepository $settingsRepository,
        ConfigurationRepository $configurationRepository,
        ToolsProxy $toolsProxy
    ) {
        $this->authenticationService = $authenticationService;
        $this->feePlansService = $feePlansService;
        $this->settingsRepository = $settingsRepository;
        $this->configurationRepository = $configurationRepository;
        $this->toolsProxy = $toolsProxy;
    }

    /**
     * Get the configuration form fields values from POST or DB for put the value on each input.
     * Sometimes we need to get the value only from DB with the param 'getFromDb' if the field is not in the POST,
     * to avoid losing the value when we save the form without changing all fields.
     * And if the field is encrypted, we need to put the obscure value in the input to not show the encrypted value.
     *
     * @return array
     */
    public function getFieldsValue(): array
    {
        $feePlansFieldsValue = $this->feePlansService->fieldsValue();
        $fieldsValue = array_merge(
            FormCollection::getAllFields(FormCollection::SETTINGS_FORMS_CLASSES),
            $feePlansFieldsValue
        );

        foreach ($fieldsValue as $field => $param) {
            $fieldsValue[$field] = $this->toolsProxy->getValue($field, $this->configurationRepository->get($field));
            // This function is to get the value from the database if the field is not in the POST.
            if (isset($param['getFromDb']) && $param['getFromDb'] === true) {
                $fieldsValue[$field] = $this->configurationRepository->get($field);
            }

            if (isset($param['encrypted']) && EncryptorHelper::isEncryptionValue($param['encrypted'], $fieldsValue[$field])) {
                $fieldsValue[$field] = EncryptorHelper::OBSCURE_VALUE;
            }
        }

        return $fieldsValue;
    }

    /**
     * Check validity of the API keys and the equal merchantIds
     * Save the configuration form from all fields values.
     * And return the notification message to show in the configuration form after saving it.
     *
     * @return string
     * @throws \PrestaShop\Module\Alma\Application\Exception\AuthenticationException
     * @throws \PrestaShop\Module\Alma\Application\Exception\FeePlansException
     */
    public function saveWithNotification(): string
    {
        $notificationSuccess = 'Settings successfully updated';
        $merchantIds = $this->authenticationService->isValidKeys();
        $this->authenticationService->checkSameMerchantIds($merchantIds);

        $mode = $this->toolsProxy->getValue(ApiAdminForm::KEY_FIELD_MODE, $this->settingsRepository->getEnvironment());
        if (!array_key_exists($mode, $merchantIds)) {
            $mode = key($merchantIds);
            $overrideValues[ApiAdminForm::KEY_FIELD_MODE] = $mode;
            $notificationSuccess = "Mode automatically switched to {$mode} mode. To use the other mode, please enter the corresponding API key.";
        }

        $overrideValues[ApiAdminForm::KEY_FIELD_MERCHANT_ID] = $merchantIds[$mode];

        $feePlansFieldsValue = $this->feePlansService->fieldsToSave();
        $fieldsValue = array_merge(
            FormCollection::getAllFields(FormCollection::SETTINGS_FORMS_CLASSES),
            $feePlansFieldsValue
        );
        $overrideValues = array_merge(
            $overrideValues,
            $feePlansFieldsValue
        );

        $this->settingsRepository->save(
            $fieldsValue,
            $overrideValues
        );

        return $notificationSuccess;
    }
}
