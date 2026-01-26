<?php

namespace PrestaShop\Module\Alma\Tests\Integration\Application\Service;

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
        $this->module = \Module::getInstanceByName('alma');
        $this->moduleInstallerService = new ModuleInstallerService($this->module);
    }

    /**
     * @return void
     */
    public function testRegisterHooksSuccessfullyInstallationSuccess()
    {
        // Remove all hooks before testing
        foreach (ModuleInstallerService::HOOK_LIST as $hook) {
            $this->module->unregisterHook($hook);
        }

        $this->assertTrue($this->moduleInstallerService->registerHooks());

        // Check that all hooks are registered
        foreach (ModuleInstallerService::HOOK_LIST as $hook) {
            $this->assertTrue(
                \Hook::isModuleRegisteredOnHook($this->module, $hook, 1),
                sprintf('Hook %s should be register', $hook)
            );
        }
    }

    /**
     * @return void
     */
    public function testRegisterHooksWhenAlreadyRegisteredInstallationSuccess()
    {
        $this->moduleInstallerService->registerHooks();

        $this->assertTrue($this->moduleInstallerService->registerHooks());
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->moduleInstallerService->registerHooks();

        parent::tearDown();
    }
}
