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

namespace Alma\PrestaShop\Modules\OpartSaveCart;

use Alma\PrestaShop\Factories\CartFactory;
use Alma\PrestaShop\Factories\ModuleFactory;
use Alma\PrestaShop\Factories\ToolsFactory;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartService
{
    /**
     * @var ModuleFactory|null
     */
    protected $moduleFactory;
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

    public function __construct($moduleFactory, $opartSaveCartRepository, $toolsFactory, $cartFactory)
    {
        $this->moduleFactory = $moduleFactory;
        $this->opartSaveCartRepository = $opartSaveCartRepository;
        $this->toolsFactory = $toolsFactory;
        $this->cartFactory = $cartFactory;
    }

    /**
     * @return \Cart|null
     */
    public function getCartSaved()
    {
        if ($this->moduleFactory->isInstalled('opartsavecart')) {
            $token = $this->toolsFactory->getValue('token');
            $opartCartId = $this->opartSaveCartRepository->getIdCartByToken($token);

            if (
                !empty($opartCartId)
                && (is_string($opartCartId) || is_int($opartCartId))
            ) {
                return $this->cartFactory->create($opartCartId);
            }
        }

        return null;
    }
}
