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

namespace Alma\PrestaShop\Model;

class AddressData
{
    /**
     * @var \Address
     */
    protected $address;

    /**
     * @param $idAddress
     */
    public function __construct($idAddress)
    {
        $this->address = new \Address((int) $idAddress);
    }

    /**
     * @return \Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getAddressLine1()
    {
        return $this->address->address1;
    }

    /**
     * @return string
     */
    public function getAddressLine2()
    {
        return $this->address->address2;
    }

    /**
     * @return string
     */
    public function getZipCode()
    {
        return $this->address->postcode;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->address->city;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->address->country;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->address->phone;
    }

    /**
     * @return string
     */
    public function getPhoneMobile()
    {
        return $this->address->phone_mobile;
    }
}
