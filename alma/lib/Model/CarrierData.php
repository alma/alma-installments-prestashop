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

use Cache;
use Carrier;
use Db;
use Shop;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CarrierData
{
    /**
     * Get all carriers in a given language.
     *
     * @param int $id_lang Language id
     *
     * @return array Carriers
     */
    public static function getCarriers($id_lang)
    {
        $sql = '
		SELECT c.*, cl.delay
		FROM `' . _DB_PREFIX_ . 'carrier` c
		LEFT JOIN `' . _DB_PREFIX_ . 'carrier_lang` cl ON (c.`id_carrier` = cl.`id_carrier` AND cl.`id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('cl') . ')
		LEFT JOIN `' . _DB_PREFIX_ . 'carrier_zone` cz ON (cz.`id_carrier` = c.`id_carrier`)';

        $cache_id = 'Carrier::getCarriers_' . md5($sql);
        if (!Cache::isStored($cache_id)) {
            $carriers = Db::getInstance()->executeS($sql);
            Cache::store($cache_id, $carriers);
        } else {
            $carriers = Cache::retrieve($cache_id);
        }

        foreach ($carriers as $key => $carrier) {
            if ($carrier['name'] == '0') {
                $carriers[$key]['name'] = Carrier::getCarrierNameFromShopName();
            }
        }

        return $carriers;
    }
}
