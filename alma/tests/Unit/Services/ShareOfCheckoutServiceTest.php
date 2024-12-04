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

namespace Alma\PrestaShop\Tests\Unit\Services;

use Alma\PrestaShop\Exceptions\ShareOfCheckoutException;
use Alma\PrestaShop\Forms\ShareOfCheckoutAdminFormBuilder;
use Alma\PrestaShop\Helpers\ShareOfCheckoutHelper;
use Alma\PrestaShop\Model\AlmaApiKeyModel;
use Alma\PrestaShop\Services\ShareOfCheckoutService;
use PHPUnit\Framework\TestCase;

class ShareOfCheckoutServiceTest extends TestCase
{
    /**
     * @var \Alma\PrestaShop\Helpers\ShareOfCheckoutHelper|(\Alma\PrestaShop\Helpers\ShareOfCheckoutHelper&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shareOfCheckoutHelperMock;
    /**
     * @var \Alma\PrestaShop\Model\AlmaApiKeyModel|(\Alma\PrestaShop\Model\AlmaApiKeyModel&\PHPUnit_Framework_MockObject_MockObject)|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $almaApiKeyModelMock;

    public function setUp()
    {
        $this->shareOfCheckoutHelperMock = $this->createMock(ShareOfCheckoutHelper::class);
        $this->almaApiKeyModelMock = $this->createMock(AlmaApiKeyModel::class);
        $this->shareOfCheckoutService = new ShareOfCheckoutService(
            $this->shareOfCheckoutHelperMock,
            $this->almaApiKeyModelMock
        );
    }

    /**
     * @throws \Alma\PrestaShop\Exceptions\ShareOfCheckoutException
     */
    public function testHandleConsentResetSoCIfKeyIsNotSame()
    {
        $this->almaApiKeyModelMock->method('isSameLiveApiKeySaved')
            ->willReturn(false);
        $this->shareOfCheckoutHelperMock->expects($this->once())
            ->method('resetShareOfCheckoutConsent');
        $this->shareOfCheckoutService->handleConsent();
    }

    public function testHandleConsentResetSoCIfKeyIsNotSameAndThrowException()
    {
        $this->almaApiKeyModelMock->method('isSameLiveApiKeySaved')
            ->willReturn(false);
        $this->shareOfCheckoutHelperMock->expects($this->once())
            ->method('resetShareOfCheckoutConsent')
            ->willThrowException(new ShareOfCheckoutException());
        $this->expectException(ShareOfCheckoutException::class);
        $this->shareOfCheckoutService->handleConsent();
    }

    public function testHandleConsentWithSameApiKeyAndShareOfCheckoutAlreadyAnsweredAndSameModeApi()
    {
        $this->almaApiKeyModelMock->method('isSameLiveApiKeySaved')
            ->willReturn(true);
        $this->shareOfCheckoutHelperMock->expects($this->never())
            ->method('resetShareOfCheckoutConsent');
        $this->shareOfCheckoutHelperMock->method('isShareOfCheckoutAnswered')
            ->willReturn(true);
        $this->almaApiKeyModelMock->method('isSameModeSaved')
            ->willReturn(true);
        $this->shareOfCheckoutHelperMock->expects($this->once())
            ->method('handleCheckoutConsent')
            ->with(ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_STATE . '_ON');
        $this->shareOfCheckoutService->handleConsent();
    }

    public function testHandleConsentWithSameApiKeyAndShareOfCheckoutAlreadyAnsweredAndNotSameModeApi()
    {
        $this->almaApiKeyModelMock->method('isSameLiveApiKeySaved')
            ->willReturn(true);
        $this->shareOfCheckoutHelperMock->expects($this->never())
            ->method('resetShareOfCheckoutConsent');
        $this->shareOfCheckoutHelperMock->method('isShareOfCheckoutAnswered')
            ->willReturn(true);
        $this->almaApiKeyModelMock->method('isSameModeSaved')
            ->willReturn(false);
        $this->shareOfCheckoutHelperMock->expects($this->never())
            ->method('handleCheckoutConsent')
            ->with(ShareOfCheckoutAdminFormBuilder::ALMA_SHARE_OF_CHECKOUT_STATE . '_ON');
        $this->shareOfCheckoutService->handleConsent();
    }
}
