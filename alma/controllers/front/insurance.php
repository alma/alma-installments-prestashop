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

use Alma\PrestaShop\Builders\Repositories\InsuranceProductRepositoryBuilder;
use Alma\PrestaShop\Builders\Services\InsuranceProductServiceBuilder;
use Alma\PrestaShop\Exceptions\AlmaException;
use Alma\PrestaShop\Factories\LoggerFactory;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Services\InsuranceProductService;
use Alma\PrestaShop\Traits\AjaxTrait;

if (!defined('_PS_VERSION_')) {
    exit;
}
class AlmaInsuranceModuleFrontController extends ModuleFrontController
{
    use AjaxTrait;

    /**
     * @var InsuranceProductService
     */
    protected $insuranceProductService;
    /**
     * @var AlmaInsuranceProductRepository
     */
    protected $almaInsuranceProductRepository;

    public function __construct()
    {
        parent::__construct();

        $insuranceProductServiceBuilder = new InsuranceProductServiceBuilder();
        $this->insuranceProductService = $insuranceProductServiceBuilder->getInstance();

        $almaInsuranceProductRepository = new InsuranceProductRepositoryBuilder();
        $this->almaInsuranceProductRepository = $almaInsuranceProductRepository->getInstance();
    }

    /**
     * @throws PrestaShopException
     */
    public function initContent()
    {
        if (Tools::isSubmit('action')) {
            header('Content-Type: application/json');

            if (!$this->isTokenValid()) {
                // Ooops! Token is not valid!
                $this->ajaxRenderAndExit(json_encode(['error' => 'Invalid Token']), 401);
            }

            try {
                /**
                 * @var \ContextCore $context
                 */
                $context = \Context::getContext();

                switch (Tools::getValue('action')) {
                    case 'removeProductFromCart':
                        $this->ajaxRemoveProductFromCart($context);
                        break;
                    case 'removeAssociation':
                        $this->ajaxRemoveAssociationAndProducts($context);
                        break;
                    case 'removeAssociations':
                        $this->ajaxRemoveAssociationsAndProducts($context);
                        break;
                    case 'removeInsuranceProduct':
                        $this->ajaxRemoveInsuranceProductAndAssociation($context);
                        break;
                    case 'removeInsuranceProducts':
                        $this->ajaxRemoveInsuranceProductsAndAssociations($context);
                        break;
                    case 'addInsuranceProduct':
                        $this->ajaxAddInsuranceProductAndAssociation();
                        break;
                    default:
                        throw new AlmaException(sprintf('Action unknown : %s', Tools::getValue('action')));
                }
            } catch (\Exception $e) {
                LoggerFactory::instance()->error(sprintf(
                    'Message : %s - Trace : %s',
                    $e->getMessage(),
                    $e->getTraceAsString())
                );
                $this->ajaxRenderAndExit(json_encode(['error' => 'An error occurred']), 500);
            }
        }
    }

    /**
     * @param \ContextCore $context
     *
     * @return void
     *
     * @throws PrestaShopException
     */
    public function ajaxRemoveProductFromCart($context)
    {
        $idProduct = \Tools::getValue('product_id');
        $idAttribute = \Tools::getValue('attribute_id');
        $idCustomization = \Tools::getValue('customization_id') == '' ? 0 : \Tools::getValue('customization_id');

        $this->decreaseCartFromOneProduct(
            $context,
            $idProduct,
            $idAttribute,
            $idCustomization
        );

        $this->ajaxRenderAndExit(json_encode(['success' => true]));
    }

    /**
     * @param \ContextCore $context
     *
     * @return void
     *
     * @throws PrestaShopException
     */
    public function ajaxRemoveAssociationAndProducts($context)
    {
        $idAlmaInsuranceProduct = \Tools::getValue('alma_insurance_product_id');

        $this->removeAssociationAndDecreaseProductInCart($context, $idAlmaInsuranceProduct);

        $this->ajaxRenderAndExit(json_encode(['success' => true]));
    }

    /**
     * @param $context
     *
     * @return void
     *
     * @throws PrestaShopException
     */
    public function ajaxRemoveAssociationsAndProducts($context)
    {
        $idsAlmaInsuranceProduct = json_decode(\Tools::getValue('alma_insurance_product_ids'));

        foreach ($idsAlmaInsuranceProduct as $idAlmaInsuranceProduct) {
            $this->removeAssociationAndDecreaseProductInCart($context, $idAlmaInsuranceProduct);
        }

        $this->ajaxRenderAndExit(json_encode(['success' => true]));
    }

