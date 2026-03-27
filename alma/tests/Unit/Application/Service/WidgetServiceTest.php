<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\WidgetService;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\CartWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ProductWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;

class WidgetServiceTest extends TestCase
{
    /**
     * @var \Context
     */
    private $context;
    /**
     * @var ConfigurationRepository
     */
    private $configurationRepository;

    public function setUp(): void
    {
        $this->context = $this->createMock(\Context::class);
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->widgetService = new WidgetService(
            $this->context,
            $this->configurationRepository
        );
    }

    public function testDefaultFieldsToSaveFirstSave()
    {
        $expectedFields = [
            ProductWidgetAdminForm::KEY_FIELD_PRODUCT_WIDGET_STATE => 1,
            ProductWidgetAdminForm::KEY_FIELD_PRODUCT_WIDGET_DISPLAY_NOT_ELIGIBLE => 1,
            CartWidgetAdminForm::KEY_FIELD_CART_WIDGET_STATE => 1,
            CartWidgetAdminForm::KEY_FIELD_CART_WIDGET_DISPLAY_NOT_ELIGIBLE => 1,
        ];

        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MERCHANT_ID)
            ->willReturn('');
        $this->assertEquals($expectedFields, $this->widgetService->defaultFieldsToSave());
    }

    public function testDefaultFieldsToSaveUpdateValues()
    {
        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MERCHANT_ID)
            ->willReturn('merchant_id');
        $this->assertEquals([], $this->widgetService->defaultFieldsToSave());
    }
}
