<?php

namespace PrestaShop\Module\Alma\Application\Service;

use Alma;
use Configuration;
use HelperForm;
use PrestaShop\Module\Alma\Infrastructure\Form\SettingsFormBuilder;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;
use Tools;
use Validate;

class SettingsService
{
    /**
     * @var Alma
     */
    private Alma $module;
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

        // this part is executed only when the form is submitted
        if (Tools::isSubmit('submit' . $this->module->name)) {
            // retrieve the value set by the user
            $configValue = (string) Tools::getValue('ALMA_API_KEY');

            // check that the value is valid
            if (empty($configValue) || !Validate::isGenericName($configValue)) {
                // invalid value, show an error
                $output = $this->module->displayError('Invalid Configuration value');
            } else {
                // value is ok, update it and display a confirmation message
                $this->settings->save();

                $output = $this->module->displayConfirmation('Settings updated');
            }
        }

        return $output . $this->settingsFormBuilder->build(new HelperForm(), $this->module);
    }
}
