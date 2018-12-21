<?php
/**
 * 2018 Alma / Nabla SAS
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
 * @author    Alma / Nabla SAS <contact@getalma.eu>
 * @copyright 2018 Alma / Nabla SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 *
 */

use Alma\API\Client;

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(_PS_MODULE_DIR_ . 'alma/includes/AlmaLogger.php');
include_once(_PS_MODULE_DIR_ . 'alma/includes/AlmaSettings.php');

class AlmaClient
{
    public static function defaultInstance()
    {
        static $_alma_client;

        if (!$_alma_client) {
            $_alma_client = self::createInstance(AlmaSettings::getActiveAPIKey());
        }

        return $_alma_client;
    }

    public static function createInstance($apiKey)
    {
        $alma = null;

        try {
            $alma = new Client($apiKey, array(
                'mode' => AlmaSettings::getActiveMode(),
                'api_root' => 'http://alma:1337',
                'force_tls' => false,
                'logger' => new AlmaLogger(),
            ));
        } catch (\Exception $e) {
            AlmaLogger::instance()->error("Error creating Alma API client: " . print_r($e, true));
        }

        return $alma;
    }
}
