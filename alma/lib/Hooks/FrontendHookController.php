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

namespace Alma\PrestaShop\Hooks;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\Utils\Settings;
use Tools;

abstract class FrontendHookController extends HookController
{
    public function canRun()
    {
        $isLive = Settings::getActiveMode() === ALMA_MODE_LIVE;
        $isAlmapayStore = $_SERVER['HTTP_HOST'] === 'almapay.store';

        // Front controllers can run if the module is properly configured ...
        return Settings::isFullyConfigured()
            // ... and the plugin is in LIVE mode, or the visitor is an admin or the website is almapay.store
            && ($isLive || $this->loggedAsEmployee() || $isAlmapayStore)
            // ... and the current shop's currency is EUR
            && in_array(Tools::strtoupper($this->context->currency->iso_code), $this->module->limited_currencies);
    }
}
