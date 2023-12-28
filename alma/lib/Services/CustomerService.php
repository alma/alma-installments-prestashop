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

namespace Alma\PrestaShop\Services;

use Alma\API\Entities\Insurance\Subscriber;
use Alma\PrestaShop\Model\AddressData;
use Alma\PrestaShop\Model\CustomerData;

class CustomerService
{
    /**
     * @var CustomerData
     */
    protected $customer;
    /**
     * @var AddressData
     */
    protected $shippingAddress;
    /**
     * @var AddressData
     */
    protected $billingAddress;

    /**
     * @param int $idCustomer
     * @param int $idShippingAddress
     * @param int $idBillingAddress
     */
    public function __construct($idCustomer, $idBillingAddress, $idShippingAddress = null)
    {
        $this->customer = new CustomerData((int) $idCustomer);
        $this->billingAddress = new AddressData((int) $idBillingAddress);
        if ($idShippingAddress) {
            $this->shippingAddress = new AddressData((int) $idShippingAddress);
        }
    }

    /**
     * @return Subscriber
     */
    public function getSubscriber()
    {
        return new Subscriber(
            $this->customer->getEmail(),
            $this->getPhone(),
            $this->customer->getLastname(),
            $this->customer->getFirstname(),
            $this->getBillingAddressLine1(),
            $this->getBillingAddressLine2(),
            $this->getBillingZipCode(),
            $this->getBillingCity(),
            $this->getBillingCountry(),
            $this->customer->getBirthday()
        );
    }

    /**
     * I guess the shipping phone number is the most serious one before billing
     * @return string|null
     */
    private function getPhone()
    {
        if ($this->shippingAddress) {
            if ($this->shippingAddress->getPhoneMobile()) {
                return $this->shippingAddress->getPhoneMobile();
            }
            if ($this->shippingAddress->getPhone()) {
                return $this->shippingAddress->getPhone();
            }
        }
        if ($this->billingAddress->getPhoneMobile()) {
            return $this->billingAddress->getPhoneMobile();
        }
        if ($this->billingAddress->getPhone()) {
            return $this->billingAddress->getPhone();
        }

        return null;
    }

    /**
     * @return string
     */
    private function getBillingAddressLine1()
    {
        return $this->billingAddress->getAddressLine1();
    }

    /**
     * @return string
     */
    private function getBillingAddressLine2()
    {
        return $this->billingAddress->getAddressLine2();
    }

    /**
     * @return string
     */
    private function getBillingZipCode()
    {
        return $this->billingAddress->getZipCode();
    }

    /**
     * @return string
     */
    private function getBillingCity()
    {
        return $this->billingAddress->getCity();
    }

    /**
     * @return string
     */
    private function getBillingCountry()
    {
        return $this->billingAddress->getCountry();
    }
}
