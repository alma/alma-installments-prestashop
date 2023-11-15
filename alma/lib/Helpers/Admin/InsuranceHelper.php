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

namespace Alma\PrestaShop\Helpers\Admin;

use Alma\PrestaShop\Exceptions\InsuranceInitException;
use Alma\PrestaShop\Exceptions\WrongParamsException;
use Alma\PrestaShop\Helpers\ConfigurationHelper;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Repositories\AttributeGroupRepository;
use Alma\PrestaShop\Repositories\ProductRepository;

class InsuranceHelper
{
    /**
     * Insurance form fields for mapping
     *
     * @var string[]
     */
    public static $fieldsDbInsuranceToIframeParamNames = [
        ConstantsHelper::ALMA_ACTIVATE_INSURANCE => 'is_insurance_activated',
        ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_PRODUCT => 'is_insurance_on_product_page_activated',
        ConstantsHelper::ALMA_SHOW_INSURANCE_WIDGET_CART => 'is_insurance_on_cart_page_activated',
        ConstantsHelper::ALMA_SHOW_INSURANCE_POPUP_CART => 'is_add_to_cart_popup_insurance_activated',
    ];

    /**
     * @var TabsHelper
     */
    public $tabsHelper;
    /**
     * @var ConfigurationHelper
     */
    public $configurationHelper;
    /**
     * @var mixed
     */
    private $module;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var AttributeGroupRepository
     */
    private $attributeGroupRepository;

    public function __construct($module)
    {
        $this->module = $module;
        $this->context = \Context::getContext();
        $this->tabsHelper = new TabsHelper();
        $this->configurationHelper = new ConfigurationHelper();
        $this->productRepository = new ProductRepository();
        $this->attributeGroupRepository = new AttributeGroupRepository();
    }

    /**
     * @return array[]
     */
    protected function tabsInsuranceDescription()
    {
        return [
            ConstantsHelper::BO_CONTROLLER_INSURANCE_CLASSNAME => [
                'name' => $this->module->l('Insurance'),
                'parent' => ConstantsHelper::ALMA_MODULE_NAME,
                'position' => 3,
                'icon' => 'security',
            ],
            ConstantsHelper::BO_CONTROLLER_INSURANCE_CONFIGURATION_CLASSNAME => [
                'name' => $this->module->l('Configuration'),
                'parent' => ConstantsHelper::BO_CONTROLLER_INSURANCE_CLASSNAME,
                'position' => 1,
                'icon' => 'tune',
            ],
            ConstantsHelper::BO_CONTROLLER_INSURANCE_ORDERS_CLASSNAME => [
                'name' => $this->module->l('Orders'),
                'parent' => ConstantsHelper::BO_CONTROLLER_INSURANCE_CLASSNAME,
                'position' => 2,
                'icon' => 'shopping_basket',
            ],
        ];
    }

    /**
     * @param int $isAllowInsurance
     *
     * @return bool|null
     *
     * @throws \PrestaShopException
     */
    public function handleBOMenu($isAllowInsurance)
    {
        $tab = $this->tabsHelper->getInstanceFromClassName(ConstantsHelper::BO_CONTROLLER_INSURANCE_CLASSNAME);
        // Remove tab if the tab exists and we are not allowed to have it
        if (
            $tab->id
            && !$isAllowInsurance
        ) {
            $this->tabsHelper->uninstallTabs($this->tabsInsuranceDescription());
        }

        // Add tab if the tab not exists and we are allowed to have it
        if (
            !$tab->id
            && $isAllowInsurance
        ) {
            $this->tabsHelper->installTabs($this->tabsInsuranceDescription());
        }

        return null;
    }

