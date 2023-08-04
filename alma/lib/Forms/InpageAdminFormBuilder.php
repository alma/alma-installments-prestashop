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
 * Class InpageAdminFormBuilder
 */
class InpageAdminFormBuilder extends AbstractAlmaAdminFormBuilder
{
    const ALMA_ACTIVATE_INPAGE = 'ALMA_ACTIVATE_INPAGE';

    /**
     * @return array
     */
    protected function configForm()
    {
        return [
            $this->inputAlmaSwitchForm(
                self::ALMA_ACTIVATE_INPAGE,
                $this->module->l('Activate in-page checkout', 'InpageAdminFormBuilder'),
                $this->module->l('Activate in-page checkout for Pay Now, P2X, P3X and P4X', 'InpageAdminFormBuilder'),
                $this->module->l('The checkout in-page in your own website', 'InpageAdminFormBuilder')
            ),
        ];
    }

    /**
     * @return string
     */
    protected function getTitle()
    {
        return $this->module->l('In-page checkout', 'InpageAdminFormBuilder');
    }
}
