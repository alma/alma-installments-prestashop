<?php
/**
 * 2018-2023 Alma SAS
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

namespace Alma\PrestaShop\Utils;

use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Trait AjaxTrait
 *
 * Error ajax return
 */
trait AjaxTrait
{
    /**
     * Echoes output value and exit
     * @param $json
     * @return void
     * @throws \PrestaShopException
     */
    protected function selectAjaxRenderMethod($json)
    {
        if (version_compare(_PS_VERSION_, '1.7.5.0', '>=')) {
            Logger::instance()->info('AjaxRender');
            return $this->ajaxRender(json_encode($json));
        }
        if (version_compare(_PS_VERSION_, '1.6.0.12', '>=')) {
            Logger::instance()->info('AjaxDie');
            $this->ajaxDie(json_encode($json));
            return;
        }
        Logger::instance()->info('Ajax Exit');
        exit(Tools::jsonEncode($json));
    }

    /**
     * @param $msg
     * @param int $statusCode
     * @throws \PrestaShopException
     */
    protected function ajaxFail($msg = null, $statusCode = 500)
    {
        header("X-PHP-Response-Code: $statusCode", true, $statusCode);
        $json = ['error' => true, 'message' => $msg];
        $this->selectAjaxRenderMethod($json);
    }
}
