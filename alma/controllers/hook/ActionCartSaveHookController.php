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

if (!defined('_PS_VERSION_')) {
    exit;
}

use Alma\PrestaShop\Exceptions\InsuranceNotFoundException;
use Alma\PrestaShop\Helpers\InsuranceHelper;
use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Repositories\ProductRepository;
use Alma\PrestaShop\Helpers\ConstantsHelper;
use Alma\PrestaShop\Repositories\AttributeGroupRepository;
use Alma\PrestaShop\Repositories\AttributeRepository;

class ActionCartSaveHookController extends FrontendHookController
{

    /**
     * @var \Alma\PrestaShop\Repositories\ProductRepository
     */
    protected $productRepository;

    /**
     * @var AttributeGroupRepository
     */
    protected $attributeGroupRepository;

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var AlmaInsuranceProductRepository
     */
    protected $almaInsuranceProductRepository;

    /**
     * @var InsuranceHelper
     */
    protected $insuranceHelper;

    public function __construct($module)
    {
        parent::__construct($module);

        $this->productRepository = new ProductRepository();
        $this->attributeGroupRepository = new AttributeGroupRepository();
        $this->attributeRepository = new AttributeRepository();
        $this->almaInsuranceProductRepository = new AlmaInsuranceProductRepository();
        $this->insuranceHelper = new InsuranceHelper();
    }

    /**
     * Run Controller
     *
     * @param array $params
     *
     * @return void
     */
    public function run($params)
    {
        if(
            isset($_POST['alma_insurance_price'])
            && $_POST['alma_insurance_price'] != 'none'
        ) {
            $this->addInsuranceToCart($_POST['alma_insurance_price']);
        }

        // @todo suppression
    }

    protected function addInsuranceToCart($insuranceData)
    {
        // @todo Check elibilibilty

        // This code is temporary until the product widget is done
        $values = explode('-', $insuranceData);
        $insurancePrice = $values[0];
        $insuranceName = $values[1];
        // !!

        // Get the default product insurance
        $defaultInsuranceProduct = $this->insuranceHelper->getDefaultInsuranceProduct($this->context->language->id);
        // Get the default group attribute id
        $attributeGroupId =  $this->insuranceHelper->getInsuranceAttributeGroupId($this->context->language->id);
        // Get or create the attribute (type of insurance choose)
        $insuranceAttributeId = $this->insuranceHelper->createOrGetInsuranceAttributeId(
            $insuranceName,
            $attributeGroupId,
            $this->context->language->id
        );

        // Create the combination of product + attribute
        $combination = $this->insuranceHelper->createOrGetInsuranceCombination(
            $defaultInsuranceProduct,
            $insuranceName,
            $insurancePrice,
            $insuranceAttributeId
        );

        \StockAvailableCore::setQuantity($defaultInsuranceProduct->id, $combination->id, 1, $this->context->shop->id);

        // We reset the data
        $_POST['alma_insurance_price']  = 'none';

        // Add the insurance to the cart
        $this->context->cart->updateQty(1, $defaultInsuranceProduct->id, $combination->id);

        $product = $this->context->cart->getLastProduct();

        $this->almaInsuranceProductRepository->add(
            $this->context->cart->id,
            $product['id_product'],
            $this->context->shop->id,
            $product['id_product_attribute'],
            $_POST['id_customization'],
            $defaultInsuranceProduct->id,
            $insuranceAttributeId,
            $insurancePrice
        );
    }
}
