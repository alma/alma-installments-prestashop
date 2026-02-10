<?php

namespace PrestaShop\Module\Alma\Application\Service;

use Alma;
use Configuration;
use HelperForm;
use PrestaShop\Module\Alma\Infrastructure\Controller\SettingsController;
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
    /**
     * @var ToolsRepository
     */
    private ToolsRepository $tools;

    public function __construct(
        \Module $module,
        SettingsFormBuilder $settingsFormBuilder,
        SettingsRepository $settingsRepository,
        ToolsRepository $tools
    ) {
        $this->module = $module;
        $this->settingsFormBuilder = $settingsFormBuilder;
        $this->settings = $settingsRepository;
        $this->tools = $tools;
    }

    /**
     * Get the configuration form for the module with the old system HelperForm (legacy).
     * @return string
     */
    public function getFormFromHelperForm(): string
    {
        $output = '';

        if (Tools::isSubmit('submit' . $this->module->name)) {
            $errors = $this->validate(SettingsController::FIELDS_FORM);
            if (!empty($errors)) {
                $output = $this->module->displayError($errors);
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

    /**
     * Validate the configuration form fields.
     *
     * @param array $fieldsForm The fields to validate with their parameters (type, required, etc.)
     *
     * @return array an array of error messages if validation fails, or an empty array if validation passes
     */
    public function validate(array $fieldsForm): array
    {
        $errors = [];
        foreach ($fieldsForm as $field => $params) {
            if ($params['required'] === false) {
                continue;
            }
            $value = $this->tools->getValue($field);
            if (empty($value) || !Validate::isGenericName($value)) {
                $errors[] = sprintf('Invalid Configuration value for %s', $field);
            }
        }

        return $errors;
    }
}
