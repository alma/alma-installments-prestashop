<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\WidgetFrontendService;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingLazyArray;

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
        $this->context = $this->createMock(\Context::class);
        $this->context->language = $this->language;
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

    public function testGetWidgetVariablesForCartWithWidgetTag()
    {
        $expected = [
            'purchaseAmount' => 2872,
            'containerId' => '#alma-widget-cart',
            'merchantId' => 'merchant_id',
            'hideIfNotEligible' => false,
            'mode' => 'test',
            'plans' => '[{"installmentsCount":3,"deferredDays":0,"minAmount":5000,"maxAmount":200000}]',
            'locale' => 'en',
        ];
        $prestashopProductMock = $this->createMock(ProductListingLazyArray::class);
        $configuration = [
            'hook' => 'widget.cart',
            'cart' => [
                'products' => [
                    $prestashopProductMock
                ],
                'totals' => [
                    'total' => [
                        'type' => 'total',
                        'label' => 'Total',
                        'amount' => 28.72,
                        'value' => '€28.72',
                    ],
                    'total_including_tax' => [
                        'type' => 'total',
                        'label' => 'Total (tax incl.)',
                        'amount' => 28.72,
                        'value' => '€28.72',
                    ],
                    'total_excluding_tax' => [
                        'type' => 'total',
                        'label' => 'Total (tax excl.)',
                        'amount' => 28.72,
                        'value' => '€28.72',
                    ],
                ],
                'subtotals' => [
                    'products' => [
                        'type' => 'products',
                        'label' => 'Subtotal',
                        'amount' => 28.72,
                        'value' => '€28.72',
                    ],
                    'discounts' => null,
                    'shipping' => [
                        'type' => 'shipping',
                        'label' => 'Shipping',
                        'amount' => 0,
                        'value' => 'Free',
                    ],
                    'tax' => null,
                ],
                'products_count' => 1,
                'summary_string' => '1 item',
                'labels' => [
                    'tax_short' => '(tax incl.)',
                    'tax_long' => '(tax included)',
                ],
                'id_address_delivery' => '0',
                'id_address_invoice' => '0',
                'is_virtual' => false,
                'vouchers' => [
                    'allowed' => 0,
                    'added' => [],
                ],
                'discounts' => [],
                'minimalPurchase' => 0.0,
                'minimalPurchaseRequired' => '',
            ],
        ];
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
        $prestashopCartMock = $this->createMock(\Cart::class);
        $configuration = [
            'smarty' => [],
            'cookie' => [],
            'cart' => $prestashopCartMock,
            'altern' => []
        ];
        $prestashopCartMock->expects($this->once())
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

    public function testGetWidgetVariablesWithUnknownHookName()
    {
        $expected = ['error_widget' => true];

        $this->assertEquals($expected, $this->widgetFrontendService->getWidgetVariables('unknown.hookname', ['config' => 'value']));
    }
}
