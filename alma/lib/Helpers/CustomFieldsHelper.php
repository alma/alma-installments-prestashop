<?php
/**
 * 2018-2023 Alma SAS.
 *
 * THE MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    Alma SAS <contact@getalma.eu>
 * @copyright 2018-2023 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Helpers;

use Alma\PrestaShop\Forms\ExcludedCategoryAdminFormBuilder;
use Alma\PrestaShop\Forms\PaymentButtonAdminFormBuilder;
use Alma\PrestaShop\Forms\PaymentOnTriggeringAdminFormBuilder;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class CustomFieldsHelper
 */
class CustomFieldsHelper
{
    /**
     * Init default custom fields in ps_configuration table
     *
     * @return void
     */
    public static function initCustomFields()
    {
        $languages = \Language::getLanguages(false);

        $keysCustomFields = array_keys(static::customFields());

        foreach ($keysCustomFields as $keyCustomFields) {
            SettingsHelper::updateValue(
                $keyCustomFields,
                json_encode(static::getAllLangCustomFieldByKeyConfig($keyCustomFields, $languages))
            );
        }
    }

    /**
     * Default custom fields
     *
     * @return array
     */
    public static function customFields()
    {
        $textPnxButtonTitle = 'Pay in %d installments';
        $textButtonDescription = 'Fast and secure payment by credit card.';

        $module = new \Alma();
        $module->l('Pay now by credit card', ConstantsHelper::SOURCE_CUSTOM_FIELDS);
        $module->l('Pay in %d installments', ConstantsHelper::SOURCE_CUSTOM_FIELDS);
        $module->l('Buy now Pay in %d days', ConstantsHelper::SOURCE_CUSTOM_FIELDS);
        $module->l('Fast and secure payment by credit card.', ConstantsHelper::SOURCE_CUSTOM_FIELDS);
        $module->l('Fast and secure payments.', ConstantsHelper::SOURCE_CUSTOM_FIELDS);
        $module->l('Your cart is not eligible for payments with Alma.', ConstantsHelper::SOURCE_CUSTOM_FIELDS);
        $module->l('At shipping', ConstantsHelper::SOURCE_CUSTOM_FIELDS);

        return [
            PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_TITLE => 'Pay now by credit card',
            PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_DESC => 'Fast and secure payments.',
            PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE => $textPnxButtonTitle,
            PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC => $textButtonDescription,
            PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE => 'Buy now Pay in %d days',
            PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC => $textButtonDescription,
            PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE => $textPnxButtonTitle,
            PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_DESC => $textButtonDescription,
            ExcludedCategoryAdminFormBuilder::ALMA_NOT_ELIGIBLE_CATEGORIES => 'Your cart is not eligible for payments with Alma.',
            PaymentOnTriggeringAdminFormBuilder::ALMA_DESCRIPTION_TRIGGER => 'At shipping',
        ];
    }

    /**
     * Get all languages custom field by key config
     *
     * @param string $keyConfig
     * @param array $languages
     *
     * @return array
     */
    public static function getAllLangCustomFieldByKeyConfig($keyConfig, $languages)
    {
        $result = [];

        foreach ($languages as $language) {
            $result[$language['id_lang']] = [
                'locale' => $language['iso_code'],
                'string' => LocaleHelper::getModuleTranslation(
                    static::customFields()[$keyConfig],
                    ConstantsHelper::SOURCE_CUSTOM_FIELDS,
                    $language['iso_code']
                ),
            ];
        }

        return $result;
    }

    /**
     * Treatment for format array custom fields for read in front
     *
     * @param string $keyConfig
     * @param array $languages
     *
     * @return array
     */
    public static function getCustomFieldByKeyConfig($keyConfig, $languages)
    {
        $defaultField = static::getAllLangCustomFieldByKeyConfig($keyConfig, $languages);
        $result = [];

        $allLangField = json_decode(SettingsHelper::get($keyConfig, json_encode($defaultField)), true);

        foreach ($allLangField as $key => $field) {
            $result[$key] = $field['string'];
        }

        return $result;
    }

    /**
     * Aggregate all languages if missing for custom fields
     *
     * @param string $keyConfig
     *
     * @return array
     */
    public static function aggregateAllLanguagesCustomFields($keyConfig)
    {
        $languages = \Language::getLanguages(false);
        $arrayFields = static::getCustomFieldByKeyConfig($keyConfig, $languages);

        if (count($arrayFields) < count($languages)) {
            foreach ($languages as $lang) {
                if (!array_key_exists($lang['id_lang'], $arrayFields)) {
                    $arrayFields[$lang['id_lang']] = LocaleHelper::getModuleTranslation(
                        static::customFields()[$keyConfig],
                        ConstantsHelper::SOURCE_CUSTOM_FIELDS,
                        $lang['iso_code']
                    );
                }
            }
        }

        return $arrayFields;
    }
}
