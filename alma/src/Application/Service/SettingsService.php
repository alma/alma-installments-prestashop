<?php

namespace PrestaShop\Module\Alma\Application\Service;

use Alma;
use PrestaShop\Module\Alma\Infrastructure\Form\SettingsFormBuilder;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\ToolsRepository;
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
