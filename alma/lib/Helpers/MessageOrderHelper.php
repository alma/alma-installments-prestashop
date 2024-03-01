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

use Alma\PrestaShop\Services\InsuranceApiService;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MessageOrderHelper
{
    /**
     * @var InsuranceApiService
     */
    protected $insuranceApiService;
    /**
     * @var \Context
     */
    protected $context;
    /**
     * @var \Alma
     */
    protected $module;

    /**
     * @param $module
     * @param $context
     * @param $insuranceApiService
     */
    public function __construct($module, $context, $insuranceApiService)
    {
        $this->module = $module;
        $this->context = $context;
        $this->insuranceApiService = $insuranceApiService;
    }

    /**
     * @param $almaInsuranceProduct
     *
     * @return string
     */
    public function getMessageForRefundInsurance($almaInsuranceProduct)
    {
        $price = $almaInsuranceProduct['price'];
        $insuranceContract = $this->insuranceApiService->getInsuranceContract($almaInsuranceProduct['id_contract'], $almaInsuranceProduct['cms_reference'], $price);
        $product = new \Product($almaInsuranceProduct['id_product'], false, $this->context->language->id, $this->context->shop->id);

        $text = sprintf(
            'The Insurance %s at %s for the product %s has been cancelled. Please refund the customer. Action Required: Refund the customer for the affected subscriptions. Thank you.',
            $insuranceContract['name'],
            PriceHelper::convertPriceFromCents($price),
            $product->name
        );

        return $this->module->l($text, 'messageOrderService');
    }
}
