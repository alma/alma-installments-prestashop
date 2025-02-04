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

namespace Alma\PrestaShop\Tests\Unit\Proxy;

use Alma\PrestaShop\Proxy\HelperFormProxy;
use PHPUnit\Framework\TestCase;

class HelperFormProxyTest extends TestCase
{
    /**
     * @var \Context
     */
    protected $contextMock;

    /**
     * @var \HelperForm
     */
    protected $helperFormMock;
    /**
     * @var \Alma\PrestaShop\Proxy\HelperFormProxy
     */
    protected $helperFormProxy;

    public function setUp()
    {
        $this->contextMock = $this->createMock(\Context::class);
        $this->helperFormMock = $this->createMock(\HelperForm::class);
        $this->helperFormProxy = new HelperFormProxy(
            $this->contextMock,
            $this->helperFormMock
        );
    }

    public function tearDown()
    {
        $this->contextMock = null;
        $this->helperFormMock = null;
    }

    public function testGetHelperForm()
    {
        $formFields = [
            'formfields' => true,
        ];
        $this->helperFormMock->expects($this->once())
            ->method('generateForm')
            ->willReturn('form');
        $this->assertEquals('form', $this->helperFormProxy->getHelperForm($formFields));
    }
}
