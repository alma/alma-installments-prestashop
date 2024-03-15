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

/**
 * Class DateHelper.
 *
 * Use for method date
 */
class DateHelper
{
    /**
     * Gets all dates Y-m-d formatted since $from to today-1day while dates are >= $first
     *
     * @param int $from timestamp
     * @param int $first timestamp limit to the first
     *
     * @return array|string[]
     */
    public function getDatesInInterval($from, $first)
    {
        $to = strtotime('-1 day');
        $datesInInterval = [];
        $startTimestamp = $this->extractTimestampWithoutTime($from);
        $firstWithoutTime = $this->extractTimestampWithoutTime($first);

        for ($date = $startTimestamp; $date <= $to; $date = strtotime('+1 day', $date)) {
            if ($date >= $firstWithoutTime) {
                $datesInInterval[] = date('Y-m-d', $date);
            }
        }

        return $datesInInterval;
    }

    /**
     * check if is the same date without time
     *
     * @param int $today timestamp
     * @param int $day timestamp
     *
     * @return bool
     */
    public function isSameDay($today, $day)
    {
        return $this->extractDateWithoutTime($today) === $this->extractDateWithoutTime($day);
    }

    /**
     * extract timestamp without minutes
     *
     * @param int $timestamp
     *
     * @return int
     */
    private function extractTimestampWithoutTime($timestamp)
    {
        return strtotime($this->extractDateWithoutTime($timestamp));
    }

    /**
     * extract date without time by timestamp
     *
     * @param int $timestamp
     *
     * @return string
     */
    private function extractDateWithoutTime($timestamp)
    {
        return date('Y-m-d', $timestamp);
    }

    /**
     * format date by locale
     *
     * @param string $locale
     * @param int $timestamp
     *
     * @return string date
     */
    public function getDateFormat($locale, $timestamp)
    {
        try {
            if (class_exists(\IntlDateFormatter::class)) {
                $formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE);

                return $formatter->format($timestamp);
            }
        } catch (\Exception $e) {
            // We don't need to deal with this Exception because a fallback exists in default return statement
        }

        return $this->getFrenchDateFormat($timestamp);
    }

    /**
     * fallback for when IntlDateFormatter is not available
     *
     * @param string $timestamp
     *
     * @return string
     */
    protected function getFrenchDateFormat($timestamp)
    {
        $date = new \DateTime();
        $date->setTimestamp($timestamp);

        return $date->format('d/m/Y');
    }

    /**
     * @param string|int $timestamp
     *
     * @return bool
     */
    public function isValidTimeStamp($timestamp)
    {
        return ((string) (int) $timestamp === $timestamp)
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
    }
}
