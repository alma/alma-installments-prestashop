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

use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\CustomerFactory;
use Alma\PrestaShop\Factories\LoggerFactory;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomerHelper
{
    /**
     * @var ContextFactory
     */
    protected $contextFactory;

    /**
     * @var OrderHelper
     */
    protected $orderHelper;

    /**
     * @var ValidateHelper
     */
    protected $validateHelper;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @param ContextFactory $contextFactory
     * @param OrderHelper $orderHelper
     * @param ValidateHelper $validateHelper
     * @param CustomerFactory $customerFactory
     */
    public function __construct($contextFactory, $orderHelper, $validateHelper, $customerFactory)
    {
        $this->contextFactory = $contextFactory;
        $this->orderHelper = $orderHelper;
        $this->validateHelper = $validateHelper;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @return \Customer|null
     */
    public function getCustomer()
    {
        $customer = null;

        if ($this->contextFactory->getContextCartCustomerId()) {
            $customer = $this->customerFactory->create($this->contextFactory->getContextCartCustomerId());
        }

        $customer = $this->validateCustomer($customer);

        if (!$customer) {
            $customer = $this->contextFactory->getContextCustomer();
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
            LoggerFactory::instance()->error(
                sprintf(
                    '[Alma] Error loading Customer %s from Cart %s',
                    $this->contextFactory->getContextCartCustomerId(),
                    $this->contextFactory->getContextCartId()
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
