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

use Alma\PrestaShop\Builders\LocaleHelperBuilder;
use Alma\PrestaShop\Helpers\LocaleHelper;
use Alma\PrestaShop\Repositories\AttributeRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AttributeProductService
{
    /**
     * @var \ContextCore
     */
    protected $context;

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var LocaleHelper
     */
    protected $localeHelper;

    public function __construct()
    {
        $this->context = \Context::getContext();
        $localeHelperBuilder = new LocaleHelperBuilder();
        $this->localeHelper = $localeHelperBuilder->getInstance();

        $this->attributeRepository = new AttributeRepository();
    }

    /**
     * @param string $name
     * @param int $attributeGroupId
     *
     * @return int
     */
    public function getAttributeId($name, $attributeGroupId)
    {
        $insuranceAttributeId = $this->attributeRepository->getAttributeIdByNameAndGroup(
            $name,
            $attributeGroupId,
            $this->context->language->id
        );

        if (!$insuranceAttributeId) {
            $insuranceAttribute = $this->getProductAttributeObject();

            $insuranceAttribute->name = $this->localeHelper->createMultiLangField($name);
            $insuranceAttribute->id_attribute_group = $attributeGroupId;
            $insuranceAttribute->add();

            $insuranceAttributeId = $insuranceAttribute->id;
        }

        return $insuranceAttributeId;
    }

    /**
     * @return \AttributeCore|\ProductAttributeCore
     */
    public function getProductAttributeObject()
    {
        if (version_compare(_PS_VERSION_, '8.0', '<')) {
            /*
             * @var \AttributeCore $insuranceAttribute
             */
            return new \AttributeCore();
        }

        /*
         * @var \ProductAttributeCore $insuranceAttribute
         */
        return new \ProductAttributeCore();
    }

    /**
     * @param int $idProduct
     *
     * @return int
     *
     * @throws \PrestaShopException
     */
    public function getIdProductAttributeFromPost($idProduct)
    {
        $idProductAttribute = (int) \Tools::getValue('product_attribute_id');

        if (\Tools::getIsset('group')) {
            $idProductAttribute = (int) \Product::getIdProductAttributeByIdAttributes(
                $idProduct,
                \Tools::getValue('group')
            );
        }

        return $idProductAttribute;
    }
}
