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

use Alma\API\RequestError;
use Alma\PrestaShop\API\ClientHelper;
use Alma\PrestaShop\Forms\ShareOfCheckoutAdminFormBuilder;
use Alma\PrestaShop\Utils\DateHelper;
use Alma\PrestaShop\Utils\Logger;
use Alma\PrestaShop\Utils\Settings;
use PrestaShop\PrestaShop\Adapter\Entity\Order;

class AdminAlmaShareOfCheckoutController extends ModuleAdminController
{
    // TODO : File to delete 
    public function __construct()
    {
        $this->startTime = null;
        $this->endTime = null;
    }

    // /**
    //  * Process endpoint Share of Checkout
    //  *
    //  * @return void
    //  */
    // public function postProcess()
    // {
    //     // var_dump(json_encode($this->getPayload()));
    //     $this->cronShareDays();
    // }

    /**
     * Date today
     *
     * @return string
     */
    public function getDateToday()
    {
        $date = new DateTime();

        return $date->getTimestamp();
    }

    /**
     * Date From
     *
     * @return string
     */
    public function getFromDate()
    {
        // $getLastShareOfCheckout = self::getLastShareOfCheckout();
        // TODO : check if $getLastShareOfCheckout or $getLastShareOfCheckout->end_time is null
        if (!empty($getLastShareOfCheckout)) {
            $today = self::getDateToday();
            $todayInDate = date('Y-m-d', $today);
            $lastTimestampShareOfCheckout = $getLastShareOfCheckout->end_time;
            $lastDateShareOfCheckout = date('Y-m-d', $lastTimestampShareOfCheckout);
            if ($lastDateShareOfCheckout < $todayInDate) {
                return strtotime('+1 day', $lastTimestampShareOfCheckout);
            }
        }
        return $this->activatedDate();
    }

    /**
     * Date To
     *
     * @return string
     */
    public function getToDate()
    {
        return $this->activatedDate();
    }

    /**
     * Date to send for Share of Checkout
     *
     * @return string
     */
    public function activatedDate()
    {
        $today = self::getDateToday();
        $todayInDate = date('Y-m-d', $today);
        $dateToSend = $today;
        // $dateToSend = strtotime('-1 day', $today);
        $activatedTimestamp = Configuration::get(ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_DATE);
        $activatedDate = date('Y-m-d', $activatedTimestamp);
        // if (empty($activatedTimestamp) || $activatedDate >= $todayInDate){
        //     $dateToSend = '';
        // }

        return $dateToSend;
    }
}