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

namespace Alma\PrestaShop\Helpers;

use Alma\PrestaShop\Forms\ApiAdminFormBuilder;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class ApiKeyHelper.
 *
 * Use for method date
 */
class ApiKeyHelper
{
    /**
     * @var EncryptionHelper
     */
    private $encryptionHelper;

    /**
     * ApiKey Helper construct
     */
    public function __construct()
    {
        $this->encryptionHelper = new EncryptionHelper();
    }

    /**
     * Get api key
     *
     * @param string $keyMode
     *
     * @return string
     */
    public function getValueApiKey($keyMode)
    {
        return trim(\Tools::getValue($keyMode));
    }

    /**
     * Set encrypted live api key
     *
     * @param string $apiKey
     *
     * @return void
     */
    public function setLiveApiKey($apiKey)
    {
        SettingsHelper::updateValue(ApiAdminFormBuilder::ALMA_LIVE_API_KEY, $this->encryptionHelper->encrypt($apiKey));
    }

    /**
     * Set encrypted test api key
     *
     * @param string $apiKey
     *
     * @return void
     */
    public function setTestApiKey($apiKey)
    {
        SettingsHelper::updateValue(ApiAdminFormBuilder::ALMA_TEST_API_KEY, $this->encryptionHelper->encrypt($apiKey));
    }
}
