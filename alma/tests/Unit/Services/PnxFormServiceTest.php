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

use Alma\API\Entities\FeePlan;
use Alma\PrestaShop\Exceptions\PnxFormException;
use Alma\PrestaShop\Helpers\FeePlanHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Model\ClientModel;
use Alma\PrestaShop\Model\FeePlanModel;
use Alma\PrestaShop\Proxy\ConfigurationProxy;
use Alma\PrestaShop\Proxy\ToolsProxy;
use Alma\PrestaShop\Services\PnxFormService;
use PHPUnit\Framework\TestCase;

class PnxFormServiceTest extends TestCase
{
    /**
     * @var \Alma\PrestaShop\Proxy\ConfigurationProxy
     */
    private $configurationProxyMock;
    /**
     * @var \Alma\PrestaShop\Proxy\ToolsProxy
     */
    private $toolsProxyMock;
    /**
     * @var \Alma\PrestaShop\Model\ClientModel
     */
    private $clientModelMock;
    /**
     * @var \Alma\PrestaShop\Helpers\SettingsHelper
     */
    private $settingsHelperMock;
    /**
     * @var \Alma\PrestaShop\Helpers\FeePlanHelper
     */
    private $feePlanHelperMock;
    /**
     * @var \Alma\PrestaShop\Model\FeePlanModel
     */
    private $feePlanModelMock;
    /**
     * @var \Alma\API\Entities\FeePlan
     */
    private $feePlanMock;

    public function setUp()
    {
        $this->clientModelMock = $this->createMock(ClientModel::class);
        $this->settingsHelperMock = $this->createMock(SettingsHelper::class);
        $this->configurationProxyMock = $this->createMock(ConfigurationProxy::class);
        $this->toolsProxyMock = $this->createMock(ToolsProxy::class);
        $this->feePlanHelperMock = $this->createMock(FeePlanHelper::class);
        $this->feePlanModelMock = $this->createMock(FeePlanModel::class);
        $this->customFieldsFormService = new PnxFormService(
            $this->clientModelMock,
            $this->settingsHelperMock,
            $this->configurationProxyMock,
            $this->toolsProxyMock,
            $this->feePlanHelperMock,
            $this->feePlanModelMock
        );
    }

    /**
     * @throws \Alma\PrestaShop\Exceptions\PnxFormException
     */
    public function testSaveWithApiOnly()
    {
        $feePlans = [
            $this->planPayNow(),
            $this->planP2x(),
            $this->planP3x(),
            $this->planP4x(),
            $this->planDeferred15(),
        ];

        $feePlanSaved = [
            'general_3_0_0' => [
                'enabled' => 1,
                'min' => 5000,
                'max' => 200000,
                'deferred_trigger_limit_days' => null,
                'order' => 1,
            ],
        ];

        $this->toolsProxyMock->expects($this->exactly(2))
            ->method('getValue')
            ->with('_api_only')
            ->willReturn(true);
        $this->clientModelMock->expects($this->once())
            ->method('getMerchantFeePlans')
            ->willReturn($feePlans);
        // Executed 3 times because we break when the p3x is found
        $this->settingsHelperMock->expects($this->once())
            ->method('isDeferred')
            ->with($this->planP3x())
            ->willReturn(false);
        $this->settingsHelperMock->expects($this->once())
            ->method('keyForFeePlan')
            ->with($this->planP3x())
            ->willReturn('general_3_0_0');
        $this->configurationProxyMock->expects($this->once())
            ->method('updateValue')
            ->with('ALMA_FEE_PLANS', json_encode($feePlanSaved));

        $this->customFieldsFormService->save();
    }

    /**
     * @throws \Alma\PrestaShop\Exceptions\PnxFormException
     */
    public function testSaveWithoutApiOnlyThrowExceptionForUpdateFeePlans()
    {
        $feePlans = [
            $this->planPayNow(),
            $this->planP2x(),
            $this->planP3x(),
            $this->planP4x(),
            $this->planDeferred15(),
        ];

        $this->toolsProxyMock->expects($this->exactly(2))
            ->method('getValue')
            ->with('_api_only')
            ->willReturn(false);
        $this->clientModelMock->expects($this->once())
            ->method('getMerchantFeePlans')
            ->willReturn($feePlans);
        $this->feePlanHelperMock->expects($this->once())
            ->method('checkLimitsSaveFeePlans')
            ->with($feePlans)
            ->willThrowException(new PnxFormException('Error'));
        $this->expectException(PnxFormException::class);
        $this->customFieldsFormService->save();
    }

    /**
     * @throws \Alma\PrestaShop\Exceptions\PnxFormException
     */
    public function testSaveWithoutApiOnlyAndUpdateFeePlans()
    {
        $feePlans = [
            $this->planPayNow(),
            $this->planP2x(),
            $this->planP3x(),
            $this->planP4x(),
            $this->planDeferred15(),
        ];

        $feePlanToSave = [
            'general_1_0_0' => [
                'enabled' => 1,
                'min' => 5000,
                'max' => 200000,
                'deferred_trigger_limit_days' => null,
                'order' => 1,
            ],
            'general_2_0_0' => [
                'enabled' => 1,
                'min' => 5000,
                'max' => 200000,
                'deferred_trigger_limit_days' => null,
                'order' => 2,
            ],
            'general_3_0_0' => [
                'enabled' => 1,
                'min' => 5000,
                'max' => 200000,
                'deferred_trigger_limit_days' => null,
                'order' => 2,
            ],
            'general_4_0_0' => [
                'enabled' => 1,
                'min' => 5000,
                'max' => 200000,
                'deferred_trigger_limit_days' => null,
                'order' => 3,
            ],
            'general_1_15_0' => [
                'enabled' => 1,
                'min' => 5000,
                'max' => 200000,
                'deferred_trigger_limit_days' => null,
                'order' => 4,
            ],
        ];

        $this->toolsProxyMock->expects($this->exactly(2))
            ->method('getValue')
            ->with('_api_only')
            ->willReturn(false);
        $this->clientModelMock->expects($this->once())
            ->method('getMerchantFeePlans')
            ->willReturn($feePlans);
        $this->feePlanHelperMock->expects($this->once())
            ->method('checkLimitsSaveFeePlans')
            ->with($feePlans);
        $this->feePlanModelMock->expects($this->once())
            ->method('getFeePlanForSave')
            ->with($feePlans)
            ->willReturn($feePlanToSave);
        $this->configurationProxyMock->expects($this->once())
            ->method('updateValue')
            ->with('ALMA_FEE_PLANS', json_encode($feePlanToSave));
        $this->customFieldsFormService->save();
    }

