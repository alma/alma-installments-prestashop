<?php

use PrestaShop\Module\Alma\Application\Exception\PsAccountsException;

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
     */
    public function initContent(): void
    {
        $psAccountsService = $this->get('alma.ps_accounts_service');
        $settingsService = $this->get('alma.settings_service');

        $errors = [];
        $urlAccountsCdn = '';
        $displayPsAccounts = true;
        $isAccountLinked = false;

        try {
            Media::addJsDef([
                'contextPsAccounts' => $psAccountsService->getPsAccountsPresenter()
                    ->present(),
            ]);
            $urlAccountsCdn = $psAccountsService->getAccountsCdn();
            // TODO : Verification is been in PHP but can be check in JS, need to wait the configuration form to check the best usage
            $isAccountLinked = $psAccountsService->isAccountLinked();
        } catch (PsAccountsException|\Exception $e) {
            $errors[] = $e->getMessage();
            $displayPsAccounts = false;
        }

        $form = $settingsService->getFormFromHelperForm();

        $this->context->smarty->assign([
            'title' => 'Alma Settings - We can custom the title',
            'displayPsAccounts' => $displayPsAccounts,
            'isPsAccountsLinked' => $isAccountLinked,
            'urlAccountsCdn' => $urlAccountsCdn,
            'errors' => $errors,
            'form' => $form,
        ]);

        $this->content = $this->module->display(
            $this->module->file,
            'views/templates/admin/settings.tpl'
        );

        parent::initContent();
    }
}
