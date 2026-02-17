<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Application\Exception\AuthenticationException;
use PrestaShop\Module\Alma\Application\Exception\SettingsServiceException;
use PrestaShop\Module\Alma\Infrastructure\Form\FormCollection;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ToolsProxy;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;

class SettingsService
{
    /**
     * @var SettingsRepository
     */
    private SettingsRepository $settingsRepository;
    /**
     * @var AuthenticationService
     */
    private AuthenticationService $authenticationService;
    /**
     * @var ToolsProxy
     */
    private ToolsProxy $toolsProxy;

    public function __construct(
        SettingsRepository $settingsRepository,
        AuthenticationService $authenticationService,
        ToolsProxy $toolsProxy
    ) {
        $this->settingsRepository = $settingsRepository;
        $this->authenticationService = $authenticationService;
        $this->toolsProxy = $toolsProxy;
    }

    /**
     * Get the API key from the POST of Form.
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->toolsProxy->getValue('ALMA_TEST_API_KEY', $this->settings->getApiKey());
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
     * @throws \PrestaShop\Module\Alma\Application\Exception\SettingsServiceException
     */
    public function save(): void
    {
        if (!$this->authenticationService->isAuthenticated()) {
            throw new SettingsServiceException('Authentication failed. Settings cannot be saved.');
        }

        // TODO : Duplicate catch, we can improve it
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
