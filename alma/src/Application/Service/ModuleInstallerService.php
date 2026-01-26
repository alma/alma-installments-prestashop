<?php

namespace PrestaShop\Module\Alma\Application\Service;

class ModuleInstallerService
{
    public const HOOK_LIST = [
        'actionFrontControllerSetMedia',
    ];
    /**
     * @var \Alma
     */
    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * Register all hooks needed by the module
     * If $this->module->registerHook return false the module does not install
     *
     * @return bool
     */
    public function registerHooks(): bool
    {
        if (!$this->module->registerHook(self::HOOK_LIST)) {
            return false;
        }

        return true;
    }

    public function install(): bool
    {
        return $this->registerHooks();
    }
}
