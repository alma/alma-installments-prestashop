<?php

use PrestaShop\Module\Alma\Application\Exception\PsAccountsException;
use PrestaShop\Module\Alma\Application\Service\PsAccountsService;
use PrestaShop\Module\Alma\Application\Service\SettingsService;
use PrestaShop\Module\Alma\Infrastructure\Form\AbstractAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\SettingsFormBuilder;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;

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
        /** @var PsAccountsService $psAccountsService */
        $psAccountsService = $this->get('alma.ps_accounts_service');
        /** @var SettingsService $settingsService */
        $settingsService = $this->get('alma.settings_service');
        /** @var SettingsFormBuilder $settingsFormBuilder */
        $settingsFormBuilder = $this->get('alma.settings_form_builder');
        /** @var SettingsRepository $settings */
        $settings = $this->get('alma.settings_repository');
        /** @var ApiAdminForm $apiAdminForm */
        $apiAdminForm = $this->get('alma.api_admin_form');

        $notifications = '';
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

        if (Tools::isSubmit('submit' . $this->module->name)) {
            $errors = array_merge($settingsService->validate(AbstractAdminForm::getAllFieldsFromNamespace()));
            if (empty($errors)) {
                $settings->save();
                $notifications = $this->module->displayConfirmation('Settings updated');
            }
        }
        if (!empty($errors)) {
            $notifications = $this->module->displayError($errors);
        }

        $forms = [
            $apiAdminForm->build(),
        ];

        $this->context->smarty->assign([
            'title' => 'Alma Settings - We can custom the title',
            'displayPsAccounts' => $displayPsAccounts,
            'isPsAccountsLinked' => $isAccountLinked,
            'urlAccountsCdn' => $urlAccountsCdn,
            'notifications' => $notifications,
            'form' => $settingsFormBuilder->build($forms),
        ]);

        $this->content = $this->module->display(
            $this->module->file,
            'views/templates/admin/settings.tpl'
        );

        parent::initContent();
    }
}
