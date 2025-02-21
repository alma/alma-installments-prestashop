<?php
/**
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
