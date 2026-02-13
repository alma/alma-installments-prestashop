<?php

namespace PrestaShop\Module\Alma\Tests\Integration\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\SettingsService;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;

class SettingsServiceTest extends TestCase
{
    public function setup(): void
    {
        $this->settings = $this->createMock(SettingsRepository::class);
        $this->settingsService = new SettingsService(
            $this->settings
        );
    }

    public function testSave(): void
    {
        $this->settings->expects($this->once())
            ->method('save');

        $this->settingsService->save();
    }
}
