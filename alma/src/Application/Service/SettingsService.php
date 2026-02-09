<?php

namespace PrestaShop\Module\Alma\Application\Service;

use Alma;
use Configuration;
use HelperForm;
use PrestaShop\Module\Alma\Infrastructure\Form\SettingsFormBuilder;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\ToolsRepository;
use Tools;
use Validate;

class SettingsService
{
    /**
     * @var Alma
     */
    private \Module $module;
    /**
     * @var SettingsFormBuilder
     */
    private SettingsFormBuilder $settingsFormBuilder;
    /**
     * @var SettingsRepository
     */
    private SettingsRepository $settings;

    public function __construct(
        Alma $module,
        SettingsFormBuilder $settingsFormBuilder,
        SettingsRepository $settingsRepository
    ) {
        $this->module = $module;
        $this->settingsFormBuilder = $settingsFormBuilder;
        $this->settings = $settingsRepository;
    }

    /**
     * Get the configuration form for the module with the old system HelperForm (legacy).
     * @return string
     */
    public function getFormFromHelperForm(): string
    {
        $output = '';

        if (Tools::isSubmit('submit' . $this->module->name)) {
            // TODO : need to validate each fields and return error if not
            $configValue = (string) Tools::getValue('ALMA_API_KEY_TEST');
            if (empty($configValue) || !Validate::isGenericName($configValue)) {
                $output = $this->module->displayError('Invalid Configuration value');
            } else {
                $this->settings->save();
                $output = $this->module->displayConfirmation('Settings updated');
            }
        }

        return $output . $this->settingsFormBuilder->build(
            new HelperForm(),
            $this->module,
            new ToolsRepository(),
            new ConfigurationRepository()
        );
    }
}
