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

use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Logger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomerHelper
{
    /**
     * @var \Context
     */
    protected $context;

    /**
     * @var OrderHelper
     */
    protected $orderHelper;

    /**
     * @var ValidateHelper
     */
    protected $validateHelper;

    /**
     * @param ContextFactory $contextFactory
     * @param OrderHelper $orderHelper
     * @param ValidateHelper $validateHelper
     *
     */
    public function __construct($contextFactory, $orderHelper, $validateHelper)
    {
        $this->context = $contextFactory->getContext();
        $this->orderHelper = $orderHelper;
        $this->validateHelper = $validateHelper;
    }

    /**
     * @param $id
     *
     * @return \Customer
     */
    public function createCustomer($id)
    {
        return new \Customer($id);
    }

    /**
     * @return \Customer|null
     */
    public function getCustomer()
    {
        if ($this->context->cart->id_customer) {
            $customer = $this->createCustomer($this->context->cart->id_customer);
        }

        $customer = $this->validateCustomer($customer);

        if (!$customer) {
            $customer = $this->context->customer;
        }

        return $this->validateCustomer($customer);
    }

    /**
     * @param \Customer $customer
     *
     * @return mixed|null
     */
    public function validateCustomer($customer)
    {
        if (!$this->validateHelper->isLoadedObject($customer)) {
            Logger::instance()->error(
                sprintf(
                    '[Alma] Error loading Customer %s from Cart %s',
                    $this->context->cart->id_customer,
                    $this->context->cart->id
                )
            );

            return null;
        }

        return $customer;
    }

    /**
     * @param int $idCustomer
     *
     * @return bool
     */
    public function isNewCustomer($idCustomer)
    {
        if ($this->orderHelper->getCustomerNbOrders($idCustomer) > 0) {
            return false;
        }

        return true;
    }
}
