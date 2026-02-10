<?php

use PrestaShop\Module\Alma\Application\Service\PsAccountsService;
use PrestaShop\Module\Alma\Application\Service\SettingsService;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminAlmaSettingsController extends ModuleAdminController
{
    /**
     * @var PsAccountsService
     */
    private PsAccountsService $psAccountsService;
    /**
     * @var SettingsService
     */
    private SettingsService $settingsService;

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    /**
     * We need to create this controller for creating a menu item in the back office
     */
    public function initContent(): void
    {
        $this->psAccountsService = $this->get('alma.ps_accounts_service');
        $this->settingsService = $this->get('alma.settings_service');
        $this->context->smarty->assign([
            'my_variable' => 'value',
        ]);

        $this->content = $this->module->display(
            $this->module->file,
            'views/templates/admin/settings.tpl'
        );

        parent::initContent();
    }
}
