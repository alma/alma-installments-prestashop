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

namespace Alma\PrestaShop\Forms;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class ShareOfCheckoutAdminFormBuilder
 */
class ShareOfCheckoutAdminFormBuilder extends AbstractAlmaAdminFormBuilder
{
    const ALMA_SHARE_OF_CHECKOUT_STATE = 'ALMA_SHARE_OF_CHECKOUT_STATE';
    const ALMA_SHARE_OF_CHECKOUT_DATE = 'ALMA_SHARE_OF_CHECKOUT_DATE';
    const ALMA_SHARE_OF_CHECKOUT_CONSENT_UNSET = 'unset';
    const ALMA_SHARE_OF_CHECKOUT_CONSENT_YES = '1';
    const ALMA_SHARE_OF_CHECKOUT_CONSENT_NO = '0';

    protected function configForm()
    {
        $tpl = $this->context->smarty->createTemplate(
            "{$this->module->local_path}views/templates/hook/_partials/shareOfCheckout.tpl"
        );

        return [
            $this->inputHtml($tpl),
            $this->inputAlmaSwitchForm(
                self::ALMA_SHARE_OF_CHECKOUT_STATE,
                $this->module->l('Activate your data sharing settings', 'ShareOfCheckoutAdminFormBuilder')
            ),
        ];
    }

    protected function getTitle()
    {
        return $this->module->l('Increase your performance & get insights', 'ShareOfCheckoutAdminFormBuilder');
    }
}
