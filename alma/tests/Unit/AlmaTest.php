<?php

namespace PrestaShop\Module\Alma\Tests\Unit;

use Alma;
use PHPUnit\Framework\TestCase;

class AlmaTest extends TestCase
{
    private Alma $alma;

    public function setUp(): void
    {
        $this->alma = $this->getMockBuilder(Alma::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['renderWidget'])
            ->getMock();
    }

    public function testHookDisplayProductPriceBlockWithAfterPriceTypeCallsRenderWidget(): void
    {
        $params = ['type' => 'after_price'];

        $this->alma->expects($this->once())
            ->method('renderWidget')
            ->with('alma.widget.ProductPriceBlock', $params)
            ->willReturn('widget html');

        $result = $this->alma->hookDisplayProductPriceBlock($params);

        $this->assertSame('widget html', $result);
    }

    public function testHookDisplayProductPriceBlockWithOtherTypeReturnsEmptyString(): void
    {
        $this->alma->expects($this->never())
            ->method('renderWidget');

        $this->assertSame('', $this->alma->hookDisplayProductPriceBlock(['type' => 'weight']));
        $this->assertSame('', $this->alma->hookDisplayProductPriceBlock(['type' => 'before_price']));
        $this->assertSame('', $this->alma->hookDisplayProductPriceBlock([]));
    }
}