    /**
     * @param \ContextCore $context
     *
     * @return void
     *
     * @throws PrestaShopException
     */
    public function ajaxRemoveInsuranceProductAndAssociation($context)
    {
        $idAlmaInsuranceProduct = \Tools::getValue('alma_insurance_product_id');

        $this->removeInsuranceProductAndAssociation($context, $idAlmaInsuranceProduct);

        $this->ajaxRenderAndExit(json_encode(['success' => true]));
    }

    /**
     * @param \ContextCore $context
     *
     * @return void
     *
     * @throws PrestaShopException
     */
    public function ajaxRemoveInsuranceProductsAndAssociations($context)
    {
        $idsAlmaInsuranceProduct = json_decode(\Tools::getValue('alma_insurance_product_ids'));

        foreach ($idsAlmaInsuranceProduct as $idAlmaInsuranceProduct) {
            $this->removeInsuranceProductAndAssociation($context, $idAlmaInsuranceProduct);
        }

        $this->ajaxRenderAndExit(json_encode(['success' => true]));
    }

    /**
     * @return void
     *
     * @throws PrestaShopException
     */
    public function ajaxAddInsuranceProductAndAssociation()
    {
        $this->addInsuranceProductAndAssociation();

        $this->ajaxRenderAndExit(json_encode(['success' => true]));
    }

    /**
     * @param ContextCore $context
     * @param $idAlmaInsuranceProduct
     *
     * @return array
     */
    protected function removeInsuranceProductAndAssociation($context, $idAlmaInsuranceProduct)
    {
        // Delete the association
        $associationData = $this->removeAssociation($idAlmaInsuranceProduct);

        // Remove the insurance Product
        $this->removeInsuranceProduct($context, $associationData);

        return $associationData;
    }

    /**
     * @param int $idAlmaInsuranceProduct
     *
     * @return array
     */
    protected function removeAssociation($idAlmaInsuranceProduct)
    {
        $almaInsuranceProductAssociation = $this->almaInsuranceProductRepository->getById($idAlmaInsuranceProduct);

        // Delete the association
        $this->almaInsuranceProductRepository->deleteById($idAlmaInsuranceProduct);

        return $almaInsuranceProductAssociation;
    }

    /**
     * @param ContextCore $context
     * @param array $product
     *
     * @return void
     */
    protected function removeInsuranceProduct($context, $product)
    {
        $this->decreaseCartFromOneProduct(
            $context,
            $product['id_product_insurance'],
            $product['id_product_attribute_insurance']
        );
    }

    /**
     * @param \ContextCore $context
     * @param int $idProduct
     * @param int $idProductAttribute
     * @param int $idCustomization
     *
     * @return void
     */
    protected function decreaseCartFromOneProduct($context, $idProduct, $idProductAttribute, $idCustomization = 0)
    {
        // Decrease one quantity of the product
        $context->cart->updateQty(
            1,
            $idProduct,
            $idProductAttribute,
            $idCustomization,
            'down',
            $context->cart->id_address_delivery
        );
    }

    /**
     * @return void
     */
    protected function addInsuranceProductAndAssociation()
    {
        $this->insuranceProductService->addInsuranceProductInPsCart(
            \Tools::getValue('product_id'),
            \Tools::getValue('insurance_contract_id'),
            \Tools::getValue('insurance_quantity'),
            \Tools::getValue('customization_id')
        );
    }

    /**
     * @param $context
     * @param $idAlmaInsuranceProduct
     *
     * @return void
     */
    protected function removeAssociationAndDecreaseProductInCart($context, $idAlmaInsuranceProduct)
    {
        $almaInsuranceProductAssociation = $this->removeInsuranceProductAndAssociation($context, $idAlmaInsuranceProduct);

        // Remove the product
        $this->decreaseCartFromOneProduct(
            $context,
            $almaInsuranceProductAssociation['id_product'],
            $almaInsuranceProductAssociation['id_product_attribute'],
            $almaInsuranceProductAssociation['id_customization']
        );
    }
}
