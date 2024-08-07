<?php
/**
 * 2018-2024 Alma SAS.
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
 * @copyright 2018-2024 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Services;

use Alma\PrestaShop\Repositories\CombinationRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CombinationProductAttributeService
{
    /**
     * @var CombinationRepository
     */
    protected $combinationRepository;

    /**
     * @var StockAvailableService
     */
    protected $stockAvailableService;

    public function __construct()
    {
        $this->combinationRepository = new CombinationRepository();
        $this->stockAvailableService = new StockAvailableService();
    }

    /**
     * @param \ProductCore$product
     * @param int $attributeId
     * @param string $reference
     * @param float $price
     * @param int $quantity
     * @param int $shopId
     * @param int $outOfStock See \StockAvailable::out_of_stock
     *
     * @return int
     */
    public function getOrCreateCombination($product, $attributeId, $reference, $price, $quantity, $shopId = 1, $outOfStock = 1)
    {
        /**
         * @var \CombinationCore $combinaison
         */
        $idProductAttributeInsurance = $this->combinationRepository->getIdByReferenceAndPrice(
            $product->id,
            $reference,
            $price
        );

        if (!$idProductAttributeInsurance) {
            $idProductAttributeInsurance = $product->addCombinationEntity(
                $price,
                $price,
                0,
                1,
                0,
                $quantity,
                0,
                $reference,
                0,
                '',
                0
            );

            $combination = new \CombinationCore((int) $idProductAttributeInsurance);
            $combination->setAttributes([$attributeId]);

            $this->stockAvailableService->createStocks(
                $product->id,
                $outOfStock,
                $shopId,
                $idProductAttributeInsurance
            );
        }

        $this->stockAvailableService->updateStocks(
            $product->id,
            $quantity,
            $shopId,
            $idProductAttributeInsurance
        );

        return $idProductAttributeInsurance;
    }
}
