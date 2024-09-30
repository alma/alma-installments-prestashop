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
use Alma\PrestaShop\Builders\Validators\PaymentValidationBuilder;
use Alma\PrestaShop\Exceptions\PaymentValidationException;
use Alma\PrestaShop\Helpers\SettingsHelper;
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

    /**
     * @var bool
     */
    public $ssl = true;

    /**
     * @var Context
     */
    public $context;

    /**
     * @var PaymentValidation
     */
    protected $paymentValidation;

    /**
     * IPN constructor
     *
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        parent::__construct();
        $this->context = Context::getContext();
        $paymentValidationBuilder = new PaymentValidationBuilder();
        $this->paymentValidation = $paymentValidationBuilder->getInstance();
    }

    /**
     * @return void
     *
     * @throws PrestaShopException
     * @throws Exception
     */
    public function postProcess()
    {
        parent::postProcess();

        header('Content-Type: application/json');

        $paymentId = Tools::getValue('pid');

        try {
            $this->paymentValidation->checkSignature($paymentId, SettingsHelper::getActiveAPIKey(), $_SERVER['HTTP_X_ALMA_SIGNATURE']);
            $this->paymentValidation->validatePayment($paymentId);
            $this->ajaxRenderAndExit(json_encode(['success' => true]));
        } catch (PaymentValidationException $e) {
            Logger::instance()->error('[Alma] IPN Payment Validation Error - Message : ' . $e->getMessage());
            $this->ajaxRenderAndExit(json_encode(['error' => $e->getMessage()]), 500);
        } catch (PaymentValidationError $e) {
            Logger::instance()->error('ipn payment_validation_error - Message : ' . $e->getMessage());
            $this->ajaxRenderAndExit(json_encode(['error' => $e->getMessage()]), 500);
        } catch (MismatchException $e) {
            Logger::instance()->error('ipn payment_validation_mismatch_error - Message : ' . $e->getMessage());
            $this->ajaxRenderAndExit(json_encode(['error' => $e->getMessage()]), 200);
        }
    }
}
