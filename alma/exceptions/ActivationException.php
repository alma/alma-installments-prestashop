<?php
/**
 * 2018-2023 Alma SAS.
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

namespace Alma\PrestaShop\Exceptions;

use Alma\PrestaShop\Helpers\LinkHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;

class ActivationException extends AlmaException
{
    /**
     * Constructor.
     *
     * @param object $module
     */
    public function __construct($module)
    {
        $message = $module->l('Your Alma account needs to be activated before you can use Alma on your shop.', 'ActivationException')
        . sprintf(
            $module->l('Go to your %1$sAlma dashboard%2$s to activate your account.', 'ActivationException'),
            '<a href="' . LinkHelper::getAlmaDashboardUrl(SettingsHelper::getActiveMode(), 'settings') . '" target="_blank">',
            '</a>'
        )
        . sprintf(
            $module->l('%1$sRefresh%2$s the page when ready.', 'ActivationException'),
            '<a href="#">',
            '</a>'
        );

        parent::__construct($message);
    }
}
