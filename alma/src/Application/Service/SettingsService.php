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
     * @var AuthenticationService
     */
    private AuthenticationService $authenticationService;
    /**
     * @var SettingsRepository
     */
    private SettingsRepository $settingsRepository;
    private \Module $module;
    /**
     * @var ToolsProxy
     */
    private ToolsProxy $toolsProxy;

    public function __construct(
        AuthenticationService $authenticationService,
        SettingsRepository $settingsRepository,
        \Module $module,
        ToolsProxy $toolsProxy
    ) {
        $this->authenticationService = $authenticationService;
        $this->settingsRepository = $settingsRepository;
        $this->module = $module;
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
