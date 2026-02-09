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
     * @var ApiAdminForm
     */
    private ApiAdminForm $apiAdminForm;
    /**
     * @var SettingsRepository
     */
    private SettingsRepository $settingsRepository;

    public function __construct(SettingsRepository $settingsRepository, ApiAdminForm $apiAdminForm)
    {
        $this->settingsRepository = $settingsRepository;
        $this->apiAdminForm = $apiAdminForm;
    }

    /**
     * @param HelperForm $helperForm
     * @param \Module $module
     * @param ToolsRepository $tools
     * @param ConfigurationRepository $configuration
     *
     * @return string
     */
    public function build(
        HelperForm $helperForm,
        \Module $module,
        ToolsRepository $tools,
        ConfigurationRepository $configuration
    ): string {
        // Table of inputs
        $apiForm = $this->apiAdminForm->build();

        $helperForm->table = $module->name;
        $helperForm->name_controller = $module->name;
        $helperForm->token = $tools->getAdminTokenLite('AdminModules');
        $helperForm->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $module->name]);
        $helperForm->submit_action = 'submit' . $module->name;
        $helperForm->default_form_language = (int) $configuration->get('PS_LANG_DEFAULT');
        $helperForm->fields_value = $this->settingsRepository->get();

        return $helperForm->generateForm([$apiForm]);
    }
}
