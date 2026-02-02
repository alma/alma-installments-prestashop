<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\ModuleInstallerService;
use PrestaShop\Module\Alma\Application\Service\ModuleService;
use PrestaShop\PsAccountsInstaller\Installer\Installer;

class ModuleInstallerServiceTest extends TestCase
{
    /**
     * @var \PrestaShop\Module\Alma\Application\Service\ModuleInstallerService
     */
    private ModuleInstallerService $moduleInstallerService;
    /**
     * @var \Module
     */
    private \Module $module;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->module = $this->createMock(\Module::class);
        $this->module->name = 'alma';
        $this->moduleService = $this->createMock(ModuleService::class);
        $this->dbInstance = $this->createMock(\Db::class);
        $this->tab = $this->createMock(\Tab::class);
        $this->psAcountsInstallerService = $this->createMock(Installer::class);
        $this->moduleInstallerService = new ModuleInstallerService(
            $this->moduleService,
            $this->dbInstance
        );
    }

    /**
     * @return void
     */
    public function testInstallerRegisterHookFailedNotInstallModule()
    {
        $this->moduleService->expects($this->once())
            ->method('registerHooks')
            ->willReturn(false);

        $this->moduleService->expects($this->never())
            ->method('installTabs');

        $this->dbInstance->expects($this->never())
            ->method('execute');

        $this->assertFalse($this->moduleInstallerService->install());
    }

    /**
     * @return void
     */
    public function testInstallerTabsFailedNotInstallModule()
    {
        $this->moduleService->expects($this->once())
            ->method('registerHooks')
            ->willReturn(true);

        $this->moduleService->expects($this->once())
            ->method('installTabs')
            ->willReturn(false);

        $this->dbInstance->expects($this->never())
            ->method('execute');

        $this->assertFalse($this->moduleInstallerService->install());
    }

    /**
     * @return void
     */
    public function testInstallerDbFailedNotInstallModule()
    {
        $this->moduleService->expects($this->once())
            ->method('registerHooks')
            ->willReturn(true);

        $this->moduleService->expects($this->once())
            ->method('installTabs')
            ->willReturn(true);

        $this->dbInstance->expects($this->once())
            ->method('execute')
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

        $this->moduleService->expects($this->once())
            ->method('installTabs')
            ->willReturn(true);

        $this->dbInstance->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->moduleService->expects($this->once())
            ->method('getService')
            ->with('alma.ps_accounts_installer')
            ->willReturn($this->psAcountsInstallerService);

        $this->psAcountsInstallerService->expects($this->once())
            ->method('install')
            ->willReturn(true);

        $this->assertTrue($this->moduleInstallerService->install());
    }

    public function tearDown(): void
    {
        $this->moduleService = null;
        $this->dbInstance = null;
        parent::tearDown();
    }
}
