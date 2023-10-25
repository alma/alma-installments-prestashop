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

namespace Alma\PrestaShop;

use Alma\PrestaShop\Helpers\SettingsHelper;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Logger extends AbstractLogger
{
    public static function instance()
    {
        static $instance;

        if (!$instance) {
            $instance = new Logger();
        }

        return $instance;
    }

    public static function loggerClass()
    {
        if (class_exists('PrestaShopLogger')) {
            return 'PrestaShopLogger';
        } else {
            return 'Logger';
        }
    }

    public function log($level, $message, array $context = [])
    {
        if (!SettingsHelper::canLog()) {
            return;
        }

        $levels = [
            LogLevel::DEBUG => 1,
            LogLevel::INFO => 1,
            LogLevel::NOTICE => 1,
            LogLevel::WARNING => 2,
            LogLevel::ERROR => 3,
            LogLevel::ALERT => 4,
            LogLevel::CRITICAL => 4,
            LogLevel::EMERGENCY => 4,
        ];

        $logger = Logger::loggerClass();
        $logger::addLog($message, $levels[$level]);
    }
}
