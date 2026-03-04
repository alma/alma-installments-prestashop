<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

use AdminController;
use HelperForm;
use PrestaShop\Module\Alma\Application\Service\SettingsService;

class SettingsFormBuilder
{
    private \Module $module;
    private HelperForm $helperForm;
    /**
     * @var SettingsService
     */
    private SettingsService $settingsService;

    public function __construct(
        \Module $module,
        HelperForm $helperForm,
        SettingsService $settingsService
    ) {
        $this->module = $module;
        $this->helperForm = $helperForm;
        $this->settingsService = $settingsService;
    }

    /**
     * @param string $token
     * @param int $defaultLang
     * @param array $forms
     * @return string
     * @throws \PrestaShop\Module\Alma\Application\Exception\FeePlansException
     */
    public function render(string $token, int $defaultLang, array $forms = []): string
    {
        $this->helperForm->table = $this->module->name;
        $this->helperForm->name_controller = $this->module->name;
        $this->helperForm->token = $token;
        $this->helperForm->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->module->name]);
        $this->helperForm->submit_action = 'submit' . $this->module->name;
        $this->helperForm->default_form_language = $defaultLang;
        $this->helperForm->fields_value = $this->settingsService->getFieldsValue();

        return $this->helperForm->generateForm($forms);
    }
}
