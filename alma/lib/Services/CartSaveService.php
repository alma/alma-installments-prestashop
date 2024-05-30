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

use Alma\PrestaShop\Helpers\ModuleHelper;
use Alma\PrestaShop\Repositories\CartSaveRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartSaveService
{
    /**
     * @var ModuleHelper
     */
    protected $moduleHelper;
    /**
     * @var CartSaveRepository
     */
    protected $cartSaveRepository;

    public function __construct(
        $moduleHelper = null,
        $cartSaveRepository = null
    ) {
        if (!$moduleHelper) {
            $moduleHelper = new ModuleHelper();
        }
        if (!$cartSaveRepository) {
            $cartSaveRepository = new CartSaveRepository();
        }
        $this->moduleHelper = $moduleHelper;
        $this->cartSaveRepository = $cartSaveRepository;
    }

    /**
     * @param $value
     *
     * @return int|null
     */
    public function getIdCartSaved($value)
    {
        if ($this->moduleHelper->isInstalled('opartsavecart')) {
            $cart = $this->cartSaveRepository->getCurrentCartForOpartSaveCart($value);
            if ($cart) {
                return (int) $cart['id_cart'];
            }
        }

        return null;
    }
}
