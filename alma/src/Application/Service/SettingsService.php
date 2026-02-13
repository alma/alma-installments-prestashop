<?php

namespace PrestaShop\Module\Alma\Application\Service;

use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;

class SettingsService
{
    /**
     * @var SettingsRepository
     */
    private SettingsRepository $settings;

    public function __construct(
        SettingsRepository $settingsRepository
    ) {
        $this->settings = $settingsRepository;
    }

    /**
     * Save the configuration form fields values.
     */
    public function save(): void
    {
        $this->settings->save();
    }
}
