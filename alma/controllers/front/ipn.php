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

use Alma\PrestaShop\API\MismatchException;
use Alma\PrestaShop\Exceptions\RefundException;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Traits\AjaxTrait;
use Alma\PrestaShop\Validators\PaymentValidation;
use Alma\PrestaShop\Validators\PaymentValidationError;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * AlmaIpnModuleFrontController
 */
class AlmaIpnModuleFrontController extends ModuleFrontController
{
    use AjaxTrait;

    public $ssl = true;

    /**
     * IPN constructor
     *
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        parent::__construct();
        $this->context = Context::getContext();
    }

    /**
     * @return void
     *
     * @throws PrestaShopException
     * @throws RefundException
     * @throws MismatchException
     */
    public function postProcess()
    {
        parent::postProcess();

        header('Content-Type: application/json');

        $paymentId = Tools::getValue('pid');
        // Test to log Header IPN callback
        Logger::instance()->info('ipn paymentId - ' . $paymentId);
        Logger::instance()->info(json_encode(get_headers($this->context->link->getModuleLink('alma', 'ipn') . '?pid=' . $paymentId, true)));

        $validator = new PaymentValidation($this->context, $this->module);

        try {
            $validator->validatePayment($paymentId);
        } catch (PaymentValidationError $e) {
            Logger::instance()->error('ipn payment_validation_error - Message : ' . $e->getMessage());
            $this->ajaxRenderAndExit(json_encode(['error' => $e->getMessage()]), 500);
        } catch (MismatchException $e) {
            Logger::instance()->error('ipn payment_validation_mismatch_error - Message : ' . $e->getMessage());
            $this->ajaxRenderAndExit(json_encode(['error' => $e->getMessage()]), 200);
        }

        $this->ajaxRenderAndExit(json_encode(['success' => true]));
    }
}
