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

use Alma\PrestaShop\Model\CarrierData;
use Alma\PrestaShop\Model\CartData;
use Alma\PrestaShop\Model\CustomerData;
use Context;
use Customer;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class CustomerOrderHelper
 */
class CustomerOrderHelper
{
    /** @var Context $context */
    private $context;

    /** @var CustomerData $customerData */
    private $customerData;

    /**
     * CustomerOrder Helper construct
     *
     * @param Context $context
     * @param Customer $customer
     */
    public function __construct(
        Context $context,
        Customer $customer
    ) {
        $this->context = $context;
        $this->customerData = new CustomerData($context, $customer);
    }

    /**
     * Get previous cart items of the customer
     *
     * @param int $idCustomer
     *
     * @return array
     */
    public function previousOrders($idCustomer)
    {
        $cartsData = [];
        $carts = $this->customerData->getCarts($idCustomer);

        $carrier = new CarrierData($this->context);
        foreach ($carts as $cart) {
            $purchaseAmount = CartData::getPurchaseAmount($cart);
            $cartsData[] = [
                'purchase_amount' => almaPriceToCents($purchaseAmount),
                'payment_method' => 'alma',
                'shipping_method' => $carrier->getNameById($cart->id_carrier),
                'items' => CartData::getCartItems($cart),
            ];
        }

        return $cartsData;
    }
}
