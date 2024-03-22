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

if (!defined('_PS_VERSION_')) {
    exit;
}

class AddressHelper
{
    /**
     * @var ToolsHelper
     */
    protected $toolsHelper;

    /**
     * @param ToolsHelper $toolHelper
     */
    public function __construct($toolHelper)
    {
        $this->toolsHelper = $toolHelper;
    }

    /**
     * @param int $id
     *
     * @return \Address
     */
    public function create($id)
    {
        return new \Address((int) $id);
    }

    /**
     * @param \Customer $customer
     * @param \Context $context
     *
     * @return mixed
     */
    public function getAdressFromCustomer($customer, $context)
    {
        if ($this->toolsHelper->psVersionCompare('1.5.4.0', '<')) {
            return $customer->getAddresses($context->language->id);
        }

        return $customer->getAddresses($customer->id_lang);
    }
}
