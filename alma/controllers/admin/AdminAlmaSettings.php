<?php

use PrestaShop\Module\Alma\Application\Exception\PsAccountsException;
use PrestaShop\Module\Alma\Application\Exception\SettingsException;
use PrestaShop\Module\Alma\Application\Service\FeePlansService;
use PrestaShop\Module\Alma\Application\Service\PsAccountsService;
use PrestaShop\Module\Alma\Application\Service\SettingsService;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FeePlansAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FormCollection;
use PrestaShop\Module\Alma\Infrastructure\Form\SettingsFormBuilder;
use PrestaShop\Module\Alma\Infrastructure\Form\ValidatorForm;

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
        /** @var ApiAdminForm $apiAdminForm */
        $apiAdminForm = $this->get('alma.api_admin_form');
        /** @var FeePlansAdminForm $feePlansAdminForm */
        $feePlansAdminForm = $this->get('alma.fee_plans_admin_form');
        /** @var FeePlansService $feePlansService */
        $feePlansService = $this->get('alma.fee_plans_service');

        $notifications = '';
        $errors = [];
        $urlAccountsCdn = '';
        $displayPsAccounts = true;
        $isAccountLinked = false;
        $token = Tools::getAdminTokenLite('AdminAlmaSettings');
        $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');

        if (Tools::isSubmit('submit' . $this->module->name)) {
            $errors = ValidatorForm::legacyValidate(FormCollection::getAllFields(FormCollection::SETTINGS_FORMS_CLASSES), Tools::getAllValues());
            if (!empty($errors)) {
                $notifications = $this->module->displayError($errors);
            }
            if (empty($errors)) {
                try {
                    $notificationSuccess = $settingsService->saveWithNotification();
                    $notifications = $this->module->displayConfirmation($notificationSuccess);
                } catch (SettingsException $e) {
                    $notifications = $this->module->displayError($e->getMessage());
                }
            }
        }

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

        if (!empty($errors)) {
            $notifications = $this->module->displayError($errors);
        }

        $templateTabs = $feePlansService->createTemplateTabs();
        $forms = [
            $apiAdminForm->build(),
            $feePlansAdminForm->build($templateTabs->fetch(), $feePlansService->feePlansFields()),
        ];

        $this->context->smarty->assign([
            'title' => 'Alma Settings - We can custom the title',
            'displayPsAccounts' => $displayPsAccounts,
            'isPsAccountsLinked' => $isAccountLinked,
            'urlAccountsCdn' => $urlAccountsCdn,
            'notifications' => $notifications,
            'form' => $settingsFormBuilder->render($token, $defaultLang, $forms),
        ]);

        $this->content = $this->module->display(
            $this->module->file,
            'views/templates/admin/settings.tpl'
        );

        parent::initContent();
    }
}
