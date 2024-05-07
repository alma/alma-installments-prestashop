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

use Alma\PrestaShop\Factories\ModuleFactory;
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
     * @var LanguageHelper
     */
    protected $languageHelper;

    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    /**
     * @var LocaleHelper
     */
    protected $localeHelper;

    /**
     * @var ModuleFactory
     */
    protected $moduleFactory;

    /**
     * @param LanguageHelper $languageHelper
     * @param LocaleHelper $localeHelper
     * @param SettingsHelper $settingsHelper
     * @param ModuleFactory $moduleFactory
     */
    public function __construct($languageHelper, $localeHelper, $settingsHelper, $moduleFactory)
    {
        $this->languageHelper = $languageHelper;
        $this->settingsHelper = $settingsHelper;
        $this->localeHelper = $localeHelper;
        $this->moduleFactory = $moduleFactory;
    }

    /**
     * Init default custom fields in ps_configuration table
     *
     * @return void
     */
    public function initCustomFields()
    {
        $languages = $this->languageHelper->getLanguages(false);

        $keysCustomFields = array_keys($this->customFields());

        foreach ($keysCustomFields as $keyCustomFields) {
            $this->settingsHelper->updateKey(
                $keyCustomFields,
                json_encode($this->getAllLangCustomFieldByKeyConfig($keyCustomFields, $languages))
            );
        }
    }

    /**
     * Default custom fields
     *
     * @return array
     */
    public function customFields()
    {
        $textPnxButtonTitle = 'Pay in %d installments';
        $textButtonDescription = 'Fast and secure payment by credit card.';

        $this->moduleFactory->l('Pay now by credit card', ConstantsHelper::SOURCE_CUSTOM_FIELDS);
        $this->moduleFactory->l('Pay in %d installments', ConstantsHelper::SOURCE_CUSTOM_FIELDS);
        $this->moduleFactory->l('Buy now Pay in %d days', ConstantsHelper::SOURCE_CUSTOM_FIELDS);
        $this->moduleFactory->l('Fast and secure payment by credit card.', ConstantsHelper::SOURCE_CUSTOM_FIELDS);
        $this->moduleFactory->l('Fast and secure payments.', ConstantsHelper::SOURCE_CUSTOM_FIELDS);
        $this->moduleFactory->l('Your cart is not eligible for payments with Alma.', ConstantsHelper::SOURCE_CUSTOM_FIELDS);
        $this->moduleFactory->l('At shipping', ConstantsHelper::SOURCE_CUSTOM_FIELDS);

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
    public function getAllLangCustomFieldByKeyConfig($keyConfig, $languages)
    {
        $result = [];

        foreach ($languages as $language) {
            $result[$language['id_lang']] = [
                'locale' => $language['iso_code'],
                'string' => $this->localeHelper->getModuleTranslation(
                    $this->customFields()[$keyConfig],
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
    public function getCustomFieldByKeyConfig($keyConfig, $languages)
    {
        $defaultField = $this->getAllLangCustomFieldByKeyConfig($keyConfig, $languages);
        $result = [];

        $allLangField = json_decode(
            $this->settingsHelper->getKey($keyConfig, json_encode($defaultField)),
            true
        );

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
    public function getValue($keyConfig)
    {
        $languages = $this->languageHelper->getLanguages(false);

        $arrayFields = $this->getCustomFieldByKeyConfig($keyConfig, $languages);

        if (count($arrayFields) < count($languages)) {
            foreach ($languages as $lang) {
                if (!array_key_exists($lang['id_lang'], $arrayFields)) {
                    $arrayFields[$lang['id_lang']] = $this->localeHelper->getModuleTranslation(
                        $this->customFields()[$keyConfig],
                        ConstantsHelper::SOURCE_CUSTOM_FIELDS,
                        $lang['iso_code']
                    );
                }
            }
        }

        return $arrayFields;
    }

    /**
     * @param int $idLang
     * @param string$key
     *
     * @return mixed|string
     */
    public function getBtnValueByLang($idLang, $key)
    {
        $result = $this->getValue($key);

        if (!array_key_exists($idLang, $result)) {
            return $this->customFields()[$key];
        }

        return $result[$idLang];
    }

    /**
     * Get custom description payment trigger
     *
     * @return array
     */
    public function getDescriptionPaymentTrigger()
    {
        $languages = $this->languageHelper->getLanguages(false);

        $defaultField = $this->getAllLangCustomFieldByKeyConfig(
            PaymentOnTriggeringAdminFormBuilder::ALMA_DESCRIPTION_TRIGGER, $languages
        );

        $return = array();

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
    public function getDescriptionPaymentTriggerByLang($idLang)
    {
        $arrayDescriptionPaymentTriggerByLang = $this->getDescriptionPaymentTrigger();

        if (!array_key_exists($idLang, $arrayDescriptionPaymentTriggerByLang)) {
            return $this->customFields()[PaymentOnTriggeringAdminFormBuilder::ALMA_DESCRIPTION_TRIGGER];
        }

        return $arrayDescriptionPaymentTriggerByLang[$idLang];
    }

    /**
     * @param int $languageId
     * @param string $key
     * @param int $installments
     *
     * @return string
     */
    public function getTextButton($languageId, $key, $installments)
    {
        return sprintf(
            $this->getBtnValueByLang(
                $languageId,
                $key
            ),
            $installments
        );
    }
}
