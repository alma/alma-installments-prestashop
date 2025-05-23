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

namespace Alma\PrestaShop\Tests\Unit\Helper;

use Alma\PrestaShop\Builders\Helpers\ContextHelperBuilder;
use Alma\PrestaShop\Exceptions\AlmaException;
use PHPUnit\Framework\TestCase;

class ContextHelperTest extends TestCase
{
    /**
     * @var \Alma\PrestaShop\Helpers\ContextHelper
     */
    protected $contextHelper;
    /**
     * @var \Link
     */
    protected $contextLinkMock;

    public function setUp()
    {
        $this->contextLinkMock = $this->createMock(\Link::class);
        $contextHelperBuilder = new ContextHelperBuilder();
        $this->contextHelper = $contextHelperBuilder->getInstance();
    }

    public function tearDown()
    {
        $this->contextHelper = null;
        $this->contextLinkMock = null;
    }

    public function testGetModuleLinkReturnLink()
    {
        $this->contextHelper->setContextLink($this->contextLinkMock);
        $base = $this->getBase(true, false);
        $this->contextLinkMock->method('getModuleLink')
            ->willReturn($base . 'module/alma/payment?key=general_1_0_0');
        $result = $this->contextHelper->getModuleLink(
            'payment',
            ['key' => 'general_1_0_0'],
            true,
            null,
            null,
            false
        );

        $this->assertEquals($base . 'module/alma/payment?key=general_1_0_0', $result);
    }

    public function testGetModuleLinkWithoutContextLinkAndThrowException()
    {
        $this->contextLinkMock = null;
        $this->contextHelper->setContextLink($this->contextLinkMock);
        $this->expectException(AlmaException::class);
        $this->contextHelper->getModuleLink(
            'payment',
            ['key' => 'general_1_0_0'],
            false,
            1,
            1,
            true
        );
    }

    /**
     * @param bool|null $ssl
     * @param bool $relativeProtocol
     *
     * @return string
     */
    protected function getBase($ssl = null, $relativeProtocol = false)
    {
        $shop = \Context::getContext()->shop;
        $sslEnabled = \Configuration::get('PS_SSL_ENABLED');

        if ($relativeProtocol) {
            $base = '//' . ($ssl && $sslEnabled ? $shop->domain_ssl : $shop->domain);
        } else {
            $base = (($ssl && $sslEnabled) ? 'https://' . $shop->domain_ssl : 'http://' . $shop->domain);
        }

        return $base . $shop->getBaseURI();
    }
}
