<?php
/**
 * 2018-2022 Alma SAS
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
 * @copyright 2018-2022 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Utils;

use Context;
use Tools;

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
     * @param $from
     * @param $shareOfCheckoutEnabledDate
     * @param $to
     * @return array
     */
    public static function getDatesInInterval($from,$shareOfCheckoutEnabledDate,$to = null)
    {
        $from = date('Y-m-d', $from);
        $shareOfCheckoutEnabledDate = date('Y-m-d', $shareOfCheckoutEnabledDate);
        if(!isset($to)){
            $to = strtotime('-1 day');
        }
        $datesInInterval = [];
        $startTimestamp = strtotime('+1 day',strtotime($from));
        for ($i = $startTimestamp; $i <= $to ; $i = strtotime('+1 day', $i)) {
            if($i >= strtotime($shareOfCheckoutEnabledDate)){
                $datesInInterval[] = date('Y-m-d',$i);
            }
        }
        return $datesInInterval;
    }
}
