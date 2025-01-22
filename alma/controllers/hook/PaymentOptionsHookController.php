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

namespace Alma\PrestaShop\Controllers\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\Builders\Services\PaymentServiceBuilder;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Services\PaymentService;

class PaymentOptionsHookController extends FrontendHookController
{
    /**
     * @var PaymentService
     */
    protected $paymentService;

    /**
     * @codeCoverageIgnore
     *
     * @param $module
     */
    public function __construct($module)
    {
        parent::__construct($module);

        $paymentServiceBuilder = new PaymentServiceBuilder();
        $this->paymentService = $paymentServiceBuilder->getInstance();
    }

    /**
     * Payment option for Hook PaymentOption (Prestashop 1.7).
     *
     * @param array $params
     *
     * @return array
     */
    public function run($params)
    {
        var_dump($this->paymentService->createPaymentOptions($params));

        return $this->paymentService->createPaymentOptions($params);
    }
}
