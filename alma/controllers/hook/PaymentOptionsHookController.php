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

use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Model\AlmaPaymentOption;
use CartCore as Cart;

use Alma\PrestaShop\Builders\Services\PaymentServiceBuilder;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Repositories\CartEventsDataRepository;
use Alma\PrestaShop\Services\PaymentService;
use PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class PaymentOptionsHookController extends FrontendHookController
{
    /**
     * @var PaymentService
     */
    protected $paymentService;


    /**
     * @var CartEventsDataRepository
     */
    protected $cartEligibilityRepository;

    protected $configurationHelper;

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
        $this->cartEligibilityRepository = new CartEventsDataRepository();
        $this->configurationHelper = new ConfigurationHelper();

    }

    /**
     * Payment option for Hook PaymentOption (Prestashop 1.7).
     *
     * @param array $params
     *
     * @return array
     *
     * @throws LocalizationException
     * @throws \SmartyException
     */
    public function run($params)
    {
        $paymentOptions = $this->paymentService->createPaymentOptions($params);

        /** @var Cart $cart */
        $cart = $params['cart'];
        $filteredOptions = array_filter($paymentOptions, [$this, 'filterP1X']);
        $this->cartEligibilityRepository->add($cart, count($filteredOptions) > 0);

        return array_map([$this, 'mapToPaymentOption'], $paymentOptions);
    }

    /**
     * @param AlmaPaymentOption $almaPaymentOption
     * @return PaymentOption
     */
    private function mapToPaymentOption($almaPaymentOption)
    {
        return $almaPaymentOption->getOption();
    }

    /**
     * @param AlmaPaymentOption $almaPaymentOption
     * @return bool
     */
    private function filterP1X($almaPaymentOption)
    {
        return !$this->configurationHelper->isPayNow($almaPaymentOption->getAlmaPaymentPlanKey());
    }
}
