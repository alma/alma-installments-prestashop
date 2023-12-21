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

namespace Alma\PrestaShop\Repositories;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AlmaInsuranceProductRepository.
 *
 * Use for Product
 */
class CombinationRepository
{

    /**
     * For a given product_attribute reference, returns the corresponding id.
     *
     * @param int $idProduct
     * @param string $reference
     *
     * @return int id
     */
    public function getIdByReference($idProduct, $reference, $price)
    {
        if (empty($reference)) {
            return 0;
        }

        $query = new \DbQuery();
        $query->select('pa.id_product_attribute');
        $query->from('product_attribute', 'pa');
        $query->where('pa.id_product = ' . (int)$idProduct);
        $query->where('pa.reference = "' . (string) $reference . '"');
        $query->where('pa.price = ' . (float)$price);

        return \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }
}
