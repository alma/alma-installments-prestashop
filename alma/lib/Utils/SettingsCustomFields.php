<?php
/**
 * 2018-2022 Alma SAS
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
 * @copyright 2018-2022 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Utils;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma;
use Alma\PrestaShop\Forms\ExcludedCategoryAdminFormBuilder;
use Alma\PrestaShop\Forms\PaymentButtonAdminFormBuilder;
use Alma\PrestaShop\Forms\PaymentOnTriggeringAdminFormBuilder;
use Language;

/**
 * Class SettingsCustomFields
 */
class SettingsCustomFields
{
    const SOURCECUSTOMFIELDS = 'SettingsCustomFields';

    /**
     * Init default custom fileds in ps_configuration table
     *
     * @return void
     */
    public static function initCustomFields()
    {
        $languages = Language::getLanguages(false);

        $keysCustomFields = array_keys(self::customFields());
        foreach ($keysCustomFields as $keyCustomFields) {
            Settings::updateValue($keyCustomFields, json_encode(self::getAllLangCustomFieldByKeyConfig($keyCustomFields, $languages)));
        }
    }

    /**
     * Default custom fields
     *
     * @return array
     */
    public static function customFields()
    {
        $TextButtonDescription = 'Fast and secure payment by credit card.';

        $module = new Alma();
        $module->l('Pay in installments with Alma', self::SOURCECUSTOMFIELDS);
        $module->l('Buy now, Pay later with Alma', self::SOURCECUSTOMFIELDS);
        $module->l('Spread your payments with Alma', self::SOURCECUSTOMFIELDS);
        $module->l('Fast and secure payment by credit card.', self::SOURCECUSTOMFIELDS);
        $module->l('Your cart is not eligible for payments with Alma.', self::SOURCECUSTOMFIELDS);
        $module->l('At shipping', self::SOURCECUSTOMFIELDS);

        return [
            PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE => 'Pay in installments with Alma',
            PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC => $TextButtonDescription,
            PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE => 'Buy now, Pay later with Alma',
            PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC => $TextButtonDescription,
            PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE => 'Spread your payments with Alma',
            PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_DESC => $TextButtonDescription,
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
        foreach ($languages as $language) {
            $return[$language['id_lang']] = [
                'locale' => $language['iso_code'],
                'string' => LocaleHelper::getModuleTranslation(self::customFields()[$keyConfig], self::SOURCECUSTOMFIELDS, $language['iso_code']),
            ];
        }

        return $return;
    }

    /**
     * Traitment for format array custom fields for read in front
     *
     * @param string $keyConfig
     * @param array $languages
     *
     * @return array
     */
    public static function getCustomFieldByKeyConfig($keyConfig, $languages)
    {
        $defaultField = self::getAllLangCustomFieldByKeyConfig($keyConfig, $languages);

        $allLangField = json_decode(Settings::get($keyConfig, json_encode($defaultField)), true);
        foreach ($allLangField as $key => $field) {
            $return[$key] = $field['string'];
        }

        return $return;
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
        $languages = Language::getLanguages(false);
        $arrayFields = self::getCustomFieldByKeyConfig($keyConfig, $languages);
        $countArrayFields = count($arrayFields);
        $countLanguages = count($languages);

        if ($countArrayFields < $countLanguages) {
            foreach ($languages as $lang) {
                if (!array_key_exists($lang['id_lang'], $arrayFields)) {
                    $arrayFields[$lang['id_lang']] = LocaleHelper::getModuleTranslation(
                        self::customFields()[$keyConfig],
                        self::SOURCECUSTOMFIELDS,
                        $lang['iso_code']
                    );
                }
            }
        }

        return $arrayFields;
    }

    /**
     * Get array custom titles button
     *
     * @return array
     */
    public static function getPnxButtonTitle()
    {
        return self::aggregateAllLanguagesCustomFields(PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE);
    }

    /**
     * Get custom title button by id lang
     *
     * @param int $idLang
     *
     * @return string
     */
    public static function getPnxButtonTitleByLang($idLang)
    {
        return self::getPnxButtonTitle()[$idLang];
    }

    /**
     * Get array custom description button
     *
     * @return array
     */
    public static function getPnxButtonDescription()
    {
        return self::aggregateAllLanguagesCustomFields(PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC);
    }

    /**
     * Get custom description button by id lang
     *
     * @param int $idLang
     *
     * @return string
     */
    public static function getPnxButtonDescriptionByLang($idLang)
    {
        return self::getPnxButtonDescription()[$idLang];
    }

    /**
     * Get array custom title deferred button
     *
     * @return array
     */
    public static function getPaymentButtonTitleDeferred()
    {
        return self::aggregateAllLanguagesCustomFields(PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE);
    }

    /**
     * Get custom title deferred button by id lang
     *
     * @param int $idLang
     *
     * @return string
     */
    public static function getPaymentButtonTitleDeferredByLang($idLang)
    {
        return self::getPaymentButtonTitleDeferred()[$idLang];
    }

    /**
     * Get array custom description deferred button
     *
     * @return array
     */
    public static function getPaymentButtonDescriptionDeferred()
    {
        return self::aggregateAllLanguagesCustomFields(PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC);
    }

    /**
     * Get custom description deferred button by id lang
     *
     * @param int $idLang
     *
     * @return string
     */
    public static function getPaymentButtonDescriptionDeferredByLang($idLang)
    {
        return self::getPaymentButtonDescriptionDeferred()[$idLang];
    }

    /**
     * Get array custom titles button
     *
     * @return array
     */
    public static function getPnxAirButtonTitle()
    {
        return self::aggregateAllLanguagesCustomFields(PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE);
    }

    /**
     * Get custom title button by id lang
     *
     * @param int $idLang
     *
     * @return string
     */
    public static function getPnxAirButtonTitleByLang($idLang)
    {
        return self::getPnxAirButtonTitle()[$idLang];
    }

    /**
     * Get array custom description button
     *
     * @return array
     */
    public static function getPnxAirButtonDescription()
    {
        return self::aggregateAllLanguagesCustomFields(PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_DESC);
    }

    /**
     * Get custom description button by id lang
     *
     * @param int $idLang
     *
     * @return string
     */
    public static function getPnxAirButtonDescriptionByLang($idLang)
    {
        return self::getPnxAirButtonDescription()[$idLang];
    }

    /**
     * Get array custom no eligible categories message
     *
     * @return array
     */
    public static function getNonEligibleCategoriesMessage()
    {
        return self::aggregateAllLanguagesCustomFields(ExcludedCategoryAdminFormBuilder::ALMA_NOT_ELIGIBLE_CATEGORIES);
    }

    /**
     * Get custom no eligible categories message by id lang
     *
     * @param int $idLang
     *
     * @return string
     */
    public static function getNonEligibleCategoriesMessageByLang($idLang)
    {
        return self::getNonEligibleCategoriesMessage()[$idLang];
    }

    /**
     * Get custom description payment trigger
     *
     * @return string
     */
    public static function getDescriptionPaymentTrigger()
    {
        $languages = Language::getLanguages(false);
        $defaultField = self::getAllLangCustomFieldByKeyConfig(PaymentOnTriggeringAdminFormBuilder::ALMA_DESCRIPTION_TRIGGER, $languages);

        foreach ($defaultField as $key => $field) {
            $return[$key] = $field['string'];
        }

        return $return;
    }

    /**
     * Get custom description payment trigger by id lang
     *
     * @param int $idLang
     *
     * @return string
     */
    public static function getDescriptionPaymentTriggerByLang($idLang)
    {
        return self::getDescriptionPaymentTrigger()[$idLang];
    }
}
