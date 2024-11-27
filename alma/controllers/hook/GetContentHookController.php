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

namespace Alma\PrestaShop\Controllers\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\API\Entities\Merchant;
use Alma\API\Lib\IntegrationsConfigurationsUtils;
use Alma\API\RequestError;
use Alma\PrestaShop\Builders\Helpers\ApiHelperBuilder;
use Alma\PrestaShop\Builders\Helpers\ContextHelperBuilder;
use Alma\PrestaShop\Builders\Helpers\PriceHelperBuilder;
use Alma\PrestaShop\Builders\Helpers\SettingsHelperBuilder;
use Alma\PrestaShop\Builders\Helpers\ShareOfCheckoutHelperBuilder;
use Alma\PrestaShop\Exceptions\MissingParameterException;
use Alma\PrestaShop\Factories\ClientFactory;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Forms\ApiAdminFormBuilder;
use Alma\PrestaShop\Forms\ExcludedCategoryAdminFormBuilder;
use Alma\PrestaShop\Forms\InpageAdminFormBuilder;
use Alma\PrestaShop\Forms\PaymentButtonAdminFormBuilder;
use Alma\PrestaShop\Forms\ShareOfCheckoutAdminFormBuilder;
use Alma\PrestaShop\Helpers\ApiHelper;
use Alma\PrestaShop\Helpers\ApiKeyHelper;
use Alma\PrestaShop\Helpers\ClientHelper;
use Alma\PrestaShop\Helpers\CmsDataHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\ContextHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Hooks\AdminHookController;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Model\ClientModel;
use Alma\PrestaShop\Services\ConfigFormService;

final class GetContentHookController extends AdminHookController
{
    /**
     * @var ApiHelper
     */
    protected $apiHelper;

    /** @var ApiKeyHelper */
    private $apiKeyHelper;

    /** @var \Module */
    protected $module;

    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var bool
     */
    protected $hasKey;

    /**
     * @var ContextHelper
     */
    protected $contextHelper;

    /**
     * @var array
     */
    const KEY_CONFIG = [
        'ALMA_SHOW_ELIGIBILITY_MESSAGE' => [
            'action' => 'test_bool',
            'suffix' => '_ON',
        ],
        'ALMA_SHOW_PRODUCT_ELIGIBILITY' => [
            'action' => 'test_bool',
            'suffix' => '_ON',
        ],
        'ALMA_CART_WDGT_NOT_ELGBL' => [
            'action' => 'cast_bool',
            'suffix' => '_ON',
        ],
        'ALMA_PRODUCT_WDGT_NOT_ELGBL' => [
            'action' => 'cast_bool',
            'suffix' => '_ON',
        ],
        'ALMA_CATEGORIES_WDGT_NOT_ELGBL' => [
            'action' => 'cast_bool',
            'suffix' => '_ON',
        ],
        'ALMA_STATE_REFUND_ENABLED' => [
            'action' => 'cast_bool',
            'suffix' => '_ON',
        ],
        'ALMA_PAYMENT_ON_TRIGGERING_ENABLED' => [
            'action' => 'cast_bool',
            'suffix' => '_ON',
        ],
        InpageAdminFormBuilder::ALMA_ACTIVATE_INPAGE => [
            'action' => 'cast_bool',
            'suffix' => '_ON',
        ],
        InpageAdminFormBuilder::ALMA_INPAGE_PAYMENT_BUTTON_SELECTOR => 'none',
        InpageAdminFormBuilder::ALMA_INPAGE_PLACE_ORDER_BUTTON_SELECTOR => 'none',
        'ALMA_ACTIVATE_LOGGING' => [
            'action' => 'cast_bool',
            'suffix' => '_ON',
        ],
        'ALMA_WIDGET_POSITION_CUSTOM' => 'cast_bool',
        'ALMA_SHOW_DISABLED_BUTTON' => 'cast_bool',
        'ALMA_CART_WIDGET_POSITION_CUSTOM' => 'cast_bool',
        'ALMA_PRODUCT_PRICE_SELECTOR' => 'none',
        'ALMA_WIDGET_POSITION_SELECTOR' => 'none',
        'ALMA_PRODUCT_ATTR_SELECTOR' => 'none',
        'ALMA_PRODUCT_ATTR_RADIO_SELECTOR' => 'none',
        'ALMA_PRODUCT_COLOR_PICK_SELECTOR' => 'none',
        'ALMA_PRODUCT_QUANTITY_SELECTOR' => 'none',
        'ALMA_CART_WDGT_POS_SELECTOR' => 'none',
        'ALMA_STATE_REFUND' => 'none',
        'ALMA_STATE_TRIGGER' => 'none',
        'ALMA_DESCRIPTION_TRIGGER' => 'none',
    ];
    /**
     * @var \Alma\PrestaShop\Services\ConfigFormService
     */
    protected $configFormService;
    protected $clientFactory;

