<?php

namespace PrestaShop\Module\Alma\Tests\Integration\Application\Service;

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
     * @return void
     */
    public function setUp(): void
    {
        $this->module = \Module::getInstanceByName('alma');
        $this->language = new LanguageRepository();
        $this->moduleService = new ModuleService(
            $this->module,
            $this->language
        );
    }

    /**
     * @return void
     */
    public function testRegisterHooksSuccessfullyInstallationSuccess()
    {
        $hooks = ['actionFrontControllerSetMedia'];
        $this->assertTrue($this->moduleService->registerHooks($hooks));

        // Check that all hooks are registered
        foreach ($hooks as $hook) {
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
        $hook = ['actionFrontControllerSetMedia'];
        $this->moduleService->registerHooks($hook);

        $this->assertTrue($this->moduleService->registerHooks($hook));
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->module = null;
        parent::tearDown();
    }
}
