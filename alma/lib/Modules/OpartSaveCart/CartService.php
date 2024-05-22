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

namespace Alma\PrestaShop\Modules\OpartSaveCart;

use Alma\PrestaShop\Factories\CartFactory;
use Alma\PrestaShop\Factories\ToolsFactory;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManager;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartService
{
    /**
     * @var ModuleManager|null
     */
    protected $moduleManagerBuilder;
    /**
     * @var CartRepository
     */
    protected $opartSaveCartRepository;

    /**
     * @var ToolsFactory
     */
    protected $toolsFactory;

    /**
     * @var CartFactory
     */
    protected $cartFactory;

    public function __construct(
        $moduleManagerBuilder = null,
        $opartSaveCartRepository = null
    ) {
        if (!$moduleManagerBuilder) {
            $moduleManagerBuilder = ModuleManagerBuilder::getInstance()->build();
        }
        if (!$opartSaveCartRepository) {
            $opartSaveCartRepository = new CartRepository();
        }
        $this->moduleManagerBuilder = $moduleManagerBuilder;
        $this->opartSaveCartRepository = $opartSaveCartRepository;

        $this->toolsFactory = new ToolsFactory();
        $this->cartFactory = new CartFactory();
    }

    /**
     * @return \Cart|null
     */
    public function getCartSaved()
    {
        $token = $this->toolsFactory->getValue('token');

        if ($this->moduleManagerBuilder->isInstalled('opartsavecart')) {
            $opartcart = $this->opartSaveCartRepository->getCurrentCartForOpartSaveCart($token);

            return $this->cartFactory->create($opartcart['id_cart']);
        }

        return null;
    }
}
