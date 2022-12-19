<?php
/**
 * 2018-2022 Alma SAS
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
 * @copyright 2018-2022 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\API\PaymentValidation;
use Alma\PrestaShop\API\PaymentValidationError;
use Alma\PrestaShop\Utils\Logger;

class AlmaIpnModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function __construct()
    {
        parent::__construct();
        $this->context = Context::getContext();
    }

    public function ajaxDie($value = null, $controller = null, $method = null)
    {
        if (method_exists(get_parent_class(get_parent_class($this)), 'ajaxRender')) {
            parent::ajaxRender($value);
            exit;
        } elseif (method_exists(get_parent_class(get_parent_class($this)), 'ajaxDie')) {
            parent::ajaxDie($value);
        } else {
            die($value);
        }
    }

    private function fail($msg = null)
    {
        header('X-PHP-Response-Code: 500', true, 500);
        $this->ajaxDie(json_encode(['error' => $msg]));
    }

    public function postProcess()
    {
        parent::postProcess();

        header('Content-Type: application/json');

        $paymentId = Tools::getValue('pid');
        $validator = new PaymentValidation($this->context, $this->module);

        try {
            Logger::instance()->debug('payment_validate');
            $validator->validatePayment($paymentId);
        } catch (PaymentValidationError $e) {
            Logger::instance()->error('payment_validation_error - Message : ' . $e->getMessage());
            $this->fail($e->getMessage());
        } catch (Exception $e) {
            Logger::instance()->error('payment_error - Message : ' . $e->getMessage());
            $this->fail($e->getMessage());
        }

        $this->ajaxDie(json_encode(['success' => true]));
    }
}
