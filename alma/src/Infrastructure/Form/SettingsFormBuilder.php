<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

use AdminController;
use HelperForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\ToolsRepository;

class SettingsFormBuilder
{
    /**
     * @var SettingsRepository
     */
    private SettingsRepository $settingsRepository;
    private \Module $module;
    private HelperForm $helperForm;
    /**
     * @var \PrestaShop\Module\Alma\Infrastructure\Repository\ToolsRepository
     */
    private ToolsRepository $tools;
    /**
     * @var \PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository
     */
    private ConfigurationRepository $configuration;

    public function __construct(
        \Module $module,
        HelperForm $helperForm,
        SettingsRepository $settingsRepository,
        ToolsRepository $tools,
        ConfigurationRepository $configuration
    ) {
        $this->module = $module;
        $this->helperForm = $helperForm;
        $this->settingsRepository = $settingsRepository;
        $this->tools = $tools;
        $this->configuration = $configuration;
    }

    /**
     * @param array $forms
     * @return string
     */
    public function build(array $forms = []): string
    {
        $this->helperForm->table = $this->module->name;
        $this->helperForm->name_controller = $this->module->name;
        $this->helperForm->token = $this->tools->getAdminTokenLite('AdminAlmaSettings');
        $this->helperForm->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->module->name]) . '&token=' . $this->helperForm->token;
        $this->helperForm->submit_action = 'submit' . $this->module->name;
        $this->helperForm->default_form_language = (int) $this->configuration->get('PS_LANG_DEFAULT');
        $this->helperForm->fields_value = $this->settingsRepository->get();

        return $this->helperForm->generateForm($forms);
    }
}
