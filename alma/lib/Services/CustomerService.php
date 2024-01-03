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
use Alma\PrestaShop\Model\AddressModel;
use Alma\PrestaShop\Model\CustomerModel;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomerService
{
    /**
     * @var CustomerModel
     */
    protected $customer;
    /**
     * @var AddressModel
     */
    protected $shippingAddress;
    /**
     * @var AddressModel
     */
    protected $billingAddress;

    /**
     * @param int $idCustomer
     * @param int $idShippingAddress
     * @param int $idBillingAddress
     */
    public function __construct($idCustomer, $idBillingAddress, $idShippingAddress = null)
    {
        $this->customer = new CustomerModel((int) $idCustomer);
        $this->billingAddress = new AddressModel((int) $idBillingAddress);

        if ($idShippingAddress) {
            $this->shippingAddress = new AddressModel((int) $idShippingAddress);
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
            $this->billingAddress->getAddressLine1(),
            $this->billingAddress->getAddressLine2(),
            $this->billingAddress->getZipCode(),
            $this->billingAddress->getCity(),
            $this->billingAddress->getCountry(),
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
}
