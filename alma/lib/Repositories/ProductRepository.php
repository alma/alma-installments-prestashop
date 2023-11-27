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

use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\LocaleHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class ProductRepository.
 *
 * Use for Product
 */
class ProductRepository
{
    const PRODUCT_TYPE_COMBINATIONS = 'combinations';
    const VISIBILITY_NONE = 'none';

    /**
     * @var LocaleHelper
     */
    protected $localeHelper;
    /**
     * @var \Module
     */
    private $module;

    public function __construct()
    {
        $this->localeHelper = new LocaleHelper();
        $this->module = \Module::getInstanceByName(ConstantsHelper::ALMA_MODULE_NAME);
    }

    /**
     * Get the product combinations
     *
     * @param \Cart $cart
     * @param array $products
     *
     * @return array
     *
     * @throws \PrestaShopException
     */
    public function getProductsCombinations($cart, $products)
    {
        $sql = new \DbQuery();
        $sql->select('CONCAT(p.`id_product`, "-", pa.`id_product_attribute`) as `unique_id`');

        $combinationName = new \DbQuery();
        $combinationName->select('GROUP_CONCAT(DISTINCT CONCAT(agl.`name`, " - ", al.`name`) SEPARATOR ", ")');
        $combinationName->from('product_attribute', 'pa2');
        $combinationName->innerJoin(
            'product_attribute_combination',
            'pac',
            'pac.`id_product_attribute` = pa2.`id_product_attribute`'
        );
        $combinationName->innerJoin('attribute', 'a', 'a.`id_attribute` = pac.`id_attribute`');
        $combinationName->innerJoin(
            'attribute_lang',
            'al',
            'a.id_attribute = al.id_attribute AND al.`id_lang` = ' . $cart->id_lang
        );
        $combinationName->innerJoin('attribute_group', 'ag', 'ag.`id_attribute_group` = a.`id_attribute_group`');
        $combinationName->innerJoin(
            'attribute_group_lang',
            'agl',
            'ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . $cart->id_lang
        );
        $combinationName->where(
            'pa2.`id_product` = p.`id_product` AND pa2.`id_product_attribute` = pa.`id_product_attribute`'
        );

        /* @noinspection PhpUnhandledExceptionInspection */
        $sql->select("({$combinationName->build()}) as combination_name");

        $sql->from('product', 'p');
        $sql->innerJoin('product_attribute', 'pa', 'pa.`id_product` = p.`id_product`');

        // DbQuery::where joins all where clauses with `) AND (` so for ORs we need a fully built where condition
        $where = '';
        $op = '';
        foreach ($products as $productRow) {
            if (!isset($productRow['id_product_attribute']) || !(int) $productRow['id_product_attribute']) {
                continue;
            }

            $where .= "{$op}(p.`id_product` = {$productRow['id_product']}";
            $where .= " AND pa.`id_product_attribute` = {$productRow['id_product_attribute']})";
            $op = ' OR ';
        }
        $sql->where($where);

        $db = \Db::getInstance();
        $combinationsNames = [];

        try {
            $results = $db->query($sql);
        } catch (\PrestaShopDatabaseException $e) {
            return $combinationsNames;
        }

        while ($result = $db->nextRow($results)) {
            $combinationsNames[$result['unique_id']] = $result['combination_name'];
        }

        return $combinationsNames;
    }

    /**
     * @param array $products
     *
     * @return array
     */
    public function getProductsDetails($products)
    {
        $sql = new \DbQuery();
        $sql->select('p.`id_product`, p.`is_virtual`, m.`name` as manufacturer_name');
        $sql->from('product', 'p');
        $sql->innerJoin('manufacturer', 'm', 'm.`id_manufacturer` = p.`id_manufacturer`');

        $in = [];
        foreach ($products as $productRow) {
            $in[] = $productRow['id_product'];
        }

        $in = implode(', ', $in);
        $sql->where("p.`id_product` IN ({$in})");

        $db = \Db::getInstance();
        $productsDetails = [];

        try {
            $results = $db->query($sql);
        } catch (\PrestaShopDatabaseException $e) {
            return $productsDetails;
        }

        if ($results !== false) {
            while ($result = $db->nextRow($results)) {
                $productsDetails[(int) $result['id_product']] = $result;
            }
        }

        return $productsDetails;
    }

    /**
     * @param string $reference
     * @param int $id_lang
     *
     * @return false|string
     */
    public function getProductIdByReference($reference, $id_lang = 1)
    {
        return \Db::getInstance()->getValue('SELECT p.id_product
                FROM `' . _DB_PREFIX_ . 'product` p
                ' . \Shop::addSqlAssociation('product', 'p') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` )
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
                LEFT JOIN `' . _DB_PREFIX_ . 'supplier` s ON (s.`id_supplier` = p.`id_supplier`) 
                WHERE pl.`id_lang` = ' . (int) $id_lang . '
                AND p.reference="' . (string) $reference . '"');
    }

    /**
     * @return \ProductCore
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function createInsuranceProduct()
    {
        /**
         * @var \ProductCore $product
         */
        $insuranceProductName = $this->module->l(ConstantsHelper::ALMA_INSURANCE_PRODUCT_NAME, 'ProductRepository');
        $product = new \Product();
        $product->name = $this->localeHelper->createMultiLangField($insuranceProductName);
        $product->reference = ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE;
        $product->link_rewrite = $this->localeHelper->createMultiLangField(\Tools::str2url($insuranceProductName));
        $product->id_category_default = ConstantsHelper::ALMA_INSURANCE_DEFAULT_CATEGORY;
        $product->product_type = self::PRODUCT_TYPE_COMBINATIONS;
        $product->visibility = self::VISIBILITY_NONE;

        if (version_compare(_PS_VERSION_, '1.7.8', '<')) {
            $product->out_of_stock = 1;
        }

        $product->addToCategories(ConstantsHelper::ALMA_INSURANCE_DEFAULT_CATEGORY);
        $product->add();

        if (version_compare(_PS_VERSION_, '1.7.8', '>=')) {
            \StockAvailable::setProductOutOfStock(
                $product->id,
                1
            );
        } else {
            \StockAvailable::setProductDependsOnStock(
                $product->id,
                false
            );
        }

        return $product;
    }

    /**
     * @param int $idProduct
     * @return \ProductCore
     */
    public function getProduct($idProduct)
    {
        return new \Product((int)$idProduct);
    }
}
