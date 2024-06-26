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

namespace Alma\PrestaShop\Controllers\Hook;

use Alma\PrestaShop\Builders\Helpers\PriceHelperBuilder;
use Alma\PrestaShop\Exceptions\InsuranceNotFoundException;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Helpers\ImageHelper;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Helpers\PriceHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Repositories\ProductRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class DisplayCartExtraProductActionsHookController extends FrontendHookController
{
    /** @var Alma */
    protected $module;

    /**
     * @var InsuranceHelper
     */
    protected $insuranceHelper;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var AlmaInsuranceProductRepository
     */
    protected $almaInsuranceProductRepository;
    /**
     * @var ImageHelper
     */
    protected $imageHelper;
    /**
     * @var \Link
     */
    protected $link;

    /*
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @param $module
     */
    public function __construct($module)
    {
        $this->insuranceHelper = new InsuranceHelper();
        $this->productRepository = new ProductRepository();
        $this->almaInsuranceProductRepository = new AlmaInsuranceProductRepository();
        $this->imageHelper = new ImageHelper();
        $priceHelperBuilder = new PriceHelperBuilder();
        $this->priceHelper = $priceHelperBuilder->getInstance();

        $this->link = new \Link();
        parent::__construct($module);
    }

    /**
     * @return bool
     */
    public function canRun()
    {
        return parent::canRun()
            && $this->insuranceHelper->isInsuranceAllowedInProductPage();
    }

    /**
     * @param $params
     *
     * @return mixed
     *
     * @throws InsuranceNotFoundException
     * @throws \PrestaShopDatabaseException
     */
    public function run($params)
    {
        /**
         * @var \ProductCore $product
         */
        $product = $params['product'];

        /**
         * @var \CartCore $cart
         */
        $cart = $params['cart'];

        $insuranceProductId = $this->productRepository->getProductIdByReference(
            ConstantsHelper::ALMA_INSURANCE_PRODUCT_REFERENCE,
            $this->context->language->id
        );

        if (!$insuranceProductId) {
            throw new InsuranceNotFoundException();
        }

        $resultInsurance = [];

        $idProduct = $product->id;
        $productQuantity = $product->quantity;
        $template = 'displayCartExtraProductActions.tpl';

        if ($idProduct !== $insuranceProductId) {
            $almaInsurances = $this->almaInsuranceProductRepository->getIdsByCartIdAndShopAndProduct(
                $product,
                $cart->id,
                $this->context->shop->id
            );

            foreach ($almaInsurances as $almaInsurance) {
                $almaInsuranceProduct = new \ProductCore((int) $almaInsurance['id_product_insurance']);
                $almaProductAttribute = new \CombinationCore((int) $almaInsurance['id_product_attribute_insurance']);
                $idImage = $almaInsuranceProduct->getImages($this->context->language->id)[0]['id_image'];
                $linkRewrite = $almaInsuranceProduct->link_rewrite[$this->context->language->id];
                $resultInsurance[$almaInsurance['id_alma_insurance_product']] = [
                    'insuranceProduct' => $almaInsuranceProduct,
                    'insuranceProductAttribute' => $almaProductAttribute,
                    'price' => $this->priceHelper->convertPriceFromCents($almaInsurance['price']),
                    'name' => $almaInsuranceProduct->name[$this->context->language->id],
                    'urlImage' => '//' . $this->link->getImageLink(
                        $linkRewrite,
                        $idImage,
                        $this->imageHelper->getFormattedImageTypeName('cart')
                    ),
                ];
            }
        }

        $ajaxLinkRemoveProduct = $this->link->getModuleLink('alma', 'insurance', ['action' => 'removeProductFromCart']);
        $ajaxLinkRemoveAssociation = $this->link->getModuleLink('alma', 'insurance', ['action' => 'removeAssociation']);
        $ajaxLinkRemoveInsuranceProduct = $this->link->getModuleLink('alma', 'insurance', ['action' => 'removeInsuranceProduct']);

        $this->context->smarty->assign([
                'idCart' => $cart->id,
                'idLanguage' => $this->context->language->id,
                'nbProductWithoutInsurance' => $productQuantity - count($resultInsurance),
                'product' => $product,
                'associatedInsurances' => $resultInsurance,
                'isAlmaInsurance' => $idProduct === $insuranceProductId ? 1 : 0,
                'ajaxLinkAlmaRemoveProduct' => $ajaxLinkRemoveProduct,
                'ajaxLinkAlmaRemoveAssociation' => $ajaxLinkRemoveAssociation,
                'ajaxLinkRemoveInsuranceProduct' => $ajaxLinkRemoveInsuranceProduct,
                'token' => \Tools::getToken(false),
                'idProduct' => $idProduct,
            ]);

        return $this->module->display($this->module->file, $template);
    }
}
