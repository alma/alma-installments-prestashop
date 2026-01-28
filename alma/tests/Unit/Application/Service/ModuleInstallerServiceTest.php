<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\ModuleInstallerService;
use PrestaShop\Module\Alma\Application\Service\ModuleService;

class ModuleInstallerServiceTest extends TestCase
{
    /**
     * @var \PrestaShop\Module\Alma\Application\Service\ModuleInstallerService
     */
    private ModuleInstallerService $moduleInstallerService;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->moduleService = $this->createMock(ModuleService::class);
        $this->moduleInstallerService = new ModuleInstallerService($this->moduleService);
    }

    /**
     * @return void
     */
    public function testInstallFailedNotInstallModule()
    {
        $this->moduleService->expects($this->once())
            ->method('registerHooks')
            ->willReturn(false);

        $this->assertFalse($this->moduleInstallerService->install());
    }

    /**
     * @return void
     */
    public function testInstallSuccessInstallModule()
    {
        $this->moduleService->expects($this->once())
            ->method('registerHooks')
            ->willReturn(true);

        $this->assertTrue($this->moduleInstallerService->install());
    }
}
