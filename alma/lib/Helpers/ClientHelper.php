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

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\API\Client;
use Alma\PrestaShop\Exceptions\ClientException;
use Alma\PrestaShop\Logger;

class ClientHelper
{
    public static function defaultInstance()
    {
        static $almaClient;

        if (!$almaClient) {
            $almaClient = static::createInstance(SettingsHelper::getActiveAPIKey());
        }

        return $almaClient;
    }

    public static function createInstance($apiKey, $mode = null)
    {
        if (!$mode) {
            $mode = SettingsHelper::getActiveMode();
        }

        $alma = null;

        try {
            $alma = new Client($apiKey, [
                'mode' => $mode,
                'logger' => Logger::instance(),
            ]);

            $alma->addUserAgentComponent('PrestaShop', _PS_VERSION_);
            $alma->addUserAgentComponent('Alma for PrestaShop', \Alma::VERSION);
        } catch (\Exception $e) {
            Logger::instance()->error('Error creating Alma API client: ' . $e->getMessage());
        }

        return $alma;
    }

    /**
     * Check Alma client
     *
     * @return Client
     *
     * @throws ClientException
     */
    public function getAlmaClient()
    {
        $alma = ClientHelper::defaultInstance();

        if (!$alma) {
            $msg = '[Alma] Error instantiating Alma API Client';
            Logger::instance()->error($msg);
            throw new ClientException($msg);
        }

        return $alma;
    }
}
