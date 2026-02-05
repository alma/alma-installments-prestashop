<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

use AdminController;
use Alma;
use Configuration;
use HelperForm;
use Tools;

class SettingsFormBuilder
{
    public function build(HelperForm $helperForm, Alma $module): string
    {
        // Table of inputs
        $apiForm = new ApiAdminFormType();
        $inputs = $apiForm->getForm();
        $inputs2 = [
            'form' => [
                'legend' => [
                    'title' => 'New panel',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => 'Widget',
                        'name' => 'ALMA_WIDGET',
                        'size' => 20,
                        'required' => true,
                    ],
                ],
                'submit' => [
                    'title' => 'Save',
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helperForm->table = $module->name;
        $helperForm->name_controller = $module->name;
        $helperForm->token = Tools::getAdminTokenLite('AdminModules');
        $helperForm->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $module->name]);
        $helperForm->submit_action = 'submit' . $module->name;
        $helperForm->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helperForm->fields_value = [
            'ALMA_API_KEY' => Tools::getValue('ALMA_API_KEY', Configuration::get('ALMA_API_KEY')),
            'ALMA_API_KEY_LIVE' => Tools::getValue('ALMA_API_KEY_LIVE', Configuration::get('ALMA_API_KEY_LIVE')),
            'ALMA_WIDGET' => Tools::getValue('ALMA_WIDGET', Configuration::get('ALMA_WIDGET')),
        ];

        return $helperForm->generateForm([$inputs, $inputs2]);
    }
}
