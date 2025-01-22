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

use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Model\PaymentData;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentHelper
{
    /**
     * @var PaymentData
     */
    protected $paymentData;

    /**
     * @param $paymentData
     */
    public function __construct($paymentData)
    {
        $this->paymentData = $paymentData;
    }

    /**
     * Return payload to send to Alma API for create payment
     *
     * @param $activePlans
     *
     * @return array
     *
     * @throws \Alma\API\ParamsError
     * @throws \Alma\PrestaShop\Exceptions\AlmaException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function checkPaymentData($activePlans)
    {
        $paymentData = $this->paymentData->dataFromCart($activePlans);

        if (!$paymentData) {
            Logger::instance()->error('Cannot check cart eligibility: no data extracted from cart');

            return [];
        }

        return $paymentData;
    }
}