    /**
     * Instantiate default db values if insurance is activated or remove it
     *
     * @param bool $isAllowInsurance
     *
     * @return void
     */
    public function handleDefaultInsuranceFieldValues($isAllowInsurance)
    {
        $isAlmaInsuranceActivated = $this->configurationHelper->hasKey(ConstantsHelper::ALMA_ACTIVATE_INSURANCE);

        // If insurance is allowed and do not exist in db
        if (
            $isAllowInsurance
            && !$isAlmaInsuranceActivated
        ) {
            foreach (ConstantsHelper::$fieldsBoInsurance as $configKey) {
                $this->configurationHelper->updateValue($configKey, 0);
            }
        }

        // If insurance is not allowed and exists in db
        if (
            !$isAllowInsurance
            && $isAlmaInsuranceActivated
        ) {
            $this->configurationHelper->deleteByNames(ConstantsHelper::$fieldsBoInsurance);
        }
    }

    /**
     * @return string
     *
     * @throws \PrestaShopException
     */
    public function constructIframeUrlWithParams()
    {
        return sprintf(
            '%s?%s',
            ConstantsHelper::BO_URL_IFRAME_CONFIGURATION_INSURANCE,
            http_build_query($this->mapDbFieldsWithIframeParams())
        );
    }

    /**
     * @return array
     *
     * @throws \PrestaShopException
     */
    protected function mapDbFieldsWithIframeParams()
    {
        $mapParams = [];
        $fieldsBoInsurance = $this->configurationHelper->getMultiple(ConstantsHelper::$fieldsBoInsurance);

        foreach ($fieldsBoInsurance as $fieldName => $fieldValue) {
            $configKey = static::$fieldsDbInsuranceToIframeParamNames[$fieldName];
            $mapParams[$configKey] = (bool) $fieldValue ? 'true' : 'false';
        }

        return $mapParams;
    }

    /**
     * @param array $configKeys
     * @param array $dbFields
     *
     * @return void
     */
    protected function saveBOFormValues($configKeys, $dbFields)
    {
        foreach ($configKeys as $configKey => $configValue) {
            $this->configurationHelper->updateValue(
                $dbFields[$configKey],
                (int) filter_var($configValue, FILTER_VALIDATE_BOOLEAN)
            );
        }
    }

    /**
     * @param array $config
     * @return void
     *
     * @throws WrongParamsException
     */
    public function saveConfigInsurance($config)
    {
        $dbFields = array_flip(static::$fieldsDbInsuranceToIframeParamNames);
        $diffKeysArray = array_diff_key($config, $dbFields);

        if (!empty($diffKeysArray)) {
            header('HTTP/1.1 401 Unauthorized request');
            throw new WrongParamsException($this->module, $diffKeysArray);
        }

        $this->saveBOFormValues($config, $dbFields);
    }

