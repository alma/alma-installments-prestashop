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

namespace Alma\PrestaShop\Traits;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Trait AjaxTrait.
 *
 * Error ajax return
 */
trait AjaxTrait
{
    /**
     * Echoes output value and exit.
     *
     * @param int $responseCode
     * @param string|null $value
     * @param string|null $controller
     * @param string|null $method
     *
     * @return void
     *
     * @throws \PrestaShopException
     */
    protected function ajaxRenderAndExit($value = null, $responseCode = null, $controller = null, $method = null)
    {
        header('Content-Type: application/json');
        if ($responseCode) {
            header('X-PHP-Response-Code: ' . $responseCode, true, $responseCode);
        }

        if (version_compare(_PS_VERSION_, '1.7.5.0', '>=')) {
            $this->renderAndExit($value, $controller, $method);
        }

        if (version_compare(_PS_VERSION_, '1.6.0.12', '>=')) {
            $this->ajaxDie($value, $controller, $method);
        }

        $this->ajaxRenderBefore16012($value);
    }

    /**
     * @param string $msg
     * @param int $statusCode
     *
     * @return void
     *
     * @throws \PrestaShopException
     */
    protected function ajaxFailAndDie($msg = null, $statusCode = 500)
    {
        header("X-PHP-Response-Code: $statusCode", true, $statusCode);
        $json = ['error' => true, 'message' => $msg];

        $this->ajaxRenderAndExit(json_encode($json));
    }

    /**
     * @param string|null $value
     * @param string|null $controller
     * @param string|null $method
     *
     * @return void
     *
     * @throws \PrestaShopException
     */
    protected function renderAndExit($value = null, $controller = null, $method = null)
    {
        $this->ajaxRender($value, $controller, $method);
        exit;
    }

    /**
     * @param string $value
     *
     * @return void
     */
    protected function ajaxRenderBefore16012($value)
    {
        exit($value);
    }
}
