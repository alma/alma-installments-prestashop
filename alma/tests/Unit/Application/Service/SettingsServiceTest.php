<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\AuthenticationException;
use PrestaShop\Module\Alma\Application\Exception\SettingsException;
use PrestaShop\Module\Alma\Application\Service\AuthenticationService;
use PrestaShop\Module\Alma\Application\Service\FeePlansService;
use PrestaShop\Module\Alma\Application\Service\SettingsService;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\FormCollection;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ToolsProxy;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;
use PrestaShop\Module\Alma\Tests\Mocks\FeePlansMock;

class SettingsServiceTest extends TestCase
{
    /**
     * @var FeePlansService
     */
    private $feePlansService;

    public function setup(): void
    {
        $this->authenticationService = $this->createMock(AuthenticationService::class);
        $this->toolsProxy = $this->createMock(ToolsProxy::class);
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->settingsRepository = $this->createMock(SettingsRepository::class);
        $this->feePlansService = $this->createMock(FeePlansService::class);
        $this->settingsService = new SettingsService(
            $this->authenticationService,
            $this->feePlansService,
            $this->settingsRepository,
            $this->configurationRepository,
            $this->toolsProxy
        );
    }

    /**
     * TODO: Need to add for each real field add in the form. We probably need to improve this test
     */
    public function testGetFieldsValue(): void
    {
        $this->feePlansService->expects($this->once())
            ->method('fieldsValue')
            ->willReturn(FeePlansMock::feePlanFieldsExpected(3));
        $this->configurationRepository->expects($this->any())
            ->method('get');
        $this->toolsProxy->expects($this->any())
            ->method('getValue');
        $this->assertIsArray($this->settingsService->getFieldsValue());
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\SettingsException
     */
    public function testDontSaveAuthenticationFailExpectException(): void
    {
        $this->authenticationService->expects($this->once())
            ->method('isValidKeys')
            ->willThrowException(new AuthenticationException());
        $this->expectException(SettingsException::class);
        $this->feePlansService->expects($this->never())
            ->method('fieldsValue');
        $this->settingsRepository->expects($this->never())
            ->method('save');

        $this->settingsService->saveWithNotification();
    }

    public function testSaveAuthenticationWrongWithDifferentMerchantIds(): void
    {
        $merchantIds = [
            'test' => '42',
            'live' => '43'
        ];
        $this->authenticationService->expects($this->once())
            ->method('isValidKeys')
            ->willReturn($merchantIds);
        $this->authenticationService->expects($this->once())
            ->method('checkSameMerchantIds')
            ->with($merchantIds)
            ->willThrowException(new AuthenticationException());
        $this->feePlansService->expects($this->never())
            ->method('fieldsValue');
        $this->settingsRepository->expects($this->never())
            ->method('save');
        $this->expectException(SettingsException::class);

        $this->settingsService->saveWithNotification();
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\SettingsException
     */
    public function testSaveWithOneKeySet(): void
    {
        $merchantIds = [
            'test' => '42'
        ];
        $overrideValues = [
            ApiAdminForm::KEY_FIELD_MERCHANT_ID => '42',
            ApiAdminForm::KEY_FIELD_MODE => 'test'
        ];
        $this->authenticationService->expects($this->once())
            ->method('isValidKeys')
            ->willReturn($merchantIds);
        $this->authenticationService->expects($this->once())
            ->method('checkSameMerchantIds')
            ->with($merchantIds);
        $this->feePlansService->expects($this->once())
            ->method('fieldsValue')
            ->willReturn(FeePlansMock::feePlanFieldsValueExpected(3));
        $fieldsValue = array_merge(
            FormCollection::getAllFields(FormCollection::SETTINGS_FORMS_CLASSES),
            FeePlansMock::feePlanFieldsValueExpected(3)
        );
        $overrideValues = array_merge(
            $overrideValues,
            FeePlansMock::feePlanFieldsValueExpected(3)
        );
        $this->settingsRepository->expects($this->once())
            ->method('save')
            ->with(
                $fieldsValue,
                $overrideValues
            );

        $this->assertEquals(
            'Mode automatically switched to test mode. To use the other mode, please enter the corresponding API key.',
            $this->settingsService->saveWithNotification()
        );
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\SettingsException
     */
    public function testSaveAuthenticationFine(): void
    {
        $merchantIds = [
            'test' => '42',
            'live' => '42'
        ];
        $overrideValues = [
            ApiAdminForm::KEY_FIELD_MERCHANT_ID => '42'
        ];
        $this->authenticationService->expects($this->once())
            ->method('isValidKeys')
            ->willReturn($merchantIds);
        $this->authenticationService->expects($this->once())
            ->method('checkSameMerchantIds')
            ->with($merchantIds);
        $this->settingsRepository->expects($this->once())
            ->method('getEnvironment')
            ->willReturn('test');
        $this->toolsProxy->expects($this->once())
            ->method('getValue')
            ->with(ApiAdminForm::KEY_FIELD_MODE, 'test')
            ->willReturn('test');
        $this->feePlansService->expects($this->once())
            ->method('fieldsValue')
            ->willReturn(FeePlansMock::feePlanFieldsValueExpected(3));
        $fieldsValue = array_merge(
            FormCollection::getAllFields(FormCollection::SETTINGS_FORMS_CLASSES),
            FeePlansMock::feePlanFieldsValueExpected(3)
        );
        $overrideValues = array_merge(
            $overrideValues,
            FeePlansMock::feePlanFieldsValueExpected(3)
        );
        $this->settingsRepository->expects($this->once())
            ->method('save')
            ->with(
                $fieldsValue,
                $overrideValues
            );

        $this->assertEquals('Settings successfully updated', $this->settingsService->saveWithNotification());
    }
}
