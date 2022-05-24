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

namespace Alma\PrestaShop\Forms;

use Module;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class ShareOfCheckoutAdminFormBuilder
 */
class ShareOfCheckoutAdminFormBuilder extends AbstractAlmaAdminFormBuilder
{
    const ALMA_ACTIVATE_SHARE_OF_CHECKOUT = 'ALMA_ACTIVATE_SHARE_OF_CHECKOUT';
    const ALMA_SHARE_OF_CHECKOUT_DATE = 'ALMA_SHARE_OF_CHECKOUT_DATE';

    protected function configForm()
    {
        $htmlCronJobDisabled = sprintf(
            // phpcs:ignore Generic.Files.LineLength
            $this->module->l('To use the share of checkout feature you need to download the native Cron module for Prestashop. %1$sSee the module%2$s.', 'PaymentOnTriggeringAdminFormBuilder'),
            '<a href="https://addons.prestashop.com/fr/outils-administration/17412-cron-tasks-manager.html" target="_blank">',
            '</a>'
        );

        if (!Module::isEnabled('cronjobs')) {
            return [
                $this->inputHtml(null, $htmlCronJobDisabled),
            ];
        }
        return [
            $this->inputAlmaSwitchForm(
                self::ALMA_ACTIVATE_SHARE_OF_CHECKOUT,
                $this->module->l('Activate Share of checkout', 'ShareOfCheckoutAdminFormBuilder')
            ),
            $this->inputHiddenForm(
                self::ALMA_SHARE_OF_CHECKOUT_DATE
            ),
        ];
    }

    protected function getTitle()
    {
        return $this->module->l('Share of checkout options', 'ShareOfCheckoutAdminFormBuilder');
    }
}
