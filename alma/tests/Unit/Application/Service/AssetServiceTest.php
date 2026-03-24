<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\AssetService;

class AssetServiceTest extends TestCase
{
    public function setUp(): void
    {
        $this->module = $this->createMock(\Module::class);
        $this->module->name = 'alma';
        $this->controller = $this->createMock(\FrontController::class);
        $this->context = $this->createMock(\Context::class);
        $this->context->controller = $this->controller;
        $this->assetService = new AssetService(
            $this->module,
            $this->context
        );
    }

    /**
     * @dataProvider controllerPagesDataProvider
     */
    public function testLoadWidgetAssetsRegistersAssetsOnProductCartOrHomePageWithPhpSelf($controllerPages): void
    {
        $this->context->controller->php_self = $controllerPages;
        $this->controller->expects($this->exactly(2))
            ->method('registerJavascript')
            ->withConsecutive(
                [
                    'alma-widget-cdn',
                    'https://cdn.jsdelivr.net/npm/@alma/widgets@4.x/dist/widgets.umd.js',
                    [
                        'server' => 'remote',
                        'position' => 'bottom',
                        'priority' => 10,
                    ],
                ],
                [
                    'alma-widget',
                    'modules/alma/views/js/alma-widget.js',
                    [
                        'priority' => 20,
                        'attribute' => 'async',
                    ],
                ]
            );
        $this->controller->expects($this->once())
            ->method('registerStylesheet')
            ->with(
                'alma-widget-cdn',
                'https://cdn.jsdelivr.net/npm/@alma/widgets@4.x/dist/widgets.min.css',
                [
                    'server' => 'remote',
                    'media' => 'all',
                    'priority' => 10,
                ]
            );

        $this->assetService->loadWidgetAssets();
    }

    public function testLoadWidgetAssetsDoesNotRegisterAssetsOnNonProductCartOrHomePageWithPhpSelf(): void
    {
        $this->context->controller->php_self = 'category';
        $this->controller->expects($this->never())->method('registerJavascript');
        $this->controller->expects($this->never())->method('registerStylesheet');

        $this->assetService->loadWidgetAssets();
    }

    public function testLoadWidgetAssetsRegistersAssetsOnProductPageWithControllerName(): void
    {
        $this->controller = $this->getMockBuilder(\FrontController::class)
            ->setMockClassName('ProductController')
            ->getMock();
        $this->context->controller = $this->controller;
        $this->controller->expects($this->exactly(2))
            ->method('registerJavascript')
            ->withConsecutive(
                [
                    'alma-widget-cdn',
                    'https://cdn.jsdelivr.net/npm/@alma/widgets@4.x/dist/widgets.umd.js',
                    [
                        'server' => 'remote',
                        'position' => 'bottom',
                        'priority' => 10,
                    ],
                ],
                [
                    'alma-widget',
                    'modules/alma/views/js/alma-widget.js',
                    [
                        'priority' => 20,
                        'attribute' => 'async',
                    ],
                ]
            );
        $this->controller->expects($this->once())
            ->method('registerStylesheet')
            ->with(
                'alma-widget-cdn',
                'https://cdn.jsdelivr.net/npm/@alma/widgets@4.x/dist/widgets.min.css',
                [
                    'server' => 'remote',
                    'media' => 'all',
                    'priority' => 10,
                ]
            );

        $this->assetService->loadWidgetAssets();
    }

    public function testLoadWidgetAssetsRegistersAssetsOnCartPageWithControllerName(): void
    {
        $this->controller = $this->getMockBuilder(\FrontController::class)
            ->setMockClassName('CartController')
            ->getMock();
        $this->context->controller = $this->controller;
        $this->controller->expects($this->exactly(2))
            ->method('registerJavascript')
            ->withConsecutive(
                [
                    'alma-widget-cdn',
                    'https://cdn.jsdelivr.net/npm/@alma/widgets@4.x/dist/widgets.umd.js',
                    [
                        'server' => 'remote',
                        'position' => 'bottom',
                        'priority' => 10,
                    ],
                ],
                [
                    'alma-widget',
                    'modules/alma/views/js/alma-widget.js',
                    [
                        'priority' => 20,
                        'attribute' => 'async',
                    ],
                ]
            );
        $this->controller->expects($this->once())
            ->method('registerStylesheet')
            ->with(
                'alma-widget-cdn',
                'https://cdn.jsdelivr.net/npm/@alma/widgets@4.x/dist/widgets.min.css',
                [
                    'server' => 'remote',
                    'media' => 'all',
                    'priority' => 10,
                ]
            );

        $this->assetService->loadWidgetAssets();
    }

    public function testLoadWidgetAssetsRegistersAssetsOnHomePageWithControllerName(): void
    {
        $this->controller = $this->getMockBuilder(\FrontController::class)
            ->setMockClassName('IndexController')
            ->getMock();
        $this->context->controller = $this->controller;
        $this->controller->expects($this->exactly(2))
            ->method('registerJavascript')
            ->withConsecutive(
                [
                    'alma-widget-cdn',
                    'https://cdn.jsdelivr.net/npm/@alma/widgets@4.x/dist/widgets.umd.js',
                    [
                        'server' => 'remote',
                        'position' => 'bottom',
                        'priority' => 10,
                    ],
                ],
                [
                    'alma-widget',
                    'modules/alma/views/js/alma-widget.js',
                    [
                        'priority' => 20,
                        'attribute' => 'async',
                    ],
                ]
            );
        $this->controller->expects($this->once())
            ->method('registerStylesheet')
            ->with(
                'alma-widget-cdn',
                'https://cdn.jsdelivr.net/npm/@alma/widgets@4.x/dist/widgets.min.css',
                [
                    'server' => 'remote',
                    'media' => 'all',
                    'priority' => 10,
                ]
            );

        $this->assetService->loadWidgetAssets();
    }

    public function testLoadWidgetAssetsRegistersAssetsOnOtherPageWithControllerName(): void
    {
        $this->controller = $this->getMockBuilder(\FrontController::class)
            ->setMockClassName('OtherController')
            ->getMock();
        $this->context->controller = $this->controller;
        $this->controller->expects($this->never())
            ->method('registerJavascript');
        $this->controller->expects($this->never())
            ->method('registerStylesheet');

        $this->assetService->loadWidgetAssets();
    }

    /**
     * Data provider for controller pages
     *
     * @return array
     */
    public function controllerPagesDataProvider(): array
    {
        return [
            ['product'],
            ['cart'],
            ['index'],
        ];
    }
}
