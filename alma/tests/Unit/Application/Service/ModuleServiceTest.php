<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\ModuleService;
use PrestaShop\Module\Alma\Infrastructure\Repository\LanguageRepository;

class ModuleServiceTest extends TestCase
{
    /**
     * @var \PrestaShop\Module\Alma\Application\Service\ModuleService
     */
    private ModuleService $moduleService;
    /**
     * @var \Tab
     */
    private \Tab $tab;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->module = $this->createMock(\Alma::class);
        $this->language = $this->createMock(LanguageRepository::class);
        $this->tab = $this->createMock(\Tab::class);
        $this->moduleService = new ModuleService(
            $this->module,
            $this->language
        );
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

    /**
     * @return void
     */
    public function testInstallTabsFailedReturnFalseNotInstallModule()
    {
        $tabs = [
            [
                'label' => 'Alma',
                'class_name' => 'ALMA',
                'parent' => 0,
                'icon' => null
            ]
        ];

        $tabFactory = function () {
            return $this->tab;
        };

        $this->tab->expects($this->once())
            ->method('add')
            ->willReturn(false);

        $this->assertFalse($this->moduleService->installTabs($tabs, $tabFactory));
    }

    /**
     * @return void
     */
    public function testInstallTabsWrongDataReturnFalseNotInstallModule()
    {
        $tabs = [
            [
                'label' => 'WrongTab'
            ]
        ];

        $tabFactory = function () {
            return $this->tab;
        };

        $this->tab->expects($this->never())
            ->method('add');

        $this->assertFalse($this->moduleService->installTabs($tabs, $tabFactory));
    }

    /**
     * @return void
     */
    public function testInstallTabsSuccessReturnTrueInstallModule()
    {
        $tabs = [
            [
                'label' => 'Alma',
                'class_name' => 'ALMA',
                'parent' => 0,
                'icon' => null
            ],
            [
                'label' => 'Settings',
                'class_name' => 'AdminAlmaSettings',
                'parent' => 'ALMA',
                'icon' => 'tune'
            ]
        ];

        $tabFactory = function () {
            return $this->tab;
        };

        $this->tab->expects($this->exactly(2))
            ->method('add')
            ->willReturn(true);

        $this->assertTrue($this->moduleService->installTabs($tabs, $tabFactory));
    }

    public function tearDown(): void
    {
        $this->module = null;
        parent::tearDown();
    }
}
