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

use Alma\PrestaShop\Exceptions\MessageOrderException;
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
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @param $module
     * @param $context
     * @param $insuranceApiService
     * @param PriceHelper $priceHelper
     */
    public function __construct($module, $context, $insuranceApiService, $priceHelper)
    {
        $this->module = $module;
        $this->context = $context;
        $this->insuranceApiService = $insuranceApiService;
        $this->priceHelper = $priceHelper;
    }

    /**
     * @param $almaInsuranceProduct
     *
     * @return string
     *
     * @throws MessageOrderException
     */
    public function getInsuranceCancelMessageRefundAllow($almaInsuranceProduct)
    {
        if (!is_array($almaInsuranceProduct)) {
            throw new MessageOrderException('The parameter $almaInsuranceProduct must be an array');
        }
        $price = $almaInsuranceProduct['price'];
        $insuranceContract = $this->insuranceApiService->getInsuranceContract(
            $almaInsuranceProduct['insurance_contract_id'],
            $almaInsuranceProduct['cms_reference'],
            $price
        );
        $product = new \Product(
            $almaInsuranceProduct['id_product'],
            false,
            \Configuration::get('PS_LANG_DEFAULT')
        );

        $text = sprintf(
            'The Insurance %s at %s for the product %s has been cancelled.
            Please refund the customer.
            Action Required: Refund the customer for the affected subscriptions.
            Thank you.',
            $insuranceContract->getName(),
            $this->priceHelper->convertPriceFromCents($price) . '€',
            $product->name
        );

        return $this->module->l($text, 'messageOrderService');
    }
}
