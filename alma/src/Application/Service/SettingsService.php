<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Application\Exception\AuthenticationException;
use PrestaShop\Module\Alma\Application\Exception\SettingsServiceException;
use PrestaShop\Module\Alma\Application\Helper\EncryptionHelper;
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

    public function __construct(
        AuthenticationService $authenticationService,
        SettingsRepository $settingsRepository,
        ConfigurationRepository $configurationRepository,
        ToolsProxy $toolsProxy
    ) {
        $this->authenticationService = $authenticationService;
        $this->settingsRepository = $settingsRepository;
        $this->configurationRepository = $configurationRepository;
        $this->toolsProxy = $toolsProxy;
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
            // This function is to get the value from the database if the field is not in the POST, to avoid losing the value when we save the form without changing all fields.
            if (isset($param['getFromDb']) && $param['getFromDb'] === true) {
                $fieldsValue[$field] = $this->configurationRepository->get($field);
            }

            if (isset($param['encrypted']) && EncryptionHelper::isEncryptionValue($param['encrypted'], $fieldsValue[$field])) {
                $fieldsValue[$field] = EncryptionHelper::OBSCURE_VALUE;
            }
        }

        return $fieldsValue;
    }

    /**
     * Save the configuration form from all fields values.
     * @throws \PrestaShop\Module\Alma\Application\Exception\SettingsServiceException
     */
    public function save(): void
    {
        try {
            $merchantIds = $this->authenticationService->isValidKey();
            $this->authenticationService->checkSameMerchantIds($merchantIds);
        } catch (AuthenticationException $e) {
            throw new SettingsServiceException($e->getMessage());
        }

        // TODO : Duplicate catch, can we improve it ?
        try {
            $overrideValues = [
                'ALMA_MERCHANT_ID' => $this->authenticationService->getMerchantId()
            ];
        } catch (AuthenticationException $e) {
            throw new SettingsServiceException($e->getMessage());
        }

        $this->settingsRepository->save(
            FormCollection::getAllFields(FormCollection::SETTINGS_FORMS_CLASSES),
            $overrideValues
        );
    }
}
