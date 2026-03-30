<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\WidgetException;
use PrestaShop\Module\Alma\Application\Service\ExcludedCategoriesService;
use PrestaShop\Module\Alma\Application\Service\WidgetFrontendService;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;

class WidgetFrontendServiceTest extends TestCase
{
    /**
     * @var ConfigurationRepository
     */
    private $configurationRepository;
    /**
     * @var ExcludedCategoriesService
     */
    private $excludedCategoriesService;

    public function setUp(): void
    {
        $this->language = $this->createMock(\Language::class);
        $this->language->iso_code = 'en';
        $this->language->id = 1;
        $this->cart = $this->createMock(\Cart::class);
        $this->cart->method('getProducts')->willReturn([]);
        $this->context = $this->createMock(\Context::class);
        $this->context->language = $this->language;
        $this->context->cart = $this->cart;
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->excludedCategoriesService = $this->createMock(ExcludedCategoriesService::class);
        $this->widgetFrontendService = new WidgetFrontendService(
            $this->context,
            $this->configurationRepository,
            $this->excludedCategoriesService
        );
    }

    public function testRenderWidgetExpectException()
    {
        $expected = '';
        $this->context->smarty = $this->createMock(\Smarty::class);
        $this->context->smarty->expects($this->once())
            ->method('createTemplate')
            ->willThrowException(new \SmartyException());
        $this->assertEquals($expected, $this->widgetFrontendService->renderWidget('alma.widget.cart'));
    }

    /**
     * Cart is excluded, message display is enabled — widget is hidden, message shown.
     * @throws \PrestaShop\Module\Alma\Application\Exception\WidgetException
     */
    public function testGetWidgetVariablesForCartWithWidgetTag()
    {
        $expected = [
            'container' => 'alma-widget-cart',
            'isExcluded' => true,
            'showExcludedMessage' => true,
            'excludedMessage' => 'Excluded product.',
            'almaLogoUrl' => _MODULE_DIR_ . 'alma/views/img/logos/logo_alma.svg',
            'widgetConfig' => json_encode([
                'purchaseAmount' => 42000,
                'containerId' => '#alma-widget-cart',
                'merchantId' => 'merchant_id',
                'hideIfNotEligible' => 0,
                'mode' => 'test',
                'plans' => [
                    [
                        'installmentsCount' => 3,
                        'deferredDays' => 0,
                        'minAmount' => 5000,
                        'maxAmount' => 200000
                    ]
                ],
                'locale' => 'en',
            ])
        ];
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
        $this->excludedCategoriesService->method('isExcluded')->willReturn(true);
        $this->excludedCategoriesService->method('isWidgetDisplayNotEligibleEnabled')->willReturn(true);
        $this->excludedCategoriesService->method('getExcludedMessage')->with(1)->willReturn('Excluded product.');
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
        $this->configurationRepository->expects($this->once())
            ->method('getCartWidgetOldPositionCustom')
            ->willReturn(false);
        $this->assertEquals($expected, $this->widgetFrontendService->getWidgetVariables('alma.widget.cart'));
    }

