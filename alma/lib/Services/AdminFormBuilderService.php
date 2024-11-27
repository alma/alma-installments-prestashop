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

use Alma\PrestaShop\Builders\Helpers\SettingsHelperBuilder;
use Alma\PrestaShop\Builders\Models\MediaHelperBuilder;
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

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminFormBuilderService
{
    /**
     * @var \Alma\PrestaShop\Forms\PnxAdminFormBuilder
     */
    private $pnxAdminFormBuilder;
    /**
     * @var \Alma\PrestaShop\Forms\ProductEligibilityAdminFormBuilder
     */
    private $productEligibilityAdminFormBuilder;
    /**
     * @var \Alma\PrestaShop\Forms\CartEligibilityAdminFormBuilder
     */
    private $cartEligibilityAdminFormBuilder;
    /**
     * @var \Alma\PrestaShop\Forms\PaymentButtonAdminFormBuilder
     */
    private $paymentButtonAdminFormBuilder;
    /**
     * @var \Alma\PrestaShop\Forms\ExcludedCategoryAdminFormBuilder
     */
    private $excludedCategoryAdminFormBuilder;
    /**
     * @var \Alma\PrestaShop\Forms\RefundAdminFormBuilder
     */
    private $refundAdminFormBuilder;
    /**
     * @var \Alma\PrestaShop\Forms\ShareOfCheckoutAdminFormBuilder
     */
    private $shareOfCheckoutAdminFormBuilder;
    /**
     * @var \Alma\PrestaShop\Forms\InpageAdminFormBuilder
     */
    private $inpageAdminFormBuilder;
    /**
     * @var \Alma\PrestaShop\Forms\PaymentOnTriggeringAdminFormBuilder
     */
    private $paymentOnTriggeringAdminFormBuilder;
    /**
     * @var \Alma\PrestaShop\Forms\ApiAdminFormBuilder
     */
    private $apiAdminFormBuilder;
    /**
     * @var \Alma\PrestaShop\Forms\DebugAdminFormBuilder
     */
    private $debugAdminFormBuilder;
    /**
     * @var \Alma\PrestaShop\Helpers\SettingsHelper
     */
    private $settingsHelper;

    public function __construct(
        $module,
        $context,
        $needsKeys,
        $pnxAdminFormBuilder = null,
        $productEligibilityAdminFormBuilder = null,
        $cartEligibilityAdminFormBuilder = null,
        $paymentButtonAdminFormBuilder = null,
        $excludedCategoryAdminFormBuilder = null,
        $refundAdminFormBuilder = null,
        $shareOfCheckoutAdminFormBuilder = null,
        $inpageAdminFormBuilder = null,
        $paymentOnTriggeringAdminFormBuilder = null,
        $apiAdminFormBuilder = null,
        $debugAdminFormBuilder = null,
        $settingsHelper = null
    ) {
        $mediaHelper = (new MediaHelperBuilder())->getInstance();
        $image = $mediaHelper->getIconPathAlmaTiny();
        if (!$pnxAdminFormBuilder) {
            $pnxAdminFormBuilder = new PnxAdminFormBuilder(
                $module,
                $context,
                $image
            );
        }
        $this->pnxAdminFormBuilder = $pnxAdminFormBuilder;
        if (!$productEligibilityAdminFormBuilder) {
            $productEligibilityAdminFormBuilder = new ProductEligibilityAdminFormBuilder(
                $module,
                $context,
                $image
            );
        }
        $this->productEligibilityAdminFormBuilder = $productEligibilityAdminFormBuilder;
        if (!$cartEligibilityAdminFormBuilder) {
            $cartEligibilityAdminFormBuilder = new CartEligibilityAdminFormBuilder(
                $module,
                $context,
                $image
            );
        }
        $this->cartEligibilityAdminFormBuilder = $cartEligibilityAdminFormBuilder;
        if (!$paymentButtonAdminFormBuilder) {
            $paymentButtonAdminFormBuilder = new PaymentButtonAdminFormBuilder(
                $module,
                $context,
                $image
            );
        }
        $this->paymentButtonAdminFormBuilder = $paymentButtonAdminFormBuilder;
        if (!$excludedCategoryAdminFormBuilder) {
            $excludedCategoryAdminFormBuilder = new ExcludedCategoryAdminFormBuilder(
                $module,
                $context,
                $image
            );
        }
        $this->excludedCategoryAdminFormBuilder = $excludedCategoryAdminFormBuilder;
        if (!$refundAdminFormBuilder) {
            $refundAdminFormBuilder = new RefundAdminFormBuilder(
                $module,
                $context,
                $image
            );
        }
        $this->refundAdminFormBuilder = $refundAdminFormBuilder;
        if (!$shareOfCheckoutAdminFormBuilder) {
            $shareOfCheckoutAdminFormBuilder = new ShareOfCheckoutAdminFormBuilder(
                $module,
                $context,
                $image
            );
        }
        $this->shareOfCheckoutAdminFormBuilder = $shareOfCheckoutAdminFormBuilder;
        if (!$inpageAdminFormBuilder) {
            $inpageAdminFormBuilder = new InpageAdminFormBuilder(
                $module,
                $context,
                $image
            );
        }
        $this->inpageAdminFormBuilder = $inpageAdminFormBuilder;
        if (!$paymentOnTriggeringAdminFormBuilder) {
            $paymentOnTriggeringAdminFormBuilder = new PaymentOnTriggeringAdminFormBuilder(
                $module,
                $context,
                $image
            );
        }
        $this->paymentOnTriggeringAdminFormBuilder = $paymentOnTriggeringAdminFormBuilder;
        if (!$apiAdminFormBuilder) {
            $apiAdminFormBuilder = new ApiAdminFormBuilder(
                $module,
                $context,
                $image,
                ['needsAPIKey' => $needsKeys]
            );
        }
        $this->apiAdminFormBuilder = $apiAdminFormBuilder;
        if (!$debugAdminFormBuilder) {
            $debugAdminFormBuilder = new DebugAdminFormBuilder(
                $module,
                $context,
                $image
            );
        }
        $this->debugAdminFormBuilder = $debugAdminFormBuilder;
        if (!$settingsHelper) {
            $settingsHelper = (new SettingsHelperBuilder())->getInstance();
        }
        $this->settingsHelper = $settingsHelper;
    }

    /**
     * @param $needsApiKey
     *
     * @return array
     */
    public function getForms($needsApiKey = true)
    {
        $formFields = [];

        if (!$needsApiKey) {
            $formFields[] = $this->pnxAdminFormBuilder->build();
            $formFields[] = $this->productEligibilityAdminFormBuilder->build();
            $formFields[] = $this->cartEligibilityAdminFormBuilder->build();
            $formFields[] = $this->paymentButtonAdminFormBuilder->build();
            $formFields[] = $this->excludedCategoryAdminFormBuilder->build();
            $formFields[] = $this->refundAdminFormBuilder->build();
            if ($this->settingsHelper->shouldDisplayShareOfCheckoutForm()) {
                $formFields[] = $this->shareOfCheckoutAdminFormBuilder->build();
            }
            $this->inpageAdminFormBuilder->build();
            if ($this->settingsHelper->isPaymentTriggerEnabledByState()) {
                $formFields[] = $this->paymentOnTriggeringAdminFormBuilder->build();
            }
        }
        $formFields[] = $this->apiAdminFormBuilder->build();
        $formFields[] = $this->debugAdminFormBuilder->build();

        return $formFields;
    }
}
