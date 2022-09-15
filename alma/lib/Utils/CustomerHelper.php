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
    public function __construct(
        Customer $customer,
        Context $context,
        Cart $cart
    ) {
        $this->customer = $customer;
        $this->context = $context;
        $this->cart = $cart;
    }

    /**
     * Get array data customer
     *
     * @return array getData
     */
    public function getData()
    {
        $addressData = new AddressData($this->cart);

        return [
            'first_name' => $this->customer->firstname,
            'last_name' => $this->customer->lastname,
            'email' => $this->customer->email,
            'birth_date' => $this->getBirthday(),
            'addresses' => $this->getAddressesData(),
            'phone' => $this->getPhone(),
            'country' => $addressData->getBillingCountry(),
            'county_sublocality' => null,
            'state_province' => $addressData->getBillingStateProvince(),
        ];
    }

    public function getWebsiteDetails()
    {
        $carrierData = new CarrierData($this->context);
        $customerOrderHelper = new CustomerOrderHelper($this->context);

        return [
            'new_customer' => $this->isNew($this->customer->id),
            'is_guest' => (bool) $this->customer->is_guest,
            'created' => strtotime($this->customer->date_add),
            'current_order' => [
                'purchase_amount' => CartData::getPurchaseAmountInCent($this->cart),
                'payment_method' => 'alma',
                'shipping_method' => $carrierData->getNameById($this->cart->id_carrier),
                'items' => CartData::cartItems($this->cart),
            ],
            'previous_orders' => [
                $customerOrderHelper->previous($this->customer->id),
            ],
        ];
    }

    /**
     * Get birthday date
     *
     * @return string|null
     */
    private function getBirthday()
    {
        $birthday = $this->customer->birthday;
        if ($birthday == '0000-00-00') {
            return null;
        }

        return $birthday;
    }

    /**
     * Get addresses of customer
     *
     * @return array getAddressesData
     */
    private function getAddressesData()
    {
        $addresses = [];

        foreach ($this->getCustomerAddresses() as $address) {
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
     * Get addresses of customer Prestashop
     *
     * @return array getCustomerAddresses
     */
    private function getCustomerAddresses()
    {
        if (version_compare(_PS_VERSION_, '1.5.4.0', '<')) {
            $idLang = $this->context->language->id;
        } else {
            $idLang = $this->customer->id_lang;
        }

        return $this->customer->getAddresses($idLang);
    }

    /**
     * get phone between info customer and addresses customer
     *
     * @return string
     */
    private function getPhone()
    {
        $phone = null;
        $addressData = new AddressData($this->cart);
        $shippingAddress = $addressData->getShippingAddress();

        if ($shippingAddress->phone) {
            $phone = $shippingAddress->phone;
        } elseif ($shippingAddress->phone_mobile) {
            $phone = $shippingAddress->phone_mobile;
        }

        if (is_null($phone)) {
            foreach ($this->getCustomerAddresses() as $address) {
                if ($address['phone']) {
                    $phone = $address['phone'];
                } elseif ($address['phone_mobile']) {
                    $phone = $address['phone_mobile'];
                }
            }
        }

        return $phone;
    }

    /**
     * If is new customer or not
     *
     * @param int $idCustomer
     *
     * @return bool
     */
    private function isNew($idCustomer)
    {
        if (Order::getCustomerNbOrders($idCustomer) > 0) {
            return false;
        }

        return true;
    }
}
