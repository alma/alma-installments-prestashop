<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\WidgetService;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\CartWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ProductWidgetAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShopBundle\Translation\TranslatorInterface;

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
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->widgetService = new WidgetService(
            $this->context,
            $this->configurationRepository,
            $this->translator
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

    public function testGetOldWidgetPositionFormWithoutCustomPositionSaved()
    {
        $this->configurationRepository->expects($this->once())
            ->method('getCartWidgetOldPositionCustom')
            ->willReturn(false);
        $this->assertEquals([], $this->widgetService->getOldCartWidgetPositionForm());
    }

    public function testGetOldWidgetPositionFormWithCustomPositionSaved()
    {
        $expected = [
            CartWidgetAdminForm::KEY_FIELD_CART_WIDGET_POSITION_CUSTOM => [
                'type' => 'switch',
                'label' => '',
                'required' => false,
                'form' => 'cart_widget',
                'encrypted' => false,
                'options' => [
                    'values' => [
                        [
                            'id' => 'ENABLE',
                            'value' => 1,
                            'label' => '',
                        ],
                        [
                            'id' => 'DISABLE',
                            'value' => 0,
                            'label' => ''
                        ]
                    ],
                    'desc' => '',
                ]
            ],
        ];

        $this->configurationRepository->expects($this->once())
            ->method('getCartWidgetOldPositionCustom')
            ->willReturn(true);
        $this->assertEquals($expected, $this->widgetService->getOldCartWidgetPositionForm());
    }

    public function testFieldsValueOldWidgetPositionWithoutCustomPositionSaved()
    {
        $this->configurationRepository->expects($this->once())
            ->method('getCartWidgetOldPositionCustom')
            ->willReturn(false);
        $this->configurationRepository->expects($this->once())
            ->method('getProductWidgetOldPositionCustom')
            ->willReturn(false);
        $this->assertEquals([], $this->widgetService->fieldsValueOldWidgetPosition());
    }

    public function testFieldsValueOldWidgetPositionWithCartCustomPositionSaved()
    {
        $expected = [
            CartWidgetAdminForm::KEY_FIELD_CART_WIDGET_POSITION_CUSTOM => 1,
        ];

        $this->configurationRepository->expects($this->once())
            ->method('getCartWidgetOldPositionCustom')
            ->willReturn(true);
        $this->configurationRepository->expects($this->once())
            ->method('getProductWidgetOldPositionCustom')
            ->willReturn(false);
        $this->assertEquals($expected, $this->widgetService->fieldsValueOldWidgetPosition());
    }

    public function testFieldsValueOldWidgetPositionWithCartAndProductCustomPositionSaved()
    {
        $expected = [
            CartWidgetAdminForm::KEY_FIELD_CART_WIDGET_POSITION_CUSTOM => 1,
            ProductWidgetAdminForm::KEY_FIELD_PRODUCT_WIDGET_POSITION_CUSTOM => 1,
        ];

        $this->configurationRepository->expects($this->once())
            ->method('getCartWidgetOldPositionCustom')
            ->willReturn(true);
        $this->configurationRepository->expects($this->once())
            ->method('getProductWidgetOldPositionCustom')
            ->willReturn(true);
        $this->assertEquals($expected, $this->widgetService->fieldsValueOldWidgetPosition());
    }

    public function testGetOldProductWidgetPositionFormWithoutCustomPositionSaved()
    {
        $this->configurationRepository->expects($this->once())
            ->method('getProductWidgetOldPositionCustom')
            ->willReturn(false);
        $this->assertEquals([], $this->widgetService->getOldProductWidgetPositionForm());
    }

    public function testGetOldProductWidgetPositionFormWithCustomPositionSaved()
    {
        $expected = [
            ProductWidgetAdminForm::KEY_FIELD_PRODUCT_WIDGET_POSITION_CUSTOM => [
                'type' => 'switch',
                'label' => '',
                'required' => false,
                'form' => 'product_widget',
                'encrypted' => false,
                'options' => [
                    'values' => [
                        [
                            'id' => 'ENABLE',
                            'value' => 1,
                            'label' => '',
                        ],
                        [
                            'id' => 'DISABLE',
                            'value' => 0,
                            'label' => ''
                        ]
                    ],
                    'desc' => '',
                ]
            ],
        ];

        $this->configurationRepository->expects($this->once())
            ->method('getProductWidgetOldPositionCustom')
            ->willReturn(true);
        $this->assertEquals($expected, $this->widgetService->getOldProductWidgetPositionForm());
    }
}
