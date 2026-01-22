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

    public function registerHooks(): bool
    {
        foreach (self::HOOK_LIST as $hookName) {
            if (!$this->module->registerHook($hookName)) {
                return false;
            }
        }
        return true;
    }

    public function install(): bool
    {
        return $this->registerHooks();
    }
}
