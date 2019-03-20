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

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(_PS_MODULE_DIR_ . 'alma/includes/AlmaSettings.php');

class AlmaLogger extends AbstractLogger
{
    public static function instance()
    {
        static $instance;

        if (!$instance) {
            $instance = new AlmaLogger();
        }

        return $instance;
    }

    public static function loggerClass()
    {
        if (class_exists('PrestaShopLogger')) {
            return PrestaShopLogger;
        } else {
            return Logger;
        }
    }

    public function log($level, $message, array $context = array())
    {
        if (!AlmaSettings::canLog()) {
            return;
        }

        $levels = array(
            LogLevel::DEBUG => 1,
            LogLevel::INFO => 1,
            LogLevel::NOTICE => 1,
            LogLevel::WARNING => 2,
            LogLevel::ERROR => 3,
            LogLevel::ALERT => 4,
            LogLevel::CRITICAL => 4,
            LogLevel::EMERGENCY => 4,
        );

        $Logger = AlmaLogger::loggerClass();
        $Logger::addLog($message, $levels[$level]);
    }
}
