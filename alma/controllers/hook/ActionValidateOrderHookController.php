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

use Alma\PrestaShop\Hooks\FrontendHookController;
use Alma\PrestaShop\Repositories\AlmaInsuranceProductRepository;
use Alma\PrestaShop\Repositories\AttributeGroupRepository;
use Alma\PrestaShop\Repositories\AttributeRepository;

class ActionValidateOrderHookController extends FrontendHookController
{
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

    public function __construct($module)
    {
        parent::__construct($module);
        $this->almaInsuranceProductRepository = new AlmaInsuranceProductRepository();
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
      /**  $order = $params['order'];
        $cart = $params['cart'];

        $ids = $this->almaInsuranceProductRepository->getIdsByCartIdAndShop($cart->id, $this->context->shop->id);

        $idsToUpdate = [];

        foreach ($ids as $data) {
            $idsToUpdate[] = $data['id'];
        }

        if (count($ids) > 0) {
            $this->almaInsuranceProductRepository->updateAssociationsOrderId($order->id, $idsToUpdate);
        }**/
    }
}