    /**
     * GetContentHook Controller construct.
     *
     * @codeCoverageIgnore
     */
    public function __construct($module)
    {
        $apiHelperBuilder = new ApiHelperBuilder();
        $this->apiHelper = $apiHelperBuilder->getInstance();

        $this->apiKeyHelper = new ApiKeyHelper();

        $settingsHelperBuilder = new SettingsHelperBuilder();
        $this->settingsHelper = $settingsHelperBuilder->getInstance();

        $priceHelperBuilder = new PriceHelperBuilder();
        $this->priceHelper = $priceHelperBuilder->getInstance();

        $contextHelperBuilder = new ContextHelperBuilder();
        $this->contextHelper = $contextHelperBuilder->getInstance();

        $contextFactory = new ContextFactory();

        $this->configFormService = new ConfigFormService(
            $module,
            $contextFactory->getContext()
        );

        $this->clientFactory = new ClientFactory();

        $this->hasKey = false;

        parent::__construct($module);
    }

    /**
     * @return array
     */
    protected function assignSmartyKeys()
    {
        $token = \Tools::getAdminTokenLite('AdminModules');
        $href = $this->context->link->getAdminLink('AdminParentModulesSf', $token);

        $assignSmartyKeys = [
            'hasPSAccounts' => false, // Dynamic content
            'updated' => false, // Dynamic content
            'suggestPSAccounts' => false, // Dynamic content
            'validation_error_classes' => 'alert alert-danger',
            'tip_classes' => 'alert alert-info',
            'success_classes' => 'alert alert-success',
            'breadcrumbs2' => [
                'container' => [
                    'name' => $this->module->l('Modules', 'GetContentHookController'),
                    'href' => $href,
                ],
                'tab' => [
                    'name' => $this->module->l('Module Manager', 'GetContentHookController'),
                    'href' => $href,
                ],
            ],
            'quick_access_current_link_name' => $this->module->l('Module Manager - List', 'GetContentHookController'),
            'quick_access_current_link_icon' => 'icon-AdminParentModulesSf',
            'token' => $token,
            'host_mode' => 0,
            'validation_error' => '', //Add error key
            'validation_message' => '', //Add error message
            'n' => 0, //Add installment value
            'deferred_days' => 0, //Add deferred days value
            'deferred_months' => 0, //Add deferred months value
            'min' => 0, //Add min purchase amount value
            'max' => 0, //Add max purchase amount value
            'hasKey' => false, //Return true if api key is set
            'tip' => 'fill_api_keys',
        ];

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $assignSmartyKeys['validation_error_classes'] = 'alert';
            $assignSmartyKeys['tip_classes'] = 'conf';
            $assignSmartyKeys['success_classes'] = 'conf';
        }

