<?php
/**
 * Our PHPCS is currently configured to check for older PHP syntax, so we need to disable it for now.
 * If we need to add a new PHP file using the updated syntax, we will have to create a separate folder for all PHP 8 files
 * and exclude it in the PHPCS configuration (php-compatibility.sh).
 * @phpcs:disable
 */

namespace Alma\PrestaShop\Model;

use Alma\PrestaShop\Helpers\SettingsHelper;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class LoggerPsr3 extends AbstractLogger
{
    /**
     * {@inheritDoc}
     */
    public function log($level, \Stringable|string $message, array $context = []): void
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
        \PrestaShopLogger::addLog($message, $levels[$level]);
    }
}
