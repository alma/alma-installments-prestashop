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

namespace Alma\PrestaShop\Services;

use Alma\PrestaShop\Repositories\CombinationRepository;

class CombinationProductAttributeService
{
    /**
     * @var CombinationRepository
     */
    protected $combinationRepository;

    public function __construct()
    {
        $this->combinationRepository = new CombinationRepository();
    }
    /**
     * @param \ProductCore$product
     * @param int $attributeId
     * @param string $reference
     * @param float $price
     * @param int $outOfStock See \StockAvailable::out_of_stock
     * @param int $shopId
     * @return int
     */
    public function manageCombination($product, $attributeId, $reference, $price, $shopId = 1, $outOfStock = 1)
    {
        /**
         * @var \CombinationCore $combinaison
         */
        $idProductAttributeInsurance = $this->combinationRepository->getIdByReference($product->id, $reference);

        if (!$idProductAttributeInsurance) {
            $idProductAttributeInsurance = $product->addCombinationEntity(
                $price,
                $price,
                0,
                1,
                0,
                1,
                0,
                $reference,
                0,
                '',
                0
            );

            $combination = new \CombinationCore((int)$idProductAttributeInsurance);
            $combination->setAttributes([$attributeId]);

            $this->manageStocks(
                $product->id,
                $outOfStock,
                $idProductAttributeInsurance,
                $shopId
            );
        }

        return $idProductAttributeInsurance;
    }

    public function manageStocks($productId, $outOfStock, $shopId, $idProductAttributeInsurance)
    {
        if (version_compare(_PS_VERSION_, '1.7.8', '>=')) {
            \StockAvailable::setProductOutOfStock(
                $productId,
                $outOfStock,
                $idProductAttributeInsurance,
                $shopId
            );
        } else {
            \StockAvailable::setProductDependsOnStock(
                $productId,
                $outOfStock == 1 ? false : true,
                $shopId,
                $idProductAttributeInsurance
            );
        }
    }
}
