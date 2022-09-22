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
use Alma\PrestaShop\Model\CarrierData;
use Alma\PrestaShop\Model\CartData;
use Alma\PrestaShop\Model\CustomerData;
use Cart;
use Context;
use Country;
use Customer;
use Order;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class CustomerHelper.
 *
 * Customer Helper
 */
class CustomerHelper
{
    /** @var Customer $customer */
    private $customer;

    /** @var Cart $cart */
    private $cart;

    /** @var AddressData $addressData */
    private $addressData;

    /** @var CustomerData $customerData */
    private $customerData;

    /** @var CarrierData $carrierData */
    private $carrierData;

    /** @var CustomerOrderHelper $customerOrderHelper */
    private $customerOrderHelper;

    /**
     * Customer Helper construct
     *
     * @param Customer $customer
     * @param Context $context
     * @param Cart $cart
     */
    public function __construct(
        Customer $customer,
        Context $context,
        Cart $cart
    ) {
        $this->customer = $customer;
        $this->cart = $cart;
        $this->addressData = new AddressData($cart);
        $this->customerData = new CustomerData($context, $customer);
        $this->carrierData = new CarrierData($context);
        $this->customerOrderHelper = new CustomerOrderHelper($context, $customer);
    }

    /**
     * Get array data customer
     *
     * @return array getData
     */
    public function getData()
    {
        return [
            'first_name' => $this->customer->firstname,
            'last_name' => $this->customer->lastname,
            'email' => $this->customer->email,
            'birth_date' => $this->customerData->getBirthday(),
            'addresses' => $this->getAddressesData(),
            'phone' => $this->getPhone(),
            'country' => $this->addressData->getBillingCountry(),
            'county_sublocality' => null,
            'state_province' => $this->addressData->getBillingStateProvinceName(),
        ];
    }

    /**
     * Detail customer for risk
     *
     * @return array
     */
    public function getWebsiteDetails()
    {
        return [
            'new_customer' => $this->customerData->isNew($this->customer->id),
            'is_guest' => (bool) $this->customer->is_guest,
            'created' => strtotime($this->customer->date_add),
            'current_order' => [
                'purchase_amount' => CartData::getPurchaseAmountInCent($this->cart),
                'payment_method' => 'alma',
                'shipping_method' => $this->carrierData->getNameById($this->cart->id_carrier),
                'items' => CartData::getCartItems($this->cart),
            ],
            'previous_orders' => [
                $this->customerOrderHelper->previousOrders($this->customer->id),
            ],
        ];
    }

    /**
     * Get addresses of customer
     *
     * @return array getAddressesData
     */
    private function getAddressesData()
    {
        $addresses = [];

        foreach ($this->customerData->getAddresses() as $address) {
            array_push($addresses, [
                'line1' => $address['address1'],
                'postal_code' => $address['postcode'],
                'city' => $address['city'],
                'country' => Country::getIsoById((int) $address['id_country']),
                'county_sublocality' => null,
                'state_province' => $address['state'],
            ]);
        }

        return $addresses;
    }

    /**
     * Get phone between info customer and addresses customer
     *
     * @return string
     */
    private function getPhone()
    {
        $phone = $this->addressData->getShippingPhone();

        if (is_null($phone)) {
            $phone = $this->customerData->getPhone();
        }

        return $phone;
    }
}
