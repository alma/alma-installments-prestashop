<?php
/**
 * 2018-2019 Alma SAS
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
 * @copyright 2018-2019 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaSecurity.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaShareOfCheckout.php';

class AlmaShareModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function __construct()
    {
        parent::__construct();
        $this->context = Context::getContext();
    }

    public function ajaxDie($value = null, $controller = null, $method = null)
    {
        if (method_exists(get_parent_class($this), 'ajaxDie')) {
            parent::ajaxDie($value);
        } else {
            die($value);
        }
    }

    private function fail($msg = null)
    {
        header('X-PHP-Response-Code: 500', true, 500);
        $this->ajaxDie(json_encode(array('error' => $msg)));
    }

    public function postProcess()
    {
        parent::postProcess();

        header('Content-Type: application/json');

        $sig = Tools::getValue('sig', null);
        $since = Tools::getValue('since', null);

        $security = new AlmaSecurity(AlmaSettings::getActiveAPIKey());
        try {
            $security->validSignature(array('since' => $since), $sig);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }

        $shareOfCheckout = new AlmaShareOfCheckout($this->context, $this->module);
        try {
            $data = $shareOfCheckout->getPayments($since);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }

        $this->ajaxDie(json_encode(array('success' => true, 'data' => $data)));
    }
}
