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

use Alma\PrestaShop\Forms\ApiAdminFormBuilder;
use Alma\PrestaShop\Forms\CartEligibilityAdminFormBuilder;
use Alma\PrestaShop\Forms\DebugAdminFormBuilder;
use Alma\PrestaShop\Forms\ExcludedCategoryAdminFormBuilder;
use Alma\PrestaShop\Forms\InpageAdminFormBuilder;
use Alma\PrestaShop\Forms\PaymentButtonAdminFormBuilder;
use Alma\PrestaShop\Forms\PaymentOnTriggeringAdminFormBuilder;
use Alma\PrestaShop\Forms\PnxAdminFormBuilder;
use Alma\PrestaShop\Forms\ProductEligibilityAdminFormBuilder;
use Alma\PrestaShop\Forms\RefundAdminFormBuilder;
use Alma\PrestaShop\Forms\ShareOfCheckoutAdminFormBuilder;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Services\AdminFormBuilderService;
use PHPUnit\Framework\TestCase;

class AdminFormBuilderServiceTest extends TestCase
{
    public function setUp()
    {
        $this->moduleMock = $this->createMock(\Module::class);
        $this->contextMock = $this->createMock(\Context::class);
        $this->pnxAdminFormBuilderMock = $this->createMock(PnxAdminFormBuilder::class);
        $this->productEligibilityAdminFormBuilderMock = $this->createMock(ProductEligibilityAdminFormBuilder::class);
        $this->cartEligibilityAdminFormBuilderMock = $this->createMock(CartEligibilityAdminFormBuilder::class);
        $this->paymentButtonAdminFormBuilderMock = $this->createMock(PaymentButtonAdminFormBuilder::class);
        $this->excludedCategoryAdminFormBuilderMock = $this->createMock(ExcludedCategoryAdminFormBuilder::class);
        $this->refundAdminFormBuilderMock = $this->createMock(RefundAdminFormBuilder::class);
        $this->shareOfCheckoutAdminFormBuilderMock = $this->createMock(ShareOfCheckoutAdminFormBuilder::class);
        $this->inpageAdminFormBuilderMock = $this->createMock(InpageAdminFormBuilder::class);
        $this->paymentOnTriggeringAdminFormBuilderMock = $this->createMock(PaymentOnTriggeringAdminFormBuilder::class);
        $this->apiAdminFormBuilderMock = $this->createMock(ApiAdminFormBuilder::class);
        $this->debugAdminFormBuilderMock = $this->createMock(DebugAdminFormBuilder::class);
        $this->settingsHelperMock = $this->createMock(SettingsHelper::class);
        $this->adminFormBuilderService = new AdminFormBuilderService(
            $this->moduleMock,
            $this->contextMock,
            true,
            $this->pnxAdminFormBuilderMock,
            $this->productEligibilityAdminFormBuilderMock,
            $this->cartEligibilityAdminFormBuilderMock,
            $this->paymentButtonAdminFormBuilderMock,
            $this->excludedCategoryAdminFormBuilderMock,
            $this->refundAdminFormBuilderMock,
            $this->shareOfCheckoutAdminFormBuilderMock,
            $this->inpageAdminFormBuilderMock,
            $this->paymentOnTriggeringAdminFormBuilderMock,
            $this->apiAdminFormBuilderMock,
            $this->debugAdminFormBuilderMock,
            $this->settingsHelperMock
        );
    }

    public function testGetFormFieldsWithoutApiKeySaved()
    {
        $this->pnxAdminFormBuilderMock->expects($this->never())
            ->method('build');
        $this->productEligibilityAdminFormBuilderMock->expects($this->never())
            ->method('build');
        $this->cartEligibilityAdminFormBuilderMock->expects($this->never())
            ->method('build');
        $this->paymentButtonAdminFormBuilderMock->expects($this->never())
            ->method('build');
        $this->excludedCategoryAdminFormBuilderMock->expects($this->never())
            ->method('build');
        $this->refundAdminFormBuilderMock->expects($this->never())
            ->method('build');
        $this->shareOfCheckoutAdminFormBuilderMock->expects($this->never())
            ->method('build');
        $this->inpageAdminFormBuilderMock->expects($this->never())
            ->method('build');
        $this->paymentOnTriggeringAdminFormBuilderMock->expects($this->never())
            ->method('build');
        $this->apiAdminFormBuilderMock->expects($this->once())
            ->method('build');
        $this->debugAdminFormBuilderMock->expects($this->once())
            ->method('build');
        $this->adminFormBuilderService->getFormFields(true);
    }

    public function testGetFormFieldsWithApiKeyAndWithoutSocAndPut()
    {
        $this->pnxAdminFormBuilderMock->expects($this->once())
            ->method('build');
        $this->productEligibilityAdminFormBuilderMock->expects($this->once())
            ->method('build');
        $this->cartEligibilityAdminFormBuilderMock->expects($this->once())
            ->method('build');
        $this->paymentButtonAdminFormBuilderMock->expects($this->once())
            ->method('build');
        $this->excludedCategoryAdminFormBuilderMock->expects($this->once())
            ->method('build');
        $this->refundAdminFormBuilderMock->expects($this->once())
            ->method('build');
        $this->settingsHelperMock->expects($this->once())
            ->method('shouldDisplayShareOfCheckoutForm')
            ->willReturn(false);
        $this->shareOfCheckoutAdminFormBuilderMock->expects($this->never())
            ->method('build');
        $this->inpageAdminFormBuilderMock->expects($this->once())
            ->method('build');
        $this->settingsHelperMock->expects($this->once())
            ->method('isPaymentTriggerEnabledByState')
            ->willReturn(false);
        $this->paymentOnTriggeringAdminFormBuilderMock->expects($this->never())
            ->method('build');
        $this->apiAdminFormBuilderMock->expects($this->once())
            ->method('build');
        $this->debugAdminFormBuilderMock->expects($this->once())
            ->method('build');
        $this->adminFormBuilderService->getFormFields(false);
    }

    public function testGetFormFieldsWithApiKeyAndWithSocAndPut()
    {
        $this->pnxAdminFormBuilderMock->expects($this->once())
            ->method('build');
        $this->productEligibilityAdminFormBuilderMock->expects($this->once())
            ->method('build');
        $this->cartEligibilityAdminFormBuilderMock->expects($this->once())
            ->method('build');
        $this->paymentButtonAdminFormBuilderMock->expects($this->once())
            ->method('build');
        $this->excludedCategoryAdminFormBuilderMock->expects($this->once())
            ->method('build');
        $this->refundAdminFormBuilderMock->expects($this->once())
            ->method('build');
        $this->settingsHelperMock->expects($this->once())
            ->method('shouldDisplayShareOfCheckoutForm')
            ->willReturn(true);
        $this->shareOfCheckoutAdminFormBuilderMock->expects($this->once())
            ->method('build');
        $this->inpageAdminFormBuilderMock->expects($this->once())
            ->method('build');
        $this->settingsHelperMock->expects($this->once())
            ->method('isPaymentTriggerEnabledByState')
            ->willReturn(true);
        $this->paymentOnTriggeringAdminFormBuilderMock->expects($this->once())
            ->method('build');
        $this->apiAdminFormBuilderMock->expects($this->once())
            ->method('build');
        $this->debugAdminFormBuilderMock->expects($this->once())
            ->method('build');
        $this->adminFormBuilderService->getFormFields(false);
    }
}
