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

namespace Alma\PrestaShop\Model;

use Carrier;
use Db;
use PrestaShopDatabaseException;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CarrierData
{
    /** @var array */
    private $carriers = [];

    /**
     * Get all carriers in a given language.
     *
     * @return array Carriers
     *
     * @throws PrestaShopDatabaseException
     */
    public function getCarriers()
    {
        if (!$this->carriers) {
            $sql = '
            SELECT
                c.id_carrier,
                c.id_reference,
                c.name
            FROM
                `' . _DB_PREFIX_ . 'carrier` c
            ';
            $this->carriers = Db::getInstance()->executeS($sql);

            foreach ($this->carriers as $key => $carrier) {
                if ($carrier['name'] == '0') {
                    $this->carriers[$key]['name'] = Carrier::getCarrierNameFromShopName();
                }
            }
        }

        return $this->carriers;
    }
}
