<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\WidgetException;
use PrestaShop\Module\Alma\Application\Service\WidgetFrontendService;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;

class WidgetFrontendServiceTest extends TestCase
{
    /**
     * @var ConfigurationRepository
     */
    private $configurationRepository;

    public function setUp(): void
    {
        $this->language = $this->createMock(\Language::class);
        $this->language->iso_code = 'en';
        $this->cart = $this->createMock(\Cart::class);
        $this->context = $this->createMock(\Context::class);
        $this->context->language = $this->language;
        $this->context->cart = $this->cart;
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->widgetFrontendService = new WidgetFrontendService(
            $this->context,
            $this->configurationRepository
        );
    }

    public function testRenderWidgetExpectException()
    {
        $expected = '';
        $configuration = [
            'hook' => 'widget.cart',
            'cart' => [],
        ];
        $smartyTemplateMock = $this->createMock(\Smarty_Internal_Template::class);
        $smartyTemplateMock->expects($this->never())
            ->method('fetch');
        $this->context->smarty = $this->createMock(\Smarty::class);
        $this->context->smarty->expects($this->once())
            ->method('createTemplate')
            ->willThrowException(new \SmartyException());
        $this->assertEquals($expected, $this->widgetFrontendService->renderWidget('alma.widget.cart', $configuration));
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\WidgetException
     */
    public function testGetWidgetVariablesForCartWithWidgetTag()
    {
        $expected = [
            'purchaseAmount' => 42000,
            'containerId' => '#alma-widget-cart',
            'merchantId' => 'merchant_id',
            'hideIfNotEligible' => false,
            'mode' => 'test',
            'plans' => '[{"installmentsCount":3,"deferredDays":0,"minAmount":5000,"maxAmount":200000}]',
            'locale' => 'en',
        ];
        $configuration = [];
        $this->cart->expects($this->once())
            ->method('getCartTotalPrice')
            ->willReturn(420.00);
        $feePlanList = [
                'general_1_0_0' => [
                    'state' => '0',
                    'min_amount' => '50',
                    'max_amount' => '200000',
                    'sort_order' => '1',
                ],
                'general_3_0_0' => [
                    'state' => '1',
                    'min_amount' => '5000',
                    'max_amount' => '200000',
                    'sort_order' => '3',
                ]
        ];
        $this->configurationRepository->expects($this->once())
            ->method('getMerchantId')
            ->willReturn('merchant_id');
        $this->configurationRepository->expects($this->once())
            ->method('getCartWidgetDisplayNotEligible')
            ->willReturn(true);
        $this->configurationRepository->expects($this->once())
            ->method('getMode')
            ->willReturn('test');
        $this->configurationRepository->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanList);
        $this->assertEquals($expected, $this->widgetFrontendService->getWidgetVariables('alma.widget.cart', $configuration));
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\WidgetException
     */
    public function testGetWidgetVariablesForCartWithHookTag()
    {
        $expected = [
            'purchaseAmount' => 42000,
            'containerId' => '#alma-widget-cart',
            'merchantId' => 'merchant_id',
            'hideIfNotEligible' => false,
            'mode' => 'test',
            'plans' => '[{"installmentsCount":3,"deferredDays":0,"minAmount":5000,"maxAmount":200000}]',
            'locale' => 'en',
        ];
        $configuration = [];
        $this->cart->expects($this->once())
            ->method('getCartTotalPrice')
            ->willReturn(420.00);
        $feePlanList = [
            'general_1_0_0' => [
                'state' => '0',
                'min_amount' => '50',
                'max_amount' => '200000',
                'sort_order' => '1',
            ],
            'general_3_0_0' => [
                'state' => '1',
                'min_amount' => '5000',
                'max_amount' => '200000',
                'sort_order' => '3',
            ]
        ];
        $this->configurationRepository->expects($this->once())
            ->method('getMerchantId')
            ->willReturn('merchant_id');
        $this->configurationRepository->expects($this->once())
            ->method('getCartWidgetDisplayNotEligible')
            ->willReturn(true);
        $this->configurationRepository->expects($this->once())
            ->method('getMode')
            ->willReturn('test');
        $this->configurationRepository->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanList);
        $this->assertEquals($expected, $this->widgetFrontendService->getWidgetVariables('alma.widget.ShoppingCartFooter', $configuration));
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\WidgetException
     */
    public function testGetWidgetVariablesWithUnknownHookName()
    {
        $this->expectException(WidgetException::class);
        $this->widgetFrontendService->getWidgetVariables('unknown.hookname');
    }

    /**
     * @throws \PrestaShop\Module\Alma\Application\Exception\WidgetException
     */
    public function testGetWidgetVariablesWithoutCartInContext()
    {
        $this->context->cart = null;
        $this->expectException(WidgetException::class);
        $this->widgetFrontendService->getWidgetVariables('alma.widget.cart');
    }
}
