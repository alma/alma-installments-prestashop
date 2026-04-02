<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
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
    /**
     * @var \ProductControllerCore
     */
    private $productController;

    public function setUp(): void
    {
        $this->language = $this->createMock(\Language::class);
        $this->language->iso_code = 'en';
        $this->language->id = 1;
        $this->cart = $this->createMock(\Cart::class);
        $this->cart->method('getProducts')->willReturn([]);
        $this->product = $this->createMock(\Product::class);
        $this->product->id = 1;
        $this->product->method('getPrice')->willReturn(99.0);
        $this->productController = $this->createMock(\ProductControllerCore::class);
        $this->context = $this->createMock(\Context::class);
        $this->context->language = $this->language;
        $this->context->cart = $this->cart;
        $this->context->controller = $this->productController;
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
        $this->configurationRepository->expects($this->once())
            ->method('getCartWidgetState')
            ->willReturn(true);
        $this->context->smarty = $this->createMock(\Smarty::class);
        $this->context->smarty->expects($this->once())
            ->method('createTemplate')
            ->willThrowException(new \SmartyException());
        $this->assertEquals($expected, $this->widgetFrontendService->renderWidget('alma.widget.cart'));
    }

    public function testRenderWidgetReturnTemplateCart()
    {
        $widgetVariables = [
            'container' => 'alma-widget-cart',
            'isExcluded' => false,
            'showExcludedMessage' => false,
            'excludedMessage' => '',
            'almaLogoUrl' => _MODULE_DIR_ . 'alma/views/img/logos/logo_alma.svg',
            'widgetConfig' => json_encode([
                'purchaseAmount' => 42000,
                'containerId' => '#alma-widget-cart',
                'merchantId' => 'merchant_id',
                'hideIfNotEligible' => 1,
                'mode' => 'test',
                'plans' => [],
                'locale' => 'en',
            ])
        ];
        $expected = 'cart template';
        $this->configurationRepository->expects($this->once())
            ->method('getCartWidgetState')
            ->willReturn(true);
        $this->cart->method('getCartTotalPrice')->willReturn(420.00);
        $this->excludedCategoriesService->method('isExcluded')->willReturn(false);
        $this->excludedCategoriesService->method('isWidgetDisplayNotEligibleEnabled')->willReturn(false);
        $this->excludedCategoriesService->method('getExcludedMessage')->willReturn('');
        $this->configurationRepository->method('getMerchantId')->willReturn('merchant_id');
        $this->configurationRepository->method('getCartWidgetDisplayNotEligible')->willReturn(false);
        $this->configurationRepository->method('getMode')->willReturn('test');
        $this->configurationRepository->method('getFeePlanList')->willReturn([]);
        $this->configurationRepository->method('getCartWidgetOldPositionCustom')->willReturn(false);

        $this->context->smarty = $this->createMock(\Smarty::class);
        $tpl = $this->createMock(\Smarty_Internal_Template::class);
        $this->context->smarty->expects($this->once())
            ->method('createTemplate')
            ->with(_PS_MODULE_DIR_ . 'alma/views/templates/widget/widget.tpl')
            ->willReturn($tpl);
        $tpl->expects($this->once())
            ->method('assign')
            ->with($widgetVariables);
        $tpl->expects($this->once())
            ->method('fetch')
            ->willReturn($expected);
        $this->assertEquals($expected, $this->widgetFrontendService->renderWidget('alma.widget.cart'));
    }

    public function testRenderWidgetWidgetDisabledReturnEmptyString()
    {
        $expected = '';

        $this->configurationRepository->expects($this->once())
            ->method('getCartWidgetState')
            ->willReturn(false);
        $this->context->smarty = $this->createMock(\Smarty::class);
        $this->context->smarty->expects($this->never())
            ->method('createTemplate');
        $this->assertEquals($expected, $this->widgetFrontendService->renderWidget('alma.widget.cart'));
    }

    /**
     * Cart is excluded, message display is enabled — widget is hidden, message shown from widget tag.
     */
    public function testGetWidgetVariablesForCartWithWidgetTagAndExcludedCategoriesAndDisplayMessage()
    {
        $widgetVariables = [
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
        $expected = 'cart template widget';
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
            ->method('getCartWidgetState')
            ->willReturn(true);
        $this->excludedCategoriesService->expects($this->once())
            ->method('isExcluded')
            ->willReturn(true);
        $this->excludedCategoriesService->expects($this->once())
            ->method('isWidgetDisplayNotEligibleEnabled')
            ->willReturn(true);
        $this->excludedCategoriesService->expects($this->once())
            ->method('getExcludedMessage')
            ->with(1)
            ->willReturn('Excluded product.');
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
        $this->context->smarty = $this->createMock(\Smarty::class);
        $tpl = $this->createMock(\Smarty_Internal_Template::class);
        $this->context->smarty->expects($this->once())
            ->method('createTemplate')
            ->with(_PS_MODULE_DIR_ . 'alma/views/templates/widget/widget.tpl')
            ->willReturn($tpl);
        $tpl->expects($this->once())
            ->method('assign')
            ->with($widgetVariables);
        $tpl->expects($this->once())
            ->method('fetch')
            ->willReturn($expected);
        $this->assertEquals($expected, $this->widgetFrontendService->renderWidget('alma.widget.cart'));
    }

    /**
     * Cart is excluded, message display is disabled — widget hidden, no message shown from hook.
     */
    public function testGetWidgetVariablesForCartWithHookTagAndExcludedCategoriesAndDisplayMessage()
    {
        $widgetVariables = [
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
        $expected = 'cart template hook';
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
            ->method('getCartWidgetState')
            ->willReturn(true);
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
        $this->context->smarty = $this->createMock(\Smarty::class);
        $tpl = $this->createMock(\Smarty_Internal_Template::class);
        $this->context->smarty->expects($this->once())
            ->method('createTemplate')
            ->with(_PS_MODULE_DIR_ . 'alma/views/templates/widget/widget.tpl')
            ->willReturn($tpl);
        $tpl->expects($this->once())
            ->method('assign')
            ->with($widgetVariables);
        $tpl->expects($this->once())
            ->method('fetch')
            ->willReturn($expected);
        $this->assertEquals($expected, $this->widgetFrontendService->renderWidget('alma.widget.ShoppingCartFooter'));
    }

    public function testGetWidgetVariablesWithUnknownHookNameReturnEmpty()
    {
        $this->assertEquals('', $this->widgetFrontendService->renderWidget('unknown.hookname'));
    }

    /**
     * Custom position enabled — containerId uses the selector from configuration, container unchanged.
     */
    public function testGetWidgetVariablesWithCustomPositionEnabled()
    {
        $widgetVariables = [
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
        $expected = 'template with custom position';
        $this->cart->expects($this->once())
            ->method('getCartTotalPrice')
            ->willReturn(0.0);
        $this->configurationRepository->expects($this->once())
            ->method('getCartWidgetState')
            ->willReturn(true);
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
        $this->context->smarty = $this->createMock(\Smarty::class);
        $tpl = $this->createMock(\Smarty_Internal_Template::class);
        $this->context->smarty->expects($this->once())
            ->method('createTemplate')
            ->with(_PS_MODULE_DIR_ . 'alma/views/templates/widget/widget.tpl')
            ->willReturn($tpl);
        $tpl->expects($this->once())
            ->method('assign')
            ->with($widgetVariables);
        $tpl->expects($this->once())
            ->method('fetch')
            ->willReturn($expected);
        $this->assertEquals($expected, $this->widgetFrontendService->renderWidget('alma.widget.cart'));
    }

    /**
     * Custom position disabled — containerId falls back to '#' . $container.
     */
    public function testGetWidgetVariablesWithCustomPositionDisabled()
    {
        $widgetVariables = [
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
        $expected = 'template with default position';
        $this->cart->expects($this->once())
            ->method('getCartTotalPrice')
            ->willReturn(0.0);
        $this->configurationRepository->expects($this->once())
            ->method('getCartWidgetState')
            ->willReturn(true);
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
        $this->context->smarty = $this->createMock(\Smarty::class);
        $tpl = $this->createMock(\Smarty_Internal_Template::class);
        $this->context->smarty->expects($this->once())
            ->method('createTemplate')
            ->with(_PS_MODULE_DIR_ . 'alma/views/templates/widget/widget.tpl')
            ->willReturn($tpl);
        $tpl->expects($this->once())
            ->method('assign')
            ->with($widgetVariables);
        $tpl->expects($this->once())
            ->method('fetch')
            ->willReturn($expected);
        $this->assertEquals($expected, $this->widgetFrontendService->renderWidget('alma.widget.cart'));
    }

    public function testGetWidgetVariablesWithoutCartInContextReturnMessageCartNotFound()
    {
        $this->context->cart = null;
        $this->configurationRepository->expects($this->once())
            ->method('getCartWidgetState')
            ->willReturn(true);
        $this->context->smarty = $this->createMock(\Smarty::class);
        $tpl = $this->createMock(\Smarty_Internal_Template::class);
        $this->context->smarty->expects($this->once())
            ->method('createTemplate')
            ->with(_PS_MODULE_DIR_ . 'alma/views/templates/widget/widget.tpl')
            ->willReturn($tpl);
        $tpl->expects($this->never())
            ->method('assign');
        $tpl->expects($this->never())
            ->method('fetch');
        $this->assertEquals('Cart not found in context', $this->widgetFrontendService->renderWidget('alma.widget.cart'));
    }

    public function testRenderWidgetReturnTemplateProduct()
    {
        $widgetVariables = [
            'container' => 'alma-widget-product',
            'isExcluded' => false,
            'showExcludedMessage' => false,
            'excludedMessage' => '',
            'almaLogoUrl' => _MODULE_DIR_ . 'alma/views/img/logos/logo_alma.svg',
            'widgetConfig' => json_encode([
                'purchaseAmount' => 9900,
                'containerId' => '#alma-widget-product',
                'merchantId' => 'merchant_id',
                'hideIfNotEligible' => 1,
                'mode' => 'test',
                'plans' => [],
                'locale' => 'en',
            ])
        ];
        $expected = 'product template';
        $this->configurationRepository->expects($this->once())
            ->method('getProductWidgetState')
            ->willReturn(true);
        $this->productController->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->product);
        $this->excludedCategoriesService->method('isExcluded')->willReturn(false);
        $this->excludedCategoriesService->method('isWidgetDisplayNotEligibleEnabled')->willReturn(false);
        $this->excludedCategoriesService->method('getExcludedMessage')->willReturn('');
        $this->configurationRepository->method('getMerchantId')->willReturn('merchant_id');
        $this->configurationRepository->method('getProductWidgetDisplayNotEligible')->willReturn(false);
        $this->configurationRepository->method('getMode')->willReturn('test');
        $this->configurationRepository->method('getFeePlanList')->willReturn([]);

        $this->context->smarty = $this->createMock(\Smarty::class);
        $tpl = $this->createMock(\Smarty_Internal_Template::class);
        $this->context->smarty->expects($this->once())
            ->method('createTemplate')
            ->with(_PS_MODULE_DIR_ . 'alma/views/templates/widget/widget.tpl')
            ->willReturn($tpl);
        $tpl->expects($this->once())
            ->method('assign')
            ->with($widgetVariables);
        $tpl->expects($this->once())
            ->method('fetch')
            ->willReturn($expected);
        $this->assertEquals($expected, $this->widgetFrontendService->renderWidget('alma.widget.product'));
    }

    public function testRenderWidgetProductWidgetDisabledReturnEmptyString()
    {
        $expected = '';
        $this->configurationRepository->expects($this->once())
            ->method('getProductWidgetState')
            ->willReturn(false);
        $this->context->smarty = $this->createMock(\Smarty::class);
        $this->context->smarty->expects($this->never())
            ->method('createTemplate');
        $this->assertEquals($expected, $this->widgetFrontendService->renderWidget('alma.widget.product'));
    }

    /**
     * Product is excluded, message display is enabled — widget is hidden, message shown.
     */
    public function testGetWidgetVariablesForProductWithWidgetTag()
    {
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
        $widgetVariables = [
            'container' => 'alma-widget-product',
            'isExcluded' => true,
            'showExcludedMessage' => true,
            'excludedMessage' => 'Excluded product.',
            'almaLogoUrl' => _MODULE_DIR_ . 'alma/views/img/logos/logo_alma.svg',
            'widgetConfig' => json_encode([
                'purchaseAmount' => 9900,
                'containerId' => '#alma-widget-product',
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
        $expected = 'product template widget tag';
        $this->configurationRepository->expects($this->once())
            ->method('getProductWidgetState')
            ->willReturn(true);
        $this->excludedCategoriesService->method('isExcluded')->willReturn(true);
        $this->excludedCategoriesService->method('isWidgetDisplayNotEligibleEnabled')->willReturn(true);
        $this->excludedCategoriesService->method('getExcludedMessage')->with(1)->willReturn('Excluded product.');
        $this->productController->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->product);
        $this->configurationRepository->expects($this->once())
            ->method('getMerchantId')
            ->willReturn('merchant_id');
        $this->configurationRepository->expects($this->once())
            ->method('getProductWidgetDisplayNotEligible')
            ->willReturn(true);
        $this->configurationRepository->expects($this->once())
            ->method('getMode')
            ->willReturn('test');
        $this->configurationRepository->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanList);
        $this->context->smarty = $this->createMock(\Smarty::class);
        $tpl = $this->createMock(\Smarty_Internal_Template::class);
        $this->context->smarty->expects($this->once())
            ->method('createTemplate')
            ->with(_PS_MODULE_DIR_ . 'alma/views/templates/widget/widget.tpl')
            ->willReturn($tpl);
        $tpl->expects($this->once())
            ->method('assign')
            ->with($widgetVariables);
        $tpl->expects($this->once())
            ->method('fetch')
            ->willReturn($expected);
        $this->assertEquals($expected, $this->widgetFrontendService->renderWidget('alma.widget.product'));
    }

    /**
     * Product is excluded, message display is disabled — widget hidden, no message shown.
     */
    public function testGetWidgetVariablesForProductWithHookTag()
    {
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
        $widgetVariables = [
            'container' => 'alma-widget-ProductPriceBlock',
            'isExcluded' => true,
            'showExcludedMessage' => false,
            'excludedMessage' => 'Excluded product.',
            'almaLogoUrl' => _MODULE_DIR_ . 'alma/views/img/logos/logo_alma.svg',
            'widgetConfig' => json_encode([
                'purchaseAmount' => 9900,
                'containerId' => '#alma-widget-ProductPriceBlock',
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
        $expected = 'product template hook tag';
        $this->configurationRepository->expects($this->once())
            ->method('getProductWidgetState')
            ->willReturn(true);
        $this->excludedCategoriesService->method('isExcluded')->willReturn(true);
        $this->excludedCategoriesService->method('isWidgetDisplayNotEligibleEnabled')->willReturn(false);
        $this->excludedCategoriesService->method('getExcludedMessage')->with(1)->willReturn('Excluded product.');
        $this->productController->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->product);
        $this->configurationRepository->expects($this->once())
            ->method('getMerchantId')
            ->willReturn('merchant_id');
        $this->configurationRepository->expects($this->once())
            ->method('getProductWidgetDisplayNotEligible')
            ->willReturn(true);
        $this->configurationRepository->expects($this->once())
            ->method('getMode')
            ->willReturn('test');
        $this->configurationRepository->expects($this->once())
            ->method('getFeePlanList')
            ->willReturn($feePlanList);
        $this->context->smarty = $this->createMock(\Smarty::class);
        $tpl = $this->createMock(\Smarty_Internal_Template::class);
        $this->context->smarty->expects($this->once())
            ->method('createTemplate')
            ->with(_PS_MODULE_DIR_ . 'alma/views/templates/widget/widget.tpl')
            ->willReturn($tpl);
        $tpl->expects($this->once())
            ->method('assign')
            ->with($widgetVariables);
        $tpl->expects($this->once())
            ->method('fetch')
            ->willReturn($expected);
        $this->assertEquals($expected, $this->widgetFrontendService->renderWidget('alma.widget.ProductPriceBlock'));
    }

    public function testGetWidgetVariablesWithoutGetProductFunctionInContextController()
    {
        $this->configurationRepository->expects($this->once())
            ->method('getProductWidgetState')
            ->willReturn(true);
        $controller = new \stdClass();
        $this->context->controller = $controller;
        $this->context->smarty = $this->createMock(\Smarty::class);
        $tpl = $this->createMock(\Smarty_Internal_Template::class);
        $this->context->smarty->expects($this->once())
            ->method('createTemplate')
            ->with(_PS_MODULE_DIR_ . 'alma/views/templates/widget/widget.tpl')
            ->willReturn($tpl);
        $tpl->expects($this->never())
            ->method('assign');
        $tpl->expects($this->never())
            ->method('fetch');
        $this->assertEquals('getProduct does not exist in context controller', $this->widgetFrontendService->renderWidget('alma.widget.product'));
    }

    public function testGetWidgetVariablesWithGetProductNotInstanceOfProductInContextController()
    {
        $this->configurationRepository->expects($this->once())
            ->method('getProductWidgetState')
            ->willReturn(true);
        $this->productController->method('getProduct')->willReturn(null);
        $this->context->controller = $this->productController;
        $this->context->smarty = $this->createMock(\Smarty::class);
        $tpl = $this->createMock(\Smarty_Internal_Template::class);
        $this->context->smarty->expects($this->once())
            ->method('createTemplate')
            ->with(_PS_MODULE_DIR_ . 'alma/views/templates/widget/widget.tpl')
            ->willReturn($tpl);
        $tpl->expects($this->never())
            ->method('assign');
        $tpl->expects($this->never())
            ->method('fetch');
        $this->assertEquals('Product not found in context controller', $this->widgetFrontendService->renderWidget('alma.widget.product'));
    }
}
