<?php
/**
 * 2018-2024 Alma SAS.
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
 * @copyright 2018-2024 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Services;

use Alma\API\Entities\Merchant;
use Alma\PrestaShop\Builders\Helpers\CustomFieldHelperBuilder;
use Alma\PrestaShop\Builders\Helpers\PriceHelperBuilder;
use Alma\PrestaShop\Builders\Models\MediaHelperBuilder;
use Alma\PrestaShop\Factories\ClientFactory;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Forms\ApiAdminFormBuilder;
use Alma\PrestaShop\Forms\CartEligibilityAdminFormBuilder;
use Alma\PrestaShop\Forms\DebugAdminFormBuilder;
use Alma\PrestaShop\Forms\ExcludedCategoryAdminFormBuilder;
use Alma\PrestaShop\Forms\InpageAdminFormBuilder;
use Alma\PrestaShop\Forms\PaymentButtonAdminFormBuilder;
use Alma\PrestaShop\Forms\PaymentOnTriggeringAdminFormBuilder;
use Alma\PrestaShop\Forms\PnxAdminFormBuilder;
use Alma\PrestaShop\Forms\ProductEligibilityAdminFormBuilder;
use Alma\PrestaShop\Forms\RefundAdminFormBuilder;
use Alma\PrestaShop\Forms\ShareOfCheckoutAdminFormBuilder;
use Alma\PrestaShop\Helpers\CustomFieldsHelper;
use Alma\PrestaShop\Helpers\MediaHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Model\ClientModel;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ConfigFormService
{
    /**
     * @var \Module
     */
    private $module;
    /**
     * @var \Context
     */
    private $context;
    /**
     * @var CustomFieldsHelper
     */
    private $customFieldsHelper;
    /**
     * @var SettingsHelper
     */
    private $settingsHelper;
    /**
     * @var ClientFactory
     */
    private $clientFactory;
    /**
     * @var MediaHelper
     */
    private $mediaHelper;
    /**
     * @var PriceHelper
     */
    private $priceHelper;

    public function __construct(
        $module,
        $settingsHelper,
        $context = null,
        $customFieldsHelper = null,
        $clientFactory = null,
        $mediaHelper = null,
        $priceHelper = null
    ) {
        $this->module = $module;
        $this->settingsHelper = $settingsHelper;
        if (!$context) {
            $context = new ContextFactory();
        }
        $this->context = $context->getContext();
        if (!$customFieldsHelper) {
            $customFieldsHelper = (new CustomFieldHelperBuilder())->getInstance();
        }
        $this->customFieldsHelper = $customFieldsHelper;
        if (!$clientFactory) {
            $clientFactory = new ClientFactory();
        }
        $this->clientFactory = $clientFactory;
        if (!$mediaHelper) {
            $mediaHelper = (new MediaHelperBuilder())->getInstance();
        }
        $this->mediaHelper = $mediaHelper;
        if (!$priceHelper) {
            $priceHelper = (new PriceHelperBuilder())->getInstance();
        }
        $this->priceHelper = $priceHelper;
    }

    /**
     * @return \HelperForm
     */
    public function getHelperForm()
    {
        $helper = new \HelperForm();
        $helper->module = $this->module;
        $helper->table = 'alma_config';
        $helper->default_form_language = (int) \Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) \Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->submit_action = 'alma_config_form';

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $helper->base_folder = 'helpers/form/15/';
            $this->context->controller->addCss(_MODULE_DIR_ . $this->module->name . '/views/css/admin/tabs.css');
        }
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->module->name
            . '&tab_module=' . $this->module->tab
            . '&module_name=' . $this->module->name;

        $helper->token = \Tools::getAdminTokenLite('AdminModules');
        $helper->fields_value = $this->getFieldsValueForForm();
        $helper->languages = $this->context->controller->getLanguages();

        return $helper;
    }

    public function getRenderHtml()
    {
        /** @var \Alma\API\Client|null $almaClient */
        $almaClient = $this->clientFactory->get();
        $feePlans = [];

        if ($almaClient) {
            $clientModel = new ClientModel($almaClient);
            $merchant = $clientModel->getMerchantMe();
            $feePlans = $clientModel->getMerchantFeePlans();

            $formFields = [];

            if (!$this->needsAPIKey()) {
                $feePlansOrdered = $this->getPlansForForms($feePlans, $merchant);
                $formFields[] = $this->getPnxAdminFormBuilder($feePlansOrdered);
                $formFields[] = $this->getProductEligibilityAdminForm();
                $formFields[] = $this->getCartEligibilityAdminForm();
                $formFields[] = $this->getPaymentButtonAdminForm();
                $formFields[] = $this->getExcludedCategoryAdminForm();
                $formFields[] = $this->getRefundAdminForm();
                if (!SettingsHelper::shouldHideShareOfCheckoutForm()) {
                    $formFields[] = $this->getShareOfCheckoutAdminForm();
                }
                $formFields[] = $this->getInpageAdminForm();
                if ($this->settingsHelper->isPaymentTriggerEnabledByState()) {
                    $formFields[] = $this->getPaymentOnTriggeringAdminForm();
                }
            }
        }
        $formFields[] = $this->getApiAdminFormBuilder($this->needsAPIKey());
        $formFields[] = $this->getDebugAdminFormBuilder();

        $helperForm = $this->getHelperForm();

        if ($feePlans) {
            $helperForm->fields_value = array_merge($helperForm->fields_value, $this->getFieldsValueFromFeePlans($feePlans));
        }

        return $helperForm->generateForm($formFields);
    }

    /**
     * @param array|null $feePlans
     * @param Merchant|null $merchant
     *
     * @return array
     */
    protected function getPlansForForms($feePlans, $merchant = null)
    {
        $feePlansOrdered = [];

        if ($merchant) {
            // sort fee plans by pnx then by pay later duration
            $feePlanDeferred = [];

            foreach ($feePlans as $feePlan) {
                if (!$this->settingsHelper->isDeferred($feePlan)) {
                    $feePlansOrdered[$feePlan->installments_count] = $feePlan;
                    continue;
                }

                $duration = $this->settingsHelper->getDuration($feePlan);
                $feePlanDeferred[$feePlan->installments_count . $duration] = $feePlan;
            }

            ksort($feePlanDeferred);
            $feePlansOrdered = array_merge($feePlansOrdered, $feePlanDeferred);
        }

        return $feePlansOrdered;
    }

    /**
     * @param array $feePlans
     *
     * @return array
     */
    public function getFieldsValueFromFeePlans($feePlans)
    {
        $installmentsPlans = json_decode(SettingsHelper::getFeePlans());
        $fieldsValue = [];
        $sortOrder = 1;
        foreach ($feePlans as $feePlan) {
            $key = $this->settingsHelper->keyForFeePlan($feePlan);

            $fieldsValue["ALMA_{$key}_ENABLED_ON"] = isset($installmentsPlans->$key->enabled)
                ? $installmentsPlans->$key->enabled
                : 0;

            $minAmount = isset($installmentsPlans->$key->min)
                ? $installmentsPlans->$key->min
                : $feePlan->min_purchase_amount;

            $fieldsValue["ALMA_{$key}_MIN_AMOUNT"] = (int) round(
                $this->priceHelper->convertPriceFromCents($minAmount)
            );
            $maxAmount = isset($installmentsPlans->$key->max)
                ? $installmentsPlans->$key->max
                : $feePlan->max_purchase_amount;

            $fieldsValue["ALMA_{$key}_MAX_AMOUNT"] = (int) $this->priceHelper->convertPriceFromCents($maxAmount);

            $order = isset($installmentsPlans->$key->order)
                ? $installmentsPlans->$key->order
                : $sortOrder;

            $fieldsValue["ALMA_{$key}_SORT_ORDER"] = $order;

            ++$sortOrder;
        }

        return $fieldsValue;
    }

    /**
     * @return array
     */
    protected function getFieldsValueForForm()
    {
        return [
            'ALMA_LIVE_API_KEY' => SettingsHelper::getLiveKey(),
            'ALMA_TEST_API_KEY' => SettingsHelper::getTestKey(),
            'ALMA_API_MODE' => SettingsHelper::getActiveMode(),
            PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_TITLE => $this->customFieldsHelper->getValue(
                PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_TITLE
            ),
            PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_DESC => $this->customFieldsHelper->getValue(
                PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_DESC
            ),
            PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE => $this->customFieldsHelper->getValue(
                PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE
            ),
            PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC => $this->customFieldsHelper->getValue(
                PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC
            ),
            PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE => $this->customFieldsHelper->getValue(
                PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE
            ),
            PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC => $this->customFieldsHelper->getValue(
                PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC
            ),
            PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE => $this->customFieldsHelper->getValue(
                PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE
            ),
            PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_DESC => $this->customFieldsHelper->getValue(
                PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_DESC
            ),
            InpageAdminFormBuilder::ALMA_ACTIVATE_INPAGE . '_ON' => $this->settingsHelper->isInPageEnabled(),
            InpageAdminFormBuilder::ALMA_INPAGE_PAYMENT_BUTTON_SELECTOR => $this->settingsHelper->getKey(InpageAdminFormBuilder::ALMA_INPAGE_PAYMENT_BUTTON_SELECTOR, InpageAdminFormBuilder::ALMA_INPAGE_DEFAULT_VALUE_PAYMENT_BUTTON_SELECTOR),
            InpageAdminFormBuilder::ALMA_INPAGE_PLACE_ORDER_BUTTON_SELECTOR => $this->settingsHelper->getKey(InpageAdminFormBuilder::ALMA_INPAGE_PLACE_ORDER_BUTTON_SELECTOR, InpageAdminFormBuilder::ALMA_INPAGE_DEFAULT_VALUE_PLACE_ORDER_BUTTON_SELECTOR),
            'ALMA_SHOW_DISABLED_BUTTON' => SettingsHelper::showDisabledButton(),
            'ALMA_SHOW_ELIGIBILITY_MESSAGE_ON' => SettingsHelper::showEligibilityMessage(),
            'ALMA_CART_WDGT_NOT_ELGBL_ON' => SettingsHelper::showCartWidgetIfNotEligible(),
            'ALMA_PRODUCT_WDGT_NOT_ELGBL_ON' => SettingsHelper::showProductWidgetIfNotEligible(),
            'ALMA_CATEGORIES_WDGT_NOT_ELGBL_ON' => SettingsHelper::showCategoriesWidgetIfNotEligible(),
            'ALMA_ACTIVATE_LOGGING_ON' => (bool) SettingsHelper::canLog(),
            'ALMA_SHARE_OF_CHECKOUT_STATE_ON' => SettingsHelper::getShareOfCheckoutStatus(),
            'ALMA_SHARE_OF_CHECKOUT_DATE' => SettingsHelper::getCurrentTimestamp(),
            'ALMA_STATE_REFUND' => SettingsHelper::getRefundState(),
            'ALMA_STATE_REFUND_ENABLED_ON' => SettingsHelper::isRefundEnabledByState(),
            'ALMA_STATE_TRIGGER' => SettingsHelper::getPaymentTriggerState(),
            'ALMA_PAYMENT_ON_TRIGGERING_ENABLED_ON' => $this->settingsHelper->isPaymentTriggerEnabledByState(),
            'ALMA_DESCRIPTION_TRIGGER' => SettingsHelper::getKeyDescriptionPaymentTrigger(),
            'ALMA_NOT_ELIGIBLE_CATEGORIES' => $this->customFieldsHelper->getValue(
                ExcludedCategoryAdminFormBuilder::ALMA_NOT_ELIGIBLE_CATEGORIES
            ),
            'ALMA_SHOW_PRODUCT_ELIGIBILITY_ON' => SettingsHelper::showProductEligibility(),
            'ALMA_PRODUCT_PRICE_SELECTOR' => SettingsHelper::getProductPriceQuerySelector(),
            'ALMA_WIDGET_POSITION_SELECTOR' => SettingsHelper::getProductWidgetPositionQuerySelector(),
            'ALMA_WIDGET_POSITION_CUSTOM' => SettingsHelper::isWidgetCustomPosition(),
            'ALMA_CART_WDGT_POS_SELECTOR' => SettingsHelper::getCartWidgetPositionQuerySelector(),
            'ALMA_CART_WIDGET_POSITION_CUSTOM' => SettingsHelper::isCartWidgetCustomPosition(),
            'ALMA_PRODUCT_ATTR_SELECTOR' => SettingsHelper::getProductAttrQuerySelector(),
            'ALMA_PRODUCT_ATTR_RADIO_SELECTOR' => SettingsHelper::getProductAttrRadioQuerySelector(),
            'ALMA_PRODUCT_COLOR_PICK_SELECTOR' => SettingsHelper::getProductColorPickQuerySelector(),
            'ALMA_PRODUCT_QUANTITY_SELECTOR' => SettingsHelper::getProductQuantityQuerySelector(),
            '_api_only' => true,
        ];
    }

    /**
     * @param $feePlansOrdered
     *
     * @return array|array[]
     */
    protected function getPnxAdminFormBuilder($feePlansOrdered)
    {
        $installmentsPlans = json_decode(SettingsHelper::getFeePlans());

        $pnxBuilder = new PnxAdminFormBuilder(
            $this->module,
            $this->context,
            $this->mediaHelper->getIconPathAlmaTiny(),
            ['feePlans' => $feePlansOrdered, 'installmentsPlans' => $installmentsPlans]
        );

        return $pnxBuilder->build();
    }

    /**
     * @return array
     */
    protected function getProductEligibilityAdminForm()
    {
        $productBuilder = new ProductEligibilityAdminFormBuilder($this->module, $this->context, $this->mediaHelper->getIconPathAlmaTiny());

        return $productBuilder->build();
    }

    /**
     * @return array|array[]
     */
    protected function getCartEligibilityAdminForm()
    {
        $cartBuilder = new CartEligibilityAdminFormBuilder($this->module, $this->context, $this->mediaHelper->getIconPathAlmaTiny());

        return $cartBuilder->build();
    }

    /**
     * @return array|array[]
     */
    protected function getPaymentButtonAdminForm()
    {
        $paymentBuilder = new PaymentButtonAdminFormBuilder($this->module, $this->context, $this->mediaHelper->getIconPathAlmaTiny());

        return $paymentBuilder->build();
    }

    /**
     * @return array|array[]
     */
    protected function getExcludedCategoryAdminForm()
    {
        $excludedBuilder = new ExcludedCategoryAdminFormBuilder($this->module, $this->context, $this->mediaHelper->getIconPathAlmaTiny());

        return $excludedBuilder->build();
    }

    /**
     * @return array|array[]
     */
    protected function getRefundAdminForm()
    {
        $refundBuilder = new RefundAdminFormBuilder($this->module, $this->context, $this->mediaHelper->getIconPathAlmaTiny());

        return $refundBuilder->build();
    }

    /**
     * @return array|array[]
     */
    protected function getShareOfCheckoutAdminForm()
    {
        $shareOfCheckoutBuilder = new ShareOfCheckoutAdminFormBuilder($this->module, $this->context, $this->mediaHelper->getIconPathAlmaTiny());

        return $shareOfCheckoutBuilder->build();
    }

    /**
     * @return array|array[]
     */
    protected function getInpageAdminForm()
    {
        $inpageBuilder = new InpageAdminFormBuilder($this->module, $this->context, $this->mediaHelper->getIconPathAlmaTiny());

        return $inpageBuilder->build();
    }

    /**
     * @return array|array[]
     */
    protected function getPaymentOnTriggeringAdminForm()
    {
        $triggerBuilder = new PaymentOnTriggeringAdminFormBuilder($this->module, $this->context, $this->mediaHelper->getIconPathAlmaTiny());

        return $triggerBuilder->build();
    }

    /**
     * @param bool $needsKeys
     *
     * @return array|array[]
     */
    protected function getApiAdminFormBuilder($needsKeys = false)
    {
        $apiBuilder = new ApiAdminFormBuilder($this->module, $this->context, $this->mediaHelper->getIconPathAlmaTiny(), ['needsAPIKey' => $needsKeys]);

        return $apiBuilder->build();
    }

    /**
     * @return array|array[]
     */
    protected function getDebugAdminFormBuilder()
    {
        $debugBuilder = new DebugAdminFormBuilder($this->module, $this->context, $this->mediaHelper->getIconPathAlmaTiny());

        return $debugBuilder->build();
    }

    /**
     * @return bool
     */
    protected function needsAPIKey()
    {
        $key = trim(SettingsHelper::getActiveAPIKey());

        return '' == $key || null == $key;
    }
}
