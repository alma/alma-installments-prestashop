<?php

namespace PrestaShop\Module\Alma\Application\Service;

use Module;

class ModuleService
{
    /**
     * @var \Alma
     */
    private Module $module;

    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    /**
     * Get the module instance
     *
     * @return Module
     */
    public function getModule(): Module
    {
        return $this->module;
    }

    /**
     * Register one or all hooks needed by the module
     * If $this->module->registerHook return false the module does not install
     *
     * @param $hook string|array
     *
     * @return bool
     */
    public function registerHooks($hook): bool
    {
        if (!$this->module->registerHook($hook)) {
            return false;
        }

        return true;
    }
}
