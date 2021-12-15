<?php
/**
 * 2018-2021 Alma SAS
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
 * @copyright 2018-2021 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Utils;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Language;

class SettingsCustomFields
{
    const ALMA_PAYMENT_BUTTON_TITLE = 'ALMA_PAYMENT_BUTTON_TITLE';
    const ALMA_PAYMENT_BUTTON_DESC = 'ALMA_PAYMENT_BUTTON_DESC';
    const ALMA_DEFERRED_BUTTON_TITLE = 'ALMA_DEFERRED_BUTTON_TITLE';
    const ALMA_DEFERRED_BUTTON_DESC = 'ALMA_DEFERRED_BUTTON_DESC';
    const ALMA_NOT_ELIGIBLE_CATEGORIES = 'ALMA_NOT_ELIGIBLE_CATEGORIES';

    /**
     * Init default custom fileds in ps_configuration table
     *
     * @return void
     */
    public static function initCustomFields()
    {
        $languages = Language::getLanguages(false);

        foreach (self::customFields() as $keyConfig => $string) {
            // phpcs:ignore
            Settings::updateValue($keyConfig, json_encode(self::getAllLangCustomFieldByKeyConfig($keyConfig, $languages)));
        }
    }

    /**
     * Default custom fields
     *
     * @return array
     */
    public static function customFields()
    {
        return [
            self::ALMA_PAYMENT_BUTTON_TITLE => 'Pay in %d installments',
            self::ALMA_PAYMENT_BUTTON_DESC => 'Pay in %d monthly installments with your credit card.',
            self::ALMA_DEFERRED_BUTTON_TITLE => 'Buy now Pay in %d days',
            self::ALMA_DEFERRED_BUTTON_DESC => 'Buy now pay in %d days with your credit card.',
            self::ALMA_NOT_ELIGIBLE_CATEGORIES => 'Your cart is not eligible for payments with Alma.',
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
                // phpcs:ignore
                'string' => LocaleHelper::getModuleTranslation(self::customFields()[$keyConfig], 'settings', $language['iso_code']),
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
                    // phpcs:ignore
                    $arrayFields[$lang['id_lang']] = LocaleHelper::getModuleTranslation(self::customFields()[$keyConfig], 'settings', $lang['iso_code']);
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
    public static function getPaymentButtonTitle()
    {
        return self::aggregateAllLanguagesCustomFields(self::ALMA_PAYMENT_BUTTON_TITLE);
    }

    /**
     * Get custom title button by id lang
     *
     * @param int $idLang
     *
     * @return string
     */
    public static function getPaymentButtonTitleByLang($idLang)
    {
        return self::getPaymentButtonTitle()[$idLang];
    }

    /**
     * Get array custom description button
     *
     * @return array
     */
    public static function getPaymentButtonDescription()
    {
        return self::aggregateAllLanguagesCustomFields(self::ALMA_PAYMENT_BUTTON_DESC);
    }

    /**
     * Get custom description button by id lang
     *
     * @param int $idLang
     *
     * @return string
     */
    public static function getPaymentButtonDescriptionByLang($idLang)
    {
        return self::getPaymentButtonDescription()[$idLang];
    }

    /**
     * Get array custom title deferred button
     *
     * @return array
     */
    public static function getPaymentButtonTitleDeferred()
    {
        return self::aggregateAllLanguagesCustomFields(self::ALMA_DEFERRED_BUTTON_TITLE);
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
        return self::aggregateAllLanguagesCustomFields(self::ALMA_DEFERRED_BUTTON_DESC);
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
     * Get array custom no eligible categories message
     *
     * @return array
     */
    public static function getNonEligibleCategoriesMessage()
    {
        return self::aggregateAllLanguagesCustomFields(self::ALMA_NOT_ELIGIBLE_CATEGORIES);
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
}
