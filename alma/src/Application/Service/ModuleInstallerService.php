<?php

namespace PrestaShop\Module\Alma\Application\Service;

class ModuleInstallerService
{
    private const HOOK_LIST = [
        'actionFrontControllerSetMedia', // Hook used for load assets
    ];

    /**
     * @var ModuleService
     */
    private ModuleService $moduleService;

    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
    }

    /**
     * Install the module by :
     * Registering all hooks
     * Create alma database tables
     * Create tabs for menus
     * Check compatibility with PS_account
     *
     * @return bool
     */
    public function install(): bool
    {
        return $this->moduleService->registerHooks(self::HOOK_LIST);
    }
}
