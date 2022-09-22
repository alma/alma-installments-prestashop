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

namespace Alma\PrestaShop\Model;

use Address;
use Cart;
use Country;
use State;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AddressData.
 *
 * Address Data
 */
class AddressData
{
    /** @var Cart Cart */
    private $cart;

    /** @var Address Address of shipping */
    private $shippingAddress;

    /** @var Address Address of billing */
    private $billingAddress;

    /** @var string Country of shipping */
    private $shippingCountry;

    /** @var string Country of billing */
    private $billingCountry;

    /** @var string|null State Province of shippîng */
    private $shippingStateProvinceName;

    /** @var string|null State Province of billing */
    private $billingStateProvinceName;

    /** @var string Phone of shipping */
    private $shippingPhone = null;

    /**
     * Address Data construct
     *
     * @param Cart $cart
     */
    public function __construct(
        Cart $cart
    ) {
        $this->cart = $cart;
    }

    /**
     * Get shipping address of cart
     *
     * @return Address
     */
    public function getShippingAddress()
    {
        if (!$this->shippingAddress) {
            $this->shippingAddress = new Address((int) $this->cart->id_address_delivery);
        }

        return $this->shippingAddress;
    }

    /**
     * Get billing address of cart
     *
     * @return Address
     */
    public function getBillingAddress()
    {
        if (!$this->billingAddress) {
            $this->billingAddress = new Address((int) $this->cart->id_address_invoice);
        }

        return $this->billingAddress;
    }

    /**
     * Get country shipping address
     *
     * @return string
     */
    public function getShippingCountry()
    {
        if (!$this->shippingCountry) {
            $this->shippingCountry = Country::getIsoById($this->getShippingAddress()->id_country);
        }

        return $this->shippingCountry;
    }

    /**
     * Get country billing address
     *
     * @return string
     */
    public function getBillingCountry()
    {
        if (!$this->billingCountry) {
            $this->billingCountry = Country::getIsoById($this->getBillingAddress()->id_country);
        }

        return $this->billingCountry;
    }

    /**
     * Get state of province shipping
     *
     * @return string|null
     */
    public function getShippingStateProvinceName()
    {
        if (is_null($this->shippingStateProvinceName) && $this->getShippingAddress()->id_state > 0) {
            $this->shippingStateProvinceName = State::getNameById((int) $this->getShippingAddress()->id_state);
        }

        return $this->shippingStateProvinceName;
    }

    /**
     * Get state of province billing
     *
     * @return string|null
     */
    public function getBillingStateProvinceName()
    {
        if (is_null($this->billingStateProvinceName) && $this->getBillingAddress()->id_state > 0) {
            $this->billingStateProvinceName = State::getNameById((int) $this->getBillingAddress()->id_state);
        }

        return $this->billingStateProvinceName;
    }

    /**
     * Get phone addresses customer
     *
     * @return string
     */
    public function getShippingPhone()
    {
        if (is_null($this->shippingPhone)) {
            $shippingAddress = $this->getShippingAddress();
    
            if ($shippingAddress->phone) {
                $this->shippingPhone = $shippingAddress->phone;
            } elseif ($shippingAddress->phone_mobile) {
                $this->shippingPhone = $shippingAddress->phone_mobile;
            }
        }

        return $this->shippingPhone;
    }
}
