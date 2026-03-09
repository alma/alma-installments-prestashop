<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\InPageService;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\InPageAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;

class InPageServiceTest extends TestCase
{
    public function setUp(): void
    {
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->inPageService = new InPageService(
            $this->configurationRepository
        );
    }

    public function testDefaultFieldsToSaveFirstSave(): void
    {
        $expected = [
            InPageAdminForm::KEY_FIELD_INPAGE_STATE => 1,
            InPageAdminForm::KEY_FIELD_INPAGE_PAYMENT_BUTTON_SELECTOR => '[data-module-name=alma]',
            InPageAdminForm::KEY_FIELD_INPAGE_PLACE_ORDER_BUTTON_SELECTOR => '#payment-confirmation button',
        ];
        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MERCHANT_ID)
            ->willReturn('');
        $this->assertEquals($expected, $this->inPageService->defaultFieldsToSave());
    }

    public function testDefaultFieldsToSaveUpdateConfiguration(): void
    {
        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MERCHANT_ID)
            ->willReturn('merchant_id');
        $this->assertEquals([], $this->inPageService->defaultFieldsToSave());
    }
}