    /**
     * Cart is excluded, message display is disabled — widget hidden, no message shown.
     * @throws \PrestaShop\Module\Alma\Application\Exception\WidgetException
     */
    public function testGetWidgetVariablesForCartWithHookTag()
    {
        $expected = [
            'container' => 'alma-widget-ShoppingCartFooter',
            'isExcluded' => true,
            'showExcludedMessage' => false,
            'excludedMessage' => 'Excluded product.',
            'almaLogoUrl' => _MODULE_DIR_ . 'alma/views/img/logos/logo_alma.svg',
            'widgetConfig' => json_encode([
                'purchaseAmount' => 42000,
                'containerId' => '#alma-widget-ShoppingCartFooter',
                'merchantId' => 'merchant_id',
                'hideIfNotEligible' => 0,
                'mode' => 'test',
                'plans' => [
                    [
                        'installmentsCount' => 3,
                        'deferredDays' => 0,
                        'minAmount' => 5000,
                        'maxAmount' => 200000
                    ]
                ],
                'locale' => 'en',
            ])
        ];
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
        $this->excludedCategoriesService->method('isExcluded')->willReturn(true);
        $this->excludedCategoriesService->method('isWidgetDisplayNotEligibleEnabled')->willReturn(false);
        $this->excludedCategoriesService->method('getExcludedMessage')->with(1)->willReturn('Excluded product.');
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
        $this->configurationRepository->expects($this->once())
            ->method('getCartWidgetOldPositionCustom')
            ->willReturn(false);
        $this->assertEquals($expected, $this->widgetFrontendService->getWidgetVariables('alma.widget.ShoppingCartFooter'));
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
     * Custom position enabled — containerId uses the selector from configuration, container unchanged.
     * @throws \PrestaShop\Module\Alma\Application\Exception\WidgetException
     */
    public function testGetWidgetVariablesWithCustomPositionEnabled()
    {
        $expected = [
            'container' => 'alma-widget-cart',
            'isExcluded' => false,
            'showExcludedMessage' => false,
            'excludedMessage' => 'Excluded product.',
            'almaLogoUrl' => _MODULE_DIR_ . 'alma/views/img/logos/logo_alma.svg',
            'widgetConfig' => json_encode([
                'purchaseAmount' => 0,
                'containerId' => '#my-custom-selector',
                'merchantId' => 'merchant_id',
                'hideIfNotEligible' => 1,
                'mode' => 'test',
                'plans' => [],
                'locale' => 'en',
            ])
        ];
        $this->cart->expects($this->once())
            ->method('getCartTotalPrice')
            ->willReturn(0.0);
        $this->excludedCategoriesService->method('isExcluded')->willReturn(false);
        $this->excludedCategoriesService->method('isWidgetDisplayNotEligibleEnabled')->willReturn(false);
        $this->excludedCategoriesService->method('getExcludedMessage')->with(1)->willReturn('Excluded product.');
        $this->configurationRepository->expects($this->once())
            ->method('getMerchantId')
            ->willReturn('merchant_id');
        $this->configurationRepository->expects($this->once())
            ->method('getCartWidgetDisplayNotEligible')
            ->willReturn(false);
        $this->configurationRepository->expects($this->once())
            ->method('getMode')
            ->willReturn('test');
        $this->configurationRepository->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn([]);
        $this->configurationRepository->expects($this->once())
            ->method('getCartWidgetOldPositionCustom')
            ->willReturn(true);
        $this->configurationRepository->expects($this->once())
            ->method('getCartWidgetOldPositionSelector')
            ->willReturn('#my-custom-selector');
        $this->assertEquals($expected, $this->widgetFrontendService->getWidgetVariables('alma.widget.cart'));
    }

    /**
     * Custom position disabled — containerId falls back to '#' . $container.
     * @throws \PrestaShop\Module\Alma\Application\Exception\WidgetException
     */
    public function testGetWidgetVariablesWithCustomPositionDisabled()
    {
        $expected = [
            'container' => 'alma-widget-cart',
            'isExcluded' => false,
            'showExcludedMessage' => false,
            'excludedMessage' => '',
            'almaLogoUrl' => _MODULE_DIR_ . 'alma/views/img/logos/logo_alma.svg',
            'widgetConfig' => json_encode([
                'purchaseAmount' => 0,
                'containerId' => '#alma-widget-cart',
                'merchantId' => 'merchant_id',
                'hideIfNotEligible' => 1,
                'mode' => 'test',
                'plans' => [],
                'locale' => 'en',
            ])
        ];
        $this->cart->expects($this->once())
            ->method('getCartTotalPrice')
            ->willReturn(0.0);
        $this->excludedCategoriesService->method('isExcluded')->willReturn(false);
        $this->excludedCategoriesService->method('isWidgetDisplayNotEligibleEnabled')->willReturn(false);
        $this->excludedCategoriesService->method('getExcludedMessage')->with(1)->willReturn('');
        $this->configurationRepository->expects($this->once())
            ->method('getMerchantId')
            ->willReturn('merchant_id');
        $this->configurationRepository->expects($this->once())
            ->method('getCartWidgetDisplayNotEligible')
            ->willReturn(false);
        $this->configurationRepository->expects($this->once())
            ->method('getMode')
            ->willReturn('test');
        $this->configurationRepository->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn([]);
        $this->configurationRepository->expects($this->once())
            ->method('getCartWidgetOldPositionCustom')
            ->willReturn(false);
        $this->configurationRepository->expects($this->never())
            ->method('getCartWidgetOldPositionSelector');
        $this->assertEquals($expected, $this->widgetFrontendService->getWidgetVariables('alma.widget.cart'));
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
