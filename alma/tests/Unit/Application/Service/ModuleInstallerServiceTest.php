<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\ModuleInstallerService;

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
        $this->module = $this->createMock(\Alma::class);
        $this->moduleInstallerService = new ModuleInstallerService($this->module);
    }

    /**
     * @return void
     */
    public function testRegisterHooksFailedNotInstallModule()
    {
        $this->module->expects($this->once())
            ->method('registerHook')
            ->willReturn(false);

        $this->assertFalse($this->moduleInstallerService->registerHooks());
    }

    /**
     * @return void
     */
    public function testRegisterHooksSuccessInstallModule()
    {
        $this->module->expects($this->once())
            ->method('registerHook')
            ->willReturn(true);

        $this->assertTrue($this->moduleInstallerService->registerHooks());
    }
}