        return $assignSmartyKeys;
    }

    /**
     * Execute the controller to display the configuration form and save the config if submit value alma_config_form
     *
     * @param $params
     *
     * @return string
     *
     * @throws \Exception
     */
    public function run($params)
    {
        $assignSmartyKeys = $this->assignSmartyKeys();
        $messages = [];
        $feePlans = [];

        if (\Tools::isSubmit('alma_config_form')) {
            $messages = $this->processConfiguration();
        }

        /** @var \Alma\API\Client|null $almaClient */
        $almaClient = $this->clientFactory->get();
        if ($almaClient) {
            $clientModel = new ClientModel($almaClient);
            $feePlans = $clientModel->getMerchantFeePlans();
        }

        $assignSmartyKeys['form'] = $this->configFormService->getRenderHtml($feePlans);
        $assignSmartyKeys['error_messages'] = $messages;
        $this->context->smarty->assign($assignSmartyKeys);

        return $this->module->display($this->module->file, 'configurationContent.tpl');
    }

    /**
     * @@deprecated
     *
     * @return mixed|null
     *
     * @throws \Exception
     */
    public function processConfiguration()
    {
        if (!\Tools::isSubmit('alma_config_form')) {
            return null;
        }

        // Consider the plugin as fully configured only when everything goes well
        $this->updateSettingsValue('ALMA_FULLY_CONFIGURED', '0');

        $oldApiMode = SettingsHelper::getActiveMode();
        $apiMode = \Tools::getValue('ALMA_API_MODE');
        $this->updateSettingsValue('ALMA_API_MODE', $apiMode);

        // Get & check provided API keys
        $liveKey = trim(\Tools::getValue(ApiAdminFormBuilder::ALMA_LIVE_API_KEY));
        $testKey = trim(\Tools::getValue(ApiAdminFormBuilder::ALMA_TEST_API_KEY));

        if ((empty($liveKey) && ALMA_MODE_LIVE == $apiMode) || (empty($testKey) && ALMA_MODE_TEST == $apiMode)) {
            $this->context->smarty->assign('validation_error', "missing_key_for_{$apiMode}_mode");
            $this->context->smarty->assign([
                'suggestPSAccounts' => false,
            ]);

            $this->hasKey = false;

            return $this->module->display($this->module->file, 'getContent.tpl');
        }

        $credentialsError = null;

        if ((ConstantsHelper::OBSCURE_VALUE != $liveKey && ALMA_MODE_LIVE == $apiMode)
            || (ConstantsHelper::OBSCURE_VALUE != $testKey && ALMA_MODE_TEST == $apiMode)
        ) {
            $credentialsError = $this->credentialsError($liveKey, $testKey);
        }

        if ($credentialsError
            && array_key_exists('error', $credentialsError)
        ) {
            return $credentialsError['message'];
        }

        $shareOfCheckoutHelperBuilder = new ShareOfCheckoutHelperBuilder();
        $shareOfCheckoutHelper = $shareOfCheckoutHelperBuilder->getInstance();

        if ($liveKey !== SettingsHelper::getLiveKey()
            && ConstantsHelper::OBSCURE_VALUE !== $liveKey
        ) {
            $shareOfCheckoutHelper->resetShareOfCheckoutConsent();
        } else {
            // Prestashop FormBuilder adds `_ON` after name in the switch
            if (
                true === SettingsHelper::isShareOfCheckoutAnswered()
                && $oldApiMode === $apiMode
            ) {
                $shareOfCheckoutHelper->handleCheckoutConsent(
                    ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_STATE . '_ON'
                );
            }
        }

        // Down here, we know the provided API keys are correct (at least the one for the chosen API mode)
        $this->setKeyIfValueIsNotObscure($liveKey, ALMA_MODE_LIVE);
        $this->setKeyIfValueIsNotObscure($testKey, ALMA_MODE_TEST);

        // Try to get merchant from configured API key/mode
        try {
            $merchant = $this->apiHelper->getMerchant();
        } catch (\Exception $e) {
            $this->context->smarty->assign(
                [
                    'validation_error' => 'custom_error',
                    'validation_message' => $e->getMessage(),
                ]
            );
            Logger::instance()->error($e->getMessage());

            return $this->module->display($this->module->file, 'getContent.tpl');
        }

        if ($merchant) {
            // Save merchant API ID for widgets usage on frontend
            $this->updateSettingsValue('ALMA_MERCHANT_ID', $merchant->id);
        }

        $apiOnly = \Tools::getValue('_api_only');

        if ($apiOnly && $merchant) {
            $feePlans = $this->getFeePlans();
            foreach ($feePlans as $feePlan) {
                $n = $feePlan->installments_count;

                if (
                    3 == $n
                    && !$this->settingsHelper->isDeferred($feePlan)
                ) {
                    $key = $this->settingsHelper->keyForFeePlan($feePlan);
                    $almaPlans = [];
                    $almaPlans[$key]['enabled'] = 1;
                    $almaPlans[$key]['min'] = $feePlan->min_purchase_amount;
                    $almaPlans[$key]['max'] = $feePlan->max_purchase_amount;
                    $almaPlans[$key]['deferred_trigger_limit_days'] = $feePlan->deferred_trigger_limit_days;
                    $almaPlans[$key]['order'] = 1;
                    $this->updateSettingsValue('ALMA_FEE_PLANS', $almaPlans);
                    break;
                }
            }
        }

        if (!$apiOnly) {
            try {
                $this->saveCustomFieldsValues();
            } catch (MissingParameterException $e) {
                $this->context->smarty->assign('validation_error', 'missing_required_setting');
                Logger::instance()->error($e->getMessage());

                return $this->module->display($this->module->file, 'getContent.tpl');
            }

            $this->saveConfigValues();

            if ($merchant) {
                // First validate that plans boundaries are correctly set
                $feePlans = $this->getFeePlans();

                foreach ($feePlans as $feePlan) {
                    $n = $feePlan->installments_count;
                    $deferred_days = $feePlan->deferred_days;
                    $deferred_months = $feePlan->deferred_months;
                    $key = $this->settingsHelper->keyForFeePlan($feePlan);

                    if (1 != $n && $this->settingsHelper->isDeferred($feePlan)) {
                        continue;
                    }

                    $min = $this->priceHelper->convertPriceToCents((int) \Tools::getValue("ALMA_{$key}_MIN_AMOUNT"));
                    $max = $this->priceHelper->convertPriceToCents((int) \Tools::getValue("ALMA_{$key}_MAX_AMOUNT"));

                    $enablePlan = (bool) \Tools::getValue("ALMA_{$key}_ENABLED_ON");

                    if ($enablePlan
                        && !(
                            $min >= $feePlan->min_purchase_amount
                            && $min <= min($max, $feePlan->max_purchase_amount)
                        )
                    ) {
                        $this->context->smarty->assign([
                            'validation_error' => 'pnx_min_amount',
                            'n' => $n,
                            'deferred_days' => $deferred_days,
                            'deferred_months' => $deferred_months,
                            'min' => $this->priceHelper->convertPriceFromCents($feePlan->min_purchase_amount),
                            'max' => $this->priceHelper->convertPriceFromCents(min($max, $feePlan->max_purchase_amount)),
                        ]);

                        return $this->module->display($this->module->file, 'getContent.tpl');
                    }

                    if ($enablePlan
                        && !(
                            $max >= $min
                            && $max <= $feePlan->max_purchase_amount
                        )
                    ) {
                        $this->context->smarty->assign([
                            'validation_error' => 'pnx_max_amount',
                            'n' => $n,
                            'deferred_days' => $deferred_days,
                            'deferred_months' => $deferred_months,
                            'min' => $this->priceHelper->convertPriceFromCents($min),
                            'max' => $this->priceHelper->convertPriceFromCents($feePlan->max_purchase_amount),
                        ]);

                        return $this->module->display($this->module->file, 'getContent.tpl');
                    }
                }

                $almaPlans = [];
                $position = 1;

                foreach ($feePlans as $feePlan) {
                    $n = $feePlan->installments_count;
                    $key = $this->settingsHelper->keyForFeePlan($feePlan);

                    if (1 != $n && $this->settingsHelper->isDeferred($feePlan)) {
                        continue;
                    }

                    $min = (int) \Tools::getValue("ALMA_{$key}_MIN_AMOUNT");
                    $max = (int) \Tools::getValue("ALMA_{$key}_MAX_AMOUNT");
                    $order = (int) \Tools::getValue("ALMA_{$key}_SORT_ORDER");

                    // In case merchant inverted min & max values, correct it
                    if ($min > $max) {
                        $realMin = $max;
                        $max = $min;
                        $min = $realMin;
                    }

                    // in case of difference between sandbox and production feeplans
                    if (0 == $min
                        && 0 == $max
                        && 0 == $order
                    ) {
                        $almaPlans[$key]['enabled'] = '0';
                        $almaPlans[$key]['min'] = $feePlan->min_purchase_amount;
                        $almaPlans[$key]['max'] = $feePlan->max_purchase_amount;
                        $almaPlans[$key]['deferred_trigger_limit_days'] = $feePlan->deferred_trigger_limit_days;
                        $almaPlans[$key]['order'] = (int) $position;
                        ++$position;
                    } else {
                        $enablePlan = (bool) \Tools::getValue("ALMA_{$key}_ENABLED_ON");
                        $almaPlans[$key]['enabled'] = $enablePlan ? '1' : '0';
                        $almaPlans[$key]['min'] = $this->priceHelper->convertPriceToCents($min);
                        $almaPlans[$key]['max'] = $this->priceHelper->convertPriceToCents($max);
                        $almaPlans[$key]['deferred_trigger_limit_days'] = $feePlan->deferred_trigger_limit_days;
                        $almaPlans[$key]['order'] = (int) \Tools::getValue("ALMA_{$key}_SORT_ORDER");
                    }
                }

                $this->updateSettingsValue('ALMA_FEE_PLANS', $almaPlans);
            }
        }

        // At this point, consider things are sufficiently configured to be usable
        $this->updateSettingsValue('ALMA_FULLY_CONFIGURED', '1');

        if ($credentialsError
            && array_key_exists('warning', $credentialsError)
        ) {
            return $credentialsError['message'];
        }

        if (IntegrationsConfigurationsUtils::isUrlRefreshRequired($this->settingsHelper->getKey(CmsDataHelper::ALMA_CMSDATA_DATE))) {
            $this->apiHelper->sendUrlForGatherCmsData($this->contextHelper->getModuleLink('cmsdataexport', [], true));
            $this->settingsHelper->updateKey(CmsDataHelper::ALMA_CMSDATA_DATE, time());
        }

        $this->context->smarty->clearAssign('validation_error');

        return $this->module->display($this->module->file, 'getContent.tpl');
    }

    /**
     * Check if Api key are obscure.
     *
     * @param string $apiKey
     * @param string $mode
     *
     * @return void
     */
    private function setKeyIfValueIsNotObscure($apiKey, $mode)
    {
        if (ConstantsHelper::OBSCURE_VALUE === $apiKey) {
            return;
        }

        if (ALMA_MODE_LIVE === $mode) {
            $this->apiKeyHelper->setLiveApiKey($apiKey);
        } else {
            $this->apiKeyHelper->setTestApiKey($apiKey);
        }
    }

    /**
     * @param $liveKey
     * @param $testKey
     *
     * @return array|null
     */
    private function credentialsError($liveKey, $testKey)
    {
        $modes = [ALMA_MODE_TEST, ALMA_MODE_LIVE];

        foreach ($modes as $mode) {
            $key = (ALMA_MODE_LIVE == $mode ? $liveKey : $testKey);
            if (
                !$key
                || ConstantsHelper::OBSCURE_VALUE === $key
                || SettingsHelper::getActiveMode() !== $mode
            ) {
                continue;
            }

            $alma = ClientHelper::createInstance($key, $mode);
            if (!$alma) {
                $this->context->smarty->assign('validation_error', 'alma_client_null');

                $errorMessage = $this->module->display($this->module->file, 'getContent.tpl');

                return ['error' => true, 'message' => $errorMessage];
            }

            // Try to get merchant from configured API key/mode
            try {
                $this->apiHelper->getMerchant($alma);
            } catch (\Exception $e) {
                $this->context->smarty->assign(
                    [
                        'validation_error' => 'custom_error',
                        'validation_message' => $e->getMessage(),
                    ]
                );
                Logger::instance()->error($e->getMessage());

                $errorMessage = $this->module->display($this->module->file, 'getContent.tpl');

                return ['error' => true, 'message' => $errorMessage];
            }
        }

        return null;
    }

    /**
     * @return array|null
     */
    private function getFeePlans()
    {
        $alma = ClientHelper::defaultInstance();

        if (!$alma) {
            return null;
        }

        try {
            return (array) $alma->merchants->feePlans('general', 'all', true);
        } catch (RequestError $e) {
            return null;
        }
    }

    /**
     * @param int $languageId
     * @param string $locale
     * @param string $keyForm
     *
     * @return array
     *
     * @throws MissingParameterException
     */
    protected function getLocaleAndString($languageId, $locale, $keyForm)
    {
        $result = [
            'locale' => $locale,
            'string' => \Tools::getValue(sprintf('%s_%s', $keyForm, $languageId)),
        ];

        if (empty($result['string'])) {
            throw new MissingParameterException($locale, $keyForm, $languageId);
        }

        return $result;
    }

    /**
     * @param array $languages
     *
     * @return void
     *
     * @throws MissingParameterException
     */
    protected function saveCustomFieldsValues()
    {
        // Get languages are active
        $languages = $this->context->controller->getLanguages();

        $titlesPayNow = $titles = $titlesDeferred = $titlesCredit = $descriptionsPayNow = $descriptions = $descriptionsDeferred = $descriptionsCredit = $nonEligibleCategoriesMsg = [];

        foreach ($languages as $language) {
            $locale = $language['iso_code'];
            $languageId = $language['id_lang'];

            if (array_key_exists('locale', $language)) {
                $locale = $language['locale'];
            }

            $titlesPayNow[$languageId] = $this->getLocaleAndString(
                $languageId,
                $locale,
                PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_TITLE
            );
            $titles[$languageId] = $this->getLocaleAndString($languageId, $locale, PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE);
            $titlesDeferred[$languageId] = $this->getLocaleAndString($languageId, $locale, PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE);
            $titlesCredit[$languageId] = $this->getLocaleAndString($languageId, $locale, PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE);
            $descriptionsPayNow[$languageId] = $this->getLocaleAndString($languageId, $locale, PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_DESC);
            $descriptions[$languageId] = $this->getLocaleAndString($languageId, $locale, PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC);
            $descriptionsDeferred[$languageId] = $this->getLocaleAndString($languageId, $locale, PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC);
            $descriptionsCredit[$languageId] = $this->getLocaleAndString($languageId, $locale, PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_DESC);
            $nonEligibleCategoriesMsg[$languageId] = $this->getLocaleAndString($languageId, $locale, ExcludedCategoryAdminFormBuilder::ALMA_NOT_ELIGIBLE_CATEGORIES);
        }

        $this->updateSettingsValue(PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE, $titles);
        $this->updateSettingsValue(PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE, $titlesDeferred);
        $this->updateSettingsValue(PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE, $titlesCredit);
        $this->updateSettingsValue(PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_TITLE, $titlesPayNow);
        $this->updateSettingsValue(PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC, $descriptions);
        $this->updateSettingsValue(PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC, $descriptionsDeferred);
        $this->updateSettingsValue(PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_DESC, $descriptionsCredit);
        $this->updateSettingsValue(PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_DESC, $descriptionsPayNow);
        $this->updateSettingsValue('ALMA_NOT_ELIGIBLE_CATEGORIES', $nonEligibleCategoriesMsg);
    }

    /**
     * @param string $configKey
     * @param array|string $value
     *
     * @return void
     */
    protected function updateSettingsValue($configKey, $value)
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        SettingsHelper::updateValue($configKey, $value);
    }

    /**
     * @return void
     */
    protected function saveConfigValues()
    {
        foreach (self::KEY_CONFIG as $key => $conditions) {
            $type = $conditions;
            $searchKey = $key;

            if (is_array($conditions)) {
                $searchKey = $key . $conditions['suffix'];
                $type = $conditions['action'];
            }

            $value = \Tools::getValue($searchKey);

            switch ($type) {
                case 'test_bool':
                    $value = $value ? '1' : '0';
                    break;
                case 'cast_bool':
                    $value = (bool) $value;
                    break;
                default:
                    break;
            }

            $this->updateSettingsValue($key, $value);
        }
    }
}
