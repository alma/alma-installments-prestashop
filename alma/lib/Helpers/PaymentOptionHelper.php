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

namespace Alma\PrestaShop\Helpers;

use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\MediaFactory;
use Alma\PrestaShop\Factories\ModuleFactory;
use Alma\PrestaShop\Forms\PaymentButtonAdminFormBuilder;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class PaymentOptionHelper.
 */
class PaymentOptionHelper
{
    /**
     * @var CustomFieldsHelper
     */
    protected $customFieldsHelper;

    protected $module;

    /**
     * @var \Context
     */
    protected $context;

    /**
     * @var MediaHelper
     */
    protected $mediaHelper;

    /**
     * @var ConfigurationHelper
     */
    protected $configurationHelper;

    /**
     * @var PaymentOptionTemplateHelper
     */
    protected $paymentOptionTemplateHelper;

    /**
     * @var SettingsHelper
     */
    protected $settingsHelper;

    /**
     * @var MediaFactory
     */
    protected $mediaFactory;

    /**
     * @param ContextFactory $contextFactory
     * @param ModuleFactory $moduleFactory
     * @param SettingsHelper $settingsHelper
     * @param CustomFieldsHelper $customFieldsHelper
     * @param MediaHelper $mediaHelper
     * @param ConfigurationHelper $configurationHelper
     * @param PaymentOptionTemplateHelper $paymentOptionTemplateHelper
     * @param MediaFactory $mediaFactory
     */
    public function __construct(
        $contextFactory,
        $moduleFactory,
        $settingsHelper,
        $customFieldsHelper,
        $mediaHelper,
        $configurationHelper,
        $paymentOptionTemplateHelper,
        $mediaFactory
    ) {
        $this->context = $contextFactory->getContext();
        $this->module = $moduleFactory->getModule();
        $this->settingsHelper = $settingsHelper;
        $this->customFieldsHelper = $customFieldsHelper;
        $this->mediaHelper = $mediaHelper;
        $this->configurationHelper = $configurationHelper;
        $this->paymentOptionTemplateHelper = $paymentOptionTemplateHelper;
        $this->mediaFactory = $mediaFactory;
    }

    /**
     * @param int $installment
     * @param string $keyTitle
     * @param string $keyDescription
     *
     * @return array
     */
    public function getTexts($installment, $keyTitle, $keyDescription)
    {
        return [
            $this->customFieldsHelper->getTextButton(
                $this->context->language->id,
                $keyTitle,
                $installment
            ),
            $this->customFieldsHelper->getTextButton(
                $this->context->language->id,
                $keyDescription,
                $installment
            ),
        ];
    }

    /**
     * Create Payment option.
     *
     * @param bool $forEUComplianceModule
     * @param string $ctaText
     * @param string $action
     * @param bool $isDeferred
     * @param int $valueBNPL
     *
     * @return PaymentOption|array
     */
    public function createPaymentOption($forEUComplianceModule, $ctaText, $action, $valueBNPL, $isDeferred)
    {
        $logoName = $this->mediaHelper->getLogoName($valueBNPL, $isDeferred);

        if ($forEUComplianceModule) {
            $logo = $this->mediaFactory->getMediaPath(
                '/views/img/logos/alma_payment_logos_tiny.svg',
                $this->module
            );

            return [
                'cta_text' => $ctaText,
                'action' => $action,
                'logo' => $logo,
            ];
        }

        $paymentOption = new PaymentOption();
        $logo = $this->mediaFactory->getMediaPath(
            '/views/img/logos/' . $logoName,
            $this->module
        );

        return $paymentOption
            ->setModuleName($this->module->name)
            ->setCallToActionText($ctaText)
            ->setAction($action)
            ->setLogo($logo);
    }

    /**
     * @param $params
     *
     * @return false|mixed
     */
    public function getEuCompliance($params)
    {
        $forEUComplianceModule = false;

        if (array_key_exists('for_eu_compliance_module', $params)) {
            $forEUComplianceModule = $params['for_eu_compliance_module'];
        }

        return $forEUComplianceModule;
    }

    /**
     * @param int $installementCount
     * @param int $duration
     * @param bool $isCredit
     * @param bool $isDeferred
     * @param bool $isPayNow
     *
     * @return array
     */
    public function getTextsByTypes($installementCount, $duration, $isCredit, $isDeferred, $isPayNow)
    {
        if ($isCredit) {
            return $this->getTexts(
                $installementCount,
                PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_TITLE,
                PaymentButtonAdminFormBuilder::ALMA_PNX_AIR_BUTTON_DESC
            );
        }
        if ($isDeferred) {
            return $this->getTexts(
                $duration,
                PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_TITLE,
                PaymentButtonAdminFormBuilder::ALMA_DEFERRED_BUTTON_DESC
            );
        }
        if ($isPayNow) {
            return $this->getTexts(
                $installementCount,
                PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_TITLE,
                PaymentButtonAdminFormBuilder::ALMA_PAY_NOW_BUTTON_DESC
            );
        }

        return $this->getTexts(
            $installementCount,
            PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_TITLE,
            PaymentButtonAdminFormBuilder::ALMA_PNX_BUTTON_DESC
        );
    }

    /**
     * @param array $sortOptions
     * @param array $paymentOptions
     *
     * @return array
     */
    public function sortPaymentsOptions($sortOptions, $paymentOptions)
    {
        asort($sortOptions);
        $payment = [];

        foreach (array_keys($sortOptions) as $key) {
            $payment[] = $paymentOptions[$key];
        }

        return $payment;
    }

    /**
     * @param PaymentOption $paymentOption
     * @param $template
     *
     * @return mixed
     */
    public function setAdditionalInformationForEuCompliance($paymentOption, $template)
    {
        $paymentOption->setAdditionalInformation($template);

        if ($this->configurationHelper->isInPageEnabled($this->settingsHelper)) {
            $paymentOption->setForm($this->paymentOptionTemplateHelper->getTemplateInPage());
        }

        return $paymentOption;
    }
}
