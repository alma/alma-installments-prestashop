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

use Alma\PrestaShop\Logger;
use Alma\PrestaShop\Traits\AjaxTrait;
use Alma\PrestaShop\Exceptions\AlmaException;
use \Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}
class AlmaInsuranceModuleFrontController extends ModuleFrontController
{
    use AjaxTrait;

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
                        $this->removeProductFromCart($context);
                        $this->ajaxRenderAndExit(json_encode(['success' => true]));
                    case 'removeAssociation' :
                        $this->removeAssociation($context);
                        $this->ajaxRenderAndExit(json_encode(['success' => true]));
                    case 'addToCartPS16' :
                     //   $this->removeAssociation($context);
                        $this->ajaxRenderAndExit(json_encode(['success' => true]));
                    default:
                        throw new AlmaException(sprintf('Action unknown : %s', Tools::getValue('action')));
                }
            }catch (\Exception $e) {
                Logger::instance()->error(sprintf(
                    'Message : %s - Trace : %s',
                    $e->getMessage(),
                    $e->getTraceAsString())
                );
                $this->ajaxRenderAndExit(json_encode(['error' => 'An error occured']), 500);
            }
        }
    }

    /**
     * @param \ContextCore $context
     * @return void
     */
    public function removeProductFromCart($context)
    {
        $idProduct = \Tools::getValue('product_id');
        $idAttribute = \Tools::getValue('attribute_id');
        $idCustomization = \Tools::getValue('customization_id') == "" ? 0 :  \Tools::getValue('customization_id') ;

        $context->cart->updateQty(
            1,
            $idProduct,
            $idAttribute,
            $idCustomization,
            'down',
            $context->cart->id_address_delivery
        );
    }

    /**
     * @param \ContextCore $context
     * @return void
     */
    public function removeAssociation($context) {
        $idAlmaInsuranceProduct = \Tools::getValue('alma_insurance_product_id');

        $almaInsuranceProductRepository = new AlmaInsuranceProductRepository();
        $almaInsuranceProductAssociation = $almaInsuranceProductRepository->getById($idAlmaInsuranceProduct);

        // Delete the association
        $almaInsuranceProductRepository->deleteById($idAlmaInsuranceProduct);

        // Remove the product
        $context->cart->updateQty(
            1,
            $almaInsuranceProductAssociation['id_product'],
            $almaInsuranceProductAssociation['id_product_attribute'],
            $almaInsuranceProductAssociation['id_customization'],
            'down',
            $context->cart->id_address_delivery
        );

        // Remove the insurance Product
        $context->cart->updateQty(
            1,
            $almaInsuranceProductAssociation['id_product_insurance'],
            $almaInsuranceProductAssociation['id_product_attribute_insurance'],
            0,
            'down',
            $context->cart->id_address_delivery
        );
    }
}