    /**
     * Init Default data for Insurance.
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function initInsurance()
    {
        $this->installDB();
        $this->createInsuranceProductIfNotExist();
        $this->createAttributeGroupIfNotExist();
    }

    /**
     * @return void
     */
    protected function installDB()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'alma_insurance_product` (
          `id_alma_insurance_product` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `id_cart` int(10) unsigned NOT NULL,
          `id_product` int(10) unsigned NOT NULL,
          `id_shop` int(10) unsigned NOT NULL DEFAULT 1,
          `id_product_attribute` int(10) unsigned NOT NULL DEFAULT 0,
          `id_customization` int(10) unsigned NOT NULL DEFAULT 0,
          `id_product_insurance` int(10) unsigned NOT NULL,
          `id_product_attribute_insurance` int(10) unsigned NOT NULL,
          `id_order` int(10) unsigned NULL,
          `price` decimal(20,6) NOT NULL DEFAULT 0.000000,
          `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
           PRIMARY KEY (`id_alma_insurance_product`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        \Db::getInstance()->execute($sql);
    }

    /**
     * Create the default Insurance product
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    protected function createInsuranceProductIfNotExist()
    {
        $insuranceProduct = $this->productRepository->getProductIdByReference(
            ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE,
            $this->context->language->id
        );

        if (!$insuranceProduct) {
            try {
                $product = new \Product();
                $product->name = $this->createMultiLangField(utf8_encode(ConstantsHelper::ALMA_INSURANCE_PRODUCT_NAME));
                $product->reference = ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE;
                $product->link_rewrite = $this->createMultiLangField(\Tools::str2url(ConstantsHelper::ALMA_INSURANCE_PRODUCT_NAME));
                $product->id_category_default = ConstantsHelper::ALMA_INSURANCE_DEFAULT_CATEGORY;
                $product->product_type = 'combinations';
                $product->visibility = 'none';
                $product->addToCategories(ConstantsHelper::ALMA_INSURANCE_DEFAULT_CATEGORY);
                $product->add();

                $shops = \Shop::getShops(true, null, true);
                $image = new \Image();
                $image->id_product = $product->id;
                $image->cover = true;
                if (($image->validateFields(false, true)) === true && ($image->validateFieldsLang(false, true)) === true && $image->add()) {
                    $image->associateTo($shops);
                    if (!$this->uploadImage($product->id, $image->id, ConstantsHelper::ALMA_INSURANCE_PRODUCT_IMAGE_URL)) {
                        $image->delete();
                    }
                }
            } catch (InsuranceInitException $e) {
                Logger::instance()->error('[Alma] The insurance product has not been created');
            }
        }
    }

    /**
     * Create the default Insurance attribute group
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    protected function createAttributeGroupIfNotExist()
    {
        $insuranceAttributeGroup = $this->attributeGroupRepository->getAttributeIdByName(
            ConstantsHelper::ALMA_INSURANCE_ATTRIBUTE_NAME,
            $this->context->language->id
        );

        if (!$insuranceAttributeGroup) {
            try {
                $attributeGroup = new \AttributeGroup();
                $attributeGroup->group_type = 'select';
                $attributeGroup->name = $this->createMultiLangField(utf8_encode(ConstantsHelper::ALMA_INSURANCE_ATTRIBUTE_NAME));
                $attributeGroup->public_name = $this->createMultiLangField(utf8_encode(ConstantsHelper::ALMA_INSURANCE_ATTRIBUTE_PUBLIC_NAME));
                $attributeGroup->add();
            } catch (InsuranceInitException $e) {
                Logger::instance()->error('[Alma] The insurance attribute group has not been created');
            }
        }
    }

    /**
     * @param $idEntity
     * @param $idImage
     * @param $imgUrl
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    protected function uploadImage($idEntity, $idImage = null, $imgUrl)
    {
        $tmpFile = tempnam(_PS_TMP_IMG_DIR_, 'ps_import');
        $watermarkTypes = explode(',', \Configuration::get('WATERMARK_TYPES'));
        $imageObj = new \Image((int)$idImage);
        $path = $imageObj->getPathForCreation();
        $imgUrl = str_replace(' ', '%20', trim($imgUrl));
        // Evaluate the memory required to resize the image: if it's too big we can't resize it.
        if (!\ImageManager::checkImageMemoryLimit($imgUrl)) {
            return false;
        }
        if (@copy($imgUrl, $tmpFile)) {
            \ImageManager::resize($tmpFile, $path . '.jpg');
            $imagesTypes = \ImageType::getImagesTypes('products');
            foreach ($imagesTypes as $imageType) {
                \ImageManager::resize($tmpFile, $path . '-' . stripslashes($imageType['name']) . '.jpg', $imageType['width'], $imageType['height']);
                if (in_array($imageType['id_image_type'], $watermarkTypes)) {
                    \Hook::exec('actionWatermark', array('id_image' => $idImage, 'id_product' => $idEntity));
                }
            }
        } else {
            unlink($tmpFile);
            return false;
        }
        unlink($tmpFile);
        return true;
    }

    /**
     * @param $field
     * @return array
     */
    protected function createMultiLangField($field)
    {
        $res = array();
        foreach (\Language::getIDs(false) as $idLang) {
            $res[$idLang] = $field;
        }
        return $res;
    }
}
