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

namespace Alma\PrestaShop\Helpers;

use Alma\PrestaShop\Exceptions\AlmaException;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\OrderStateFactory;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class OrderStateHelper
 */
class OrderStateHelper
{
    /**
     * @var int
     */
    protected $contextLanguageId;

    /**
     * @var
     */
    protected $orderStateFactory;

    /**
     * @param ContextFactory $contextFactory
     * @param OrderStateFactory $orderStateFactory
     */
    public function __construct($contextFactory, $orderStateFactory)
    {
        try {
            $this->contextLanguageId = $contextFactory->getContextLanguageId();
        } catch (AlmaException $e) {
            $this->contextLanguageId = null;
        }

        $this->orderStateFactory = $orderStateFactory;
    }

    /**
     * @param $idOrderState
     *
     * @return mixed
     *
     * @throws AlmaException
     */
    public function getNameById($idOrderState)
    {
        if (!$this->contextLanguageId) {
            throw new AlmaException('Context language id not set');
        }

        $orderStates = $this->orderStateFactory->getOrderStates($this->contextLanguageId);

        $state = array_filter($orderStates, function ($orderState) use ($idOrderState) {
            return $orderState['id_order_state'] == $idOrderState;
        });

        return array_values($state)[0]['name'];
    }
}
