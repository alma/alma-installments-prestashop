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

use Alma\PrestaShop\Model\AddressData;
use Alma\PrestaShop\Model\CartData;
use Cart;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class EligibilityDataHelper.
 *
 * Eligibility Data
 */
class EligibilityDataHelper
{
    /** @var Cart $cart */
    private $cart;

    /** @var array $feePlans */
    private $feePlans;

    /** @var AddressData $addressData */
    private $addressData;

    /**
     * EligibilityData Helper
     *
     * @param Cart $cart
     * @param array $feePlans
     */
    public function __construct(
        Cart $cart,
        array $feePlans
    ) {
        $this->cart = $cart;
        $this->feePlans = $feePlans;
        $this->addressData = new AddressData($this->cart);
    }

    /**
     * Return data for eligibility
     *
     * @return array getData
     */
    public function getData()
    {
        return [
            'purchase_amount' => CartData::getPurchaseAmountInCent($this->cart),
            'queries' => $this->getQueries(),
            'shipping_address' => [
                'country' => $this->addressData->getShippingCountry(),
            ],
            'billing_address' => [
                'country' => $this->addressData->getBillingCountry(),
            ],
            'locale' => LocaleHelper::getLocale(),
        ];
    }

    /**
     * Get array of plan
     *
     * @return array getQueries
     */
    private function getQueries()
    {
        $queries = [];

        foreach ($this->feePlans as $plan) {
            $queries[] = [
                'purchase_amount' => CartData::getPurchaseAmountInCent($this->cart),
                'installments_count' => $plan['installmentsCount'],
                'deferred_days' => $plan['deferredDays'],
                'deferred_months' => $plan['deferredMonths'],
            ];
        }

        return $queries;
    }
}
