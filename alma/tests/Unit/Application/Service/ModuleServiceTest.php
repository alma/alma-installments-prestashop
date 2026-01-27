<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\ModuleService;

class ModuleServiceTest extends TestCase
{
    /**
     * @var \PrestaShop\Module\Alma\Application\Service\ModuleService
     */
    private ModuleService $moduleService;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->module = $this->createMock(\Alma::class);
        $this->moduleService = new ModuleService($this->module);
    }

    /**
     * @return void
     */
    public function testRegisterHooksWrongHookNotInstallModule()
    {
        $hook = '';
        $this->module->expects($this->once())
            ->method('registerHook')
            ->with($hook)
            ->willReturn(null);

        $this->assertFalse($this->moduleService->registerHooks($hook));
    }

    /**
     * @return void
     */
    public function testRegisterHooksFailedNotInstallModule()
    {
        $hook = 'hookFailed';
        $this->module->expects($this->once())
            ->method('registerHook')
            ->with($hook)
            ->willReturn(false);

        $this->assertFalse($this->moduleService->registerHooks($hook));
    }

    /**
     * @return void
     */
    public function testRegisterHooksSuccessInstallModule()
    {
        $hook = ['actionFrontControllerSetMedia'];
        $this->module->expects($this->once())
            ->method('registerHook')
            ->willReturn(true);

        $this->assertTrue($this->moduleService->registerHooks($hook));
    }
}
