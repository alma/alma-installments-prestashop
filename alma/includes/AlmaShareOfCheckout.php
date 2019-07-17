<?php
/**
 * 2018-2019 Alma SAS
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
 * @copyright 2018-2019 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

class AlmaShareOfCheckout
{
    private $context;
    private $module;

    public function __construct($context, $module)
    {
        $this->context = $context;
        $this->module = $module;
    }

    /**
     * @param $getPayments
     * @return string URL to redirect the customer to
     * @throws Exception
     */
    public function getPayments($since)
    {
        if (!$this->isValidTimeStamp($since)) {
            AlmaLogger::instance()->error("[Alma] ShareOfCheckout since value {$since}: not a valid timestamp.");
            throw new Exception('not a valid timestamp');
        }
        $dateSince = new Datetime();
        $dateSince->setTimestamp($since);
        $payments = Db::getInstance()->executeS('
            SELECT `payment_method`, COUNT(`id_order_payment`) AS `count`, SUM(`amount`) AS `tpv`
            FROM `'._DB_PREFIX_.'order_payment`
            WHERE `date_add` >= \'' . pSQL($dateSince->format('Y-m-d')) . '\'
            GROUP BY `payment_method`
        ');
        $data = [];
        if ($payments) {
            foreach ($payments as $payment) {
                $data[$payment['payment_method']] = [
                    'count' => $payment['count'],
                    'tpv' => $payment['tpv'],
                ];
            }
        }
        return $data;
    }

    protected function isValidTimeStamp($timestamp)
    {
        return ((string) (int) $timestamp === $timestamp)
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
    }
}
