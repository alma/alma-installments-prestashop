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

use Alma\PrestaShop\Forms\ExcludedCategoryAdminFormBuilder;
use Alma\PrestaShop\Forms\PaymentButtonAdminFormBuilder;
use Alma\PrestaShop\Forms\PaymentOnTriggeringAdminFormBuilder;
use Language;

/**
 * Class SettingsCustomFields
 */
class SettingsCustomFields
{
    /**
     * Get array custom titles button
     *
     * @return array
     */
    public static function getPnxButtonTitle()
    {
        return CustomFieldsHelper::aggregateAllLanguagesCustomFields(PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE);
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
        if (!array_key_exists($idLang, self::getPnxButtonTitle())) {
            return CustomFieldsHelper::customFields()[PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE];
        }

        return self::getPnxButtonTitle()[$idLang];
    }

    /**
     * Get array custom description button
     *
     * @return array
     */
    public static function getPnxButtonDescription()
    {
        return CustomFieldsHelper::aggregateAllLanguagesCustomFields(PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC);
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
        if (!array_key_exists($idLang, self::getPnxButtonDescription())) {
            return CustomFieldsHelper::customFields()[PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC];
        }

        return self::getPnxButtonDescription()[$idLang];
    }

    /**
     * Get array custom title deferred button
     *
     * @return array
     */
    public static function getPaymentButtonTitleDeferred()
    {
        return CustomFieldsHelper::aggregateAllLanguagesCustomFields(PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE);
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
        if (!array_key_exists($idLang, self::getPaymentButtonTitleDeferred())) {
            return CustomFieldsHelper::customFields()[PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE];
        }

        return self::getPaymentButtonTitleDeferred()[$idLang];
    }

    /**
     * Get array custom description deferred button
     *
     * @return array
     */
    public static function getPaymentButtonDescriptionDeferred()
    {
        return CustomFieldsHelper::aggregateAllLanguagesCustomFields(PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC);
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
        if (!array_key_exists($idLang, self::getPaymentButtonDescriptionDeferred())) {
            return CustomFieldsHelper::customFields()[PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC];
        }

        return self::getPaymentButtonDescriptionDeferred()[$idLang];
    }

    /**
     * Get array custom titles button
     *
     * @return array
     */
    public static function getPnxAirButtonTitle()
    {
        return CustomFieldsHelper::aggregateAllLanguagesCustomFields(PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE);
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
        if (!array_key_exists($idLang, self::getPnxAirButtonTitle())) {
            return CustomFieldsHelper::customFields()[PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE];
        }

        return self::getPnxAirButtonTitle()[$idLang];
    }

    /**
     * Get array custom description button
     *
     * @return array
     */
    public static function getPnxAirButtonDescription()
    {
        return CustomFieldsHelper::aggregateAllLanguagesCustomFields(PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_DESC);
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
        if (!array_key_exists($idLang, self::getPnxAirButtonDescription())) {
            return CustomFieldsHelper::customFields()[PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_DESC];
        }

        return self::getPnxAirButtonDescription()[$idLang];
    }

    /**
     * Get array custom no eligible categories message
     *
     * @return array
     */
    public static function getNonEligibleCategoriesMessage()
    {
        return CustomFieldsHelper::aggregateAllLanguagesCustomFields(ExcludedCategoryAdminFormBuilder::ALMA_NOT_ELIGIBLE_CATEGORIES);
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
        if (!array_key_exists($idLang, self::getNonEligibleCategoriesMessage())) {
            return CustomFieldsHelper::customFields()[ExcludedCategoryAdminFormBuilder::ALMA_NOT_ELIGIBLE_CATEGORIES];
        }

        return self::getNonEligibleCategoriesMessage()[$idLang];
    }

    /**
     * Get custom description payment trigger
     *
     * @return array
     */
    public static function getDescriptionPaymentTrigger()
    {
        $languages = Language::getLanguages(false);
        $defaultField = CustomFieldsHelper::getAllLangCustomFieldByKeyConfig(PaymentOnTriggeringAdminFormBuilder::ALMA_DESCRIPTION_TRIGGER, $languages);

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
        if (!array_key_exists($idLang, self::getDescriptionPaymentTrigger())) {
            return CustomFieldsHelper::customFields()[PaymentOnTriggeringAdminFormBuilder::ALMA_DESCRIPTION_TRIGGER];
        }

        return self::getDescriptionPaymentTrigger()[$idLang];
    }
}
