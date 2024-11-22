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

namespace Alma\PrestaShop\Model;

use Alma\PrestaShop\Exceptions\AlmaApiKeyException;
use Alma\PrestaShop\Forms\ApiAdminFormBuilder;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\Prestashop\Proxy\ConfigurationProxy;
use Alma\Prestashop\Proxy\ToolsProxy;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AlmaApiKeyModel
{
    const ALMA_API_KEY_MODE = [
        'test' => ApiAdminFormBuilder::ALMA_TEST_API_KEY,
        'live' => ApiAdminFormBuilder::ALMA_LIVE_API_KEY,
    ];
    /**
     * @var ToolsProxy
     */
    private $toolsProxy;
    /**
     * @var ConfigurationProxy|mixed|null
     */
    private $configurationProxy;

    public function __construct(
        $toolsProxy = null,
        $configurationProxy = null
    ) {
        if (!$toolsProxy) {
            $toolsProxy = new ToolsProxy();
        }
        $this->toolsProxy = $toolsProxy;

        if (!$configurationProxy) {
            $configurationProxy = new ConfigurationProxy();
        }
        $this->configurationProxy = $configurationProxy;
    }

    /**
     * @param $mode
     *
     * @return void
     *
     * @throws \Alma\PrestaShop\Exceptions\AlmaApiKeyException
     */
    public function checkActiveApiKey($mode)
    {
        $apiKeyCurrentMode = $this->toolsProxy->getValue(self::ALMA_API_KEY_MODE[$mode]);
        if (empty($apiKeyCurrentMode)) {
            // TODO : set 'suggestPSAccounts' => false,
            // TODO : set $this->hasKey = false;
            throw new AlmaApiKeyException('[Alma] No active API key found');
        }
    }

    /**
     * @throws \Exception
     */
    public function getActiveApiKey()
    {
        if (ALMA_MODE_LIVE == $this->configurationProxy->get(ApiAdminFormBuilder::ALMA_API_MODE)) {
            return SettingsHelper::getLiveKey();
        }

        return SettingsHelper::getTestKey();
    }

    /**
     * @throws \Exception
     */
    public function needApiKey()
    {
        $key = trim(SettingsHelper::getActiveAPIKey());

        return '' == $key || null == $key;
    }
}