    protected function planPayNow()
    {
        $plan = $this->createMock(FeePlan::class);

        $plan->installments_count = 1;
        $plan->kind = 'general';
        $plan->deferred_months = 0;
        $plan->deferred_days = 0;
        $plan->deferred_trigger_limit_days = null;
        $plan->max_purchase_amount = 200000;
        $plan->min_purchase_amount = 5000;
        $plan->allowed = true;
        $plan->merchant_fee_variable = 75;
        $plan->merchant_fee_fixed = 0;
        $plan->customer_fee_variable = 0;
        $plan->customer_lending_rate = 0;
        $plan->customer_fee_fixed = 0;
        $plan->id = null;
        $plan->available_in_pos = true;
        $plan->deferred_trigger_bypass_scoring = false;
        $plan->first_installment_ratio = null;
        $plan->merchant = 'merchant_id';
        $plan->payout_on_acceptance = false;

        return $plan;
    }

    protected function planP2x()
    {
        $plan = $this->createMock(FeePlan::class);

        $plan->installments_count = 2;
        $plan->kind = 'general';
        $plan->deferred_months = 0;
        $plan->deferred_days = 0;
        $plan->deferred_trigger_limit_days = null;
        $plan->max_purchase_amount = 200000;
        $plan->min_purchase_amount = 5000;
        $plan->allowed = true;
        $plan->merchant_fee_variable = 310;
        $plan->merchant_fee_fixed = 0;
        $plan->customer_fee_variable = 0;
        $plan->customer_lending_rate = 0;
        $plan->customer_fee_fixed = 0;
        $plan->id = null;
        $plan->available_in_pos = true;
        $plan->deferred_trigger_bypass_scoring = false;
        $plan->first_installment_ratio = null;
        $plan->merchant = 'merchant_id';
        $plan->payout_on_acceptance = false;

        return $plan;
    }

    protected function planP3x()
    {
        $plan = $this->createMock(FeePlan::class);

        $plan->installments_count = 3;
        $plan->kind = 'general';
        $plan->deferred_months = 0;
        $plan->deferred_days = 0;
        $plan->deferred_trigger_limit_days = null;
        $plan->max_purchase_amount = 200000;
        $plan->min_purchase_amount = 5000;
        $plan->allowed = true;
        $plan->merchant_fee_variable = 380;
        $plan->merchant_fee_fixed = 0;
        $plan->customer_fee_variable = 0;
        $plan->customer_lending_rate = 0;
        $plan->customer_fee_fixed = 0;
        $plan->id = null;
        $plan->available_in_pos = true;
        $plan->deferred_trigger_bypass_scoring = false;
        $plan->first_installment_ratio = null;
        $plan->merchant = 'merchant_id';
        $plan->payout_on_acceptance = false;

        return $plan;
    }

    protected function planP4x()
    {
        $plan = $this->createMock(FeePlan::class);

        $plan->installments_count = 4;
        $plan->kind = 'general';
        $plan->deferred_months = 0;
        $plan->deferred_days = 0;
        $plan->deferred_trigger_limit_days = null;
        $plan->max_purchase_amount = 200000;
        $plan->min_purchase_amount = 5000;
        $plan->allowed = true;
        $plan->merchant_fee_variable = 480;
        $plan->merchant_fee_fixed = 0;
        $plan->customer_fee_variable = 0;
        $plan->customer_lending_rate = 0;
        $plan->customer_fee_fixed = 0;
        $plan->id = null;
        $plan->available_in_pos = true;
        $plan->deferred_trigger_bypass_scoring = false;
        $plan->first_installment_ratio = null;
        $plan->merchant = 'merchant_id';
        $plan->payout_on_acceptance = false;

        return $plan;
    }

    protected function planDeferred15()
    {
        $plan = $this->createMock(FeePlan::class);

        $plan->installments_count = 1;
        $plan->kind = 'general';
        $plan->deferred_months = 0;
        $plan->deferred_days = 15;
        $plan->deferred_trigger_limit_days = null;
        $plan->max_purchase_amount = 200000;
        $plan->min_purchase_amount = 5000;
        $plan->allowed = true;
        $plan->merchant_fee_variable = 450;
        $plan->merchant_fee_fixed = 0;
        $plan->customer_fee_variable = 0;
        $plan->customer_lending_rate = 0;
        $plan->customer_fee_fixed = 0;
        $plan->id = null;
        $plan->available_in_pos = true;
        $plan->deferred_trigger_bypass_scoring = false;
        $plan->first_installment_ratio = null;
        $plan->merchant = 'merchant_id';
        $plan->payout_on_acceptance = false;

        return $plan;
    }
}