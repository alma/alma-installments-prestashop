<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminAlmaSettingsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    /**
     * We need to create this controller for creating a menu item in the back office
     * This controller redirect to the module configuration page
     */
    public function initContent(): void
    {
        parent::initContent();

        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminModules', true, [], [
                'configure' => $this->module->name,
                'module_name' => $this->module->name,
                'tab_module' => $this->module->tab
            ])
        );
    }
}
