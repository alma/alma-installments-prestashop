<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\SettingsService;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ToolsProxy;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;

class SettingsServiceTest extends TestCase
{
    public function setup(): void
    {
        $this->toolsProxy = $this->createMock(ToolsProxy::class);
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->settingsRepository = $this->createMock(SettingsRepository::class);
        $this->settingsService = new SettingsService(
            $this->settingsRepository,
            $this->toolsProxy,
            $this->configurationRepository
        );
    }

    public function testGetFieldsValue(): void
    {
        $this->configurationRepository->expects($this->any())
            ->method('get')
            ->willReturnOnConsecutiveCalls('value1', 'value2', 'value3');
        $this->toolsProxy->expects($this->any())
            ->method('getValue')
            ->willReturnOnConsecutiveCalls('value1', 'value2', 'value3');
        $this->assertIsArray($this->settingsService->getFieldsValue());
    }

    public function testSave(): void
    {
        $this->settingsRepository->expects($this->once())
            ->method('save');

        $this->settingsService->save();
    }
}
