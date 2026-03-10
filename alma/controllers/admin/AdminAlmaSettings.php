<?php

use PrestaShop\Module\Alma\Application\Exception\AlmaException;
use PrestaShop\Module\Alma\Application\Exception\PsAccountsException;
use PrestaShop\Module\Alma\Application\Service\FormService;
use PrestaShop\Module\Alma\Application\Service\PsAccountsService;
use PrestaShop\Module\Alma\Application\Service\SettingsService;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
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
        /** @var FormService $formService */
        $formService = $this->get('alma.form_service');

        $notifications = '';
        $errors = [];
        $urlAccountsCdn = '';
        $displayPsAccounts = true;
        $isAccountLinked = false;
        $token = Tools::getAdminTokenLite('AdminAlmaSettings');
        $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');
        $allowEmployeeFormLang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $languages = $this->context->controller->getLanguages();

        if (Tools::isSubmit('submit' . $this->module->name)) {
            $settingsFormClasses = FormCollection::SETTINGS_FORMS_CLASSES_BEFORE_AUTH;
            if (Configuration::get(ApiAdminForm::KEY_FIELD_MERCHANT_ID)) {
                $settingsFormClasses = FormCollection::SETTINGS_FORMS_CLASSES;
            }
            $splitLanguageFields = $settingsService->getSplitLanguageFields(FormCollection::getAllFields($settingsFormClasses));
            $errors = ValidatorForm::legacyValidate($splitLanguageFields, Tools::getAllValues());
            if (!empty($errors)) {
                $notifications = $this->module->displayError($errors);
            } else {
                try {
                    $notificationSuccess = $settingsService->saveWithNotification();
                    $notifications = $this->module->displayConfirmation($notificationSuccess);
                } catch (AlmaException $e) {
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

        $this->context->smarty->assign([
            'title' => 'Alma Settings - We can custom the title',
            'displayPsAccounts' => $displayPsAccounts,
            'isPsAccountsLinked' => $isAccountLinked,
            'urlAccountsCdn' => $urlAccountsCdn,
            'notifications' => $notifications,
            'form' => $settingsFormBuilder->render($token, $defaultLang, $allowEmployeeFormLang, $languages, $formService->getForm()),
        ]);

        $this->content = $this->module->display(
            $this->module->file,
            'views/templates/admin/settings.tpl'
        );

        parent::initContent();
    }
}
