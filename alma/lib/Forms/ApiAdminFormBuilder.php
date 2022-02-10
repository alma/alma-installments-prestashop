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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class ApiAdminFormBuilder
 */
class ApiAdminFormBuilder extends AbstractAlmaAdminFormBuilder
{
    const ALMA_API_MODE = 'ALMA_API_MODE';
    const ALMA_LIVE_API_KEY = 'ALMA_LIVE_API_KEY';
    const ALMA_TEST_API_KEY = 'ALMA_TEST_API_KEY';

    protected function configForm()
    {
        $needsAPIKey = isset($this->config['needsAPIKey']) ? boolval($this->config['needsAPIKey']) : false;

        $return = [
            $this->inputSelectForm(
                self::ALMA_API_MODE,
                $this->module->l('API Mode', 'ApiAdminFormBuilder'),
                // phpcs:ignore
                $this->module->l('Use Test mode until you are ready to take real orders with Alma. In Test mode, only admins can see Alma on cart/checkout pages.', 'ApiAdminFormBuilder'),
                [
                    ['api_mode' => ALMA_MODE_LIVE, 'name' => 'Live'],
                    ['api_mode' => ALMA_MODE_TEST, 'name' => 'Test'],
                ],
                'api_mode'
            ),
            $this->inputTextForm(
                self::ALMA_LIVE_API_KEY,
                $this->module->l('Live API key', 'ApiAdminFormBuilder'),
                $this->module->l('Not required for Test mode', 'ApiAdminFormBuilder') .
                    ' – ' .
                    sprintf(
                        // phpcs:ignore
                        $this->module->l('You can find your Live API key on %1$syour Alma dashboard%2$s', 'ApiAdminFormBuilder'),
                        '<a href="https://dashboard.getalma.eu/api" target="_blank">',
                        '</a>'
                    )
            ),
            $this->inputTextForm(
                self::ALMA_TEST_API_KEY,
                $this->module->l('Test API key', 'ApiAdminFormBuilder'),
                $this->module->l('Not required for Live mode', 'ApiAdminFormBuilder') .
                    ' – ' .
                    sprintf(
                        // phpcs:ignore
                        $this->module->l('You can find your Test API key on %1$syour sandbox dashboard%2$s', 'ApiAdminFormBuilder'),
                        '<a href="https://dashboard.sandbox.getalma.eu/api" target="_blank">',
                        '</a>'
                    )
            ),
        ];

        if ($needsAPIKey) {
            $return[] = $this->inputHiddenForm('_api_only');
        }

        return $return;
    }

    protected function getTitle()
    {
        return $this->module->l('API configuration', 'ApiAdminFormBuilder');
    }
}
