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
 * Class AttributeGroupRepository.
 *
 * Use for Product
 */
class AttributeGroupRepository
{
    const GROUP_TYPE_SELECT = 'select';
    /**
     * @var LocaleHelper
     */
    protected $localeHelper;

    public function __construct()
    {
        $this->localeHelper = new LocaleHelper();
        $this->module = \Module::getInstanceByName(ConstantsHelper::ALMA_MODULE_NAME);
    }

    /**
     * @param string $name
     * @param int $idLang
     *
     * @return false|string
     */
    public function getAttributeIdByName($name, $idLang = 1)
    {
        if (!\Combination::isFeatureActive()) {
            return [];
        }

        return \Db::getInstance()->getValue('
			SELECT agl.id_attribute_group 
			FROM `' . _DB_PREFIX_ . 'attribute_group` ag
			' . \Shop::addSqlAssociation('attribute_group', 'ag') . '
			LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl
			ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND `id_lang` = ' . (int) $idLang . ')
            WHERE agl.`name` = "' . $name . '"');
    }

    /**
     * @return \AttributeGroupCore
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function createInsuranceAttributeGroup()
    {
        /**
         * @var \AttributeGroupCore $attributeGroup
         */
        $attributeGroupPublicName = $this->module->l(ConstantsHelper::ALMA_INSURANCE_ATTRIBUTE_PUBLIC_NAME, 'AttributeGroupRepository');
        $attributeGroup = new \AttributeGroup();
        $attributeGroup->group_type = self::GROUP_TYPE_SELECT;
        $attributeGroup->name = $this->localeHelper->createMultiLangField(ConstantsHelper::ALMA_INSURANCE_ATTRIBUTE_NAME);
        $attributeGroup->public_name = $this->localeHelper->createMultiLangField($attributeGroupPublicName);
        $attributeGroup->add();

        return $attributeGroup;
    }
}
