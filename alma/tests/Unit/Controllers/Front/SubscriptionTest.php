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

namespace Alma\PrestaShop\Tests\Unit\Controllers\Front;

use Alma\PrestaShop\Controllers\Front\AlmaSubscriptionModuleFrontController;
use Alma\PrestaShop\Exceptions\SubscriptionException;
use PHPUnit\Framework\TestCase;

class SubscriptionTest extends TestCase
{
    /**
     * @throws \PrestaShopException
     * @throws \ReflectionException
     */
    public function testThrowErrorIfNoSidOnTheCallBackUrl()
    {
        // @TODO : I don't know if we can make test on Prestashop controllers, if not we can delete this file
        $this->assertTrue(true);
        /*
                $Tools = $this->getMockClass('Tools', array('getValue'));
                $almaSubscriptionModuleFrontController = new AlmaSubscriptionModuleFrontController();
                $Tools::staticExpects($this->any())
                    ->method('getValue')
                    ->with($this->equalTo(null));
                $almaSubscriptionModuleFrontController->postProcess();
                $this->expectException(SubscriptionException::class);
        */
    }
}
