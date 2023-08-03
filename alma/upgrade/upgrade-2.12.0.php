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
use Alma\API\RequestError;
use Alma\PrestaShop\Forms\ApiAdminFormBuilder;
use Alma\PrestaShop\Helpers\ApiKeyHelper;
use Alma\PrestaShop\Helpers\ClientHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @return true
 */
function upgrade_module_2_12_0()
{
    if (SettingsHelper::isFullyConfigured()) {
        $alma = ClientHelper::defaultInstance();

        if (!$alma) {
            return true;
        }

        $apiKeyHelper = new ApiKeyHelper();

        try {
            $apiKeyHelper->setLiveApiKey(SettingsHelper::get(ApiAdminFormBuilder::ALMA_LIVE_API_KEY, null));
            $apiKeyHelper->setTestApiKey(SettingsHelper::get(ApiAdminFormBuilder::ALMA_TEST_API_KEY, null));
        } catch (RequestError $e) {
            return true;
        }
    }

    return true;
}
