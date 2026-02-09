<?php

namespace PrestaShop\Module\Alma\Infrastructure\Factory;

use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FormBuilder;
use PrestaShop\Module\Alma\Infrastructure\Form\InputFormBuilder;
use PrestaShop\Module\Alma\Infrastructure\Form\SettingsFormBuilder;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\ToolsRepository;

class SettingsFormFactory
{
    /**
     * @return SettingsFormBuilder
     */
    public static function createSettingsFormBuilder(): SettingsFormBuilder
    {
        $apiAdminForm = self::createApiAdminForm();

        return new SettingsFormBuilder(
            new SettingsRepository(
                new ConfigurationRepository(),
                new ToolsRepository(),
            ),
            $apiAdminForm
        );
    }

    /**
     * @return ApiAdminForm
     */
    public static function createApiAdminForm(): ApiAdminForm
    {
        $formBuilder = new FormBuilder();
        $inputFormBuilder = new InputFormBuilder();

        return new ApiAdminForm($formBuilder, $inputFormBuilder);
    }
}
