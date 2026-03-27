<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\AssetService;
use PrestaShop\Module\Alma\Application\Service\WidgetService;

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
    public function testCheckAndLoadAssetsRegistersAssetsOnProductCartOrHomePageWithPhpSelf($controllerPages): void
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
                        'priority' => 200,
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

        $this->assetService->checkAndLoadAssets();
    }

    public function testCheckAndLoadAssetsDoesNotRegisterAssetsOnNonProductCartOrHomePageWithPhpSelf(): void
    {
        $this->context->controller->php_self = 'category';
        $this->controller->expects($this->never())->method('registerJavascript');
        $this->controller->expects($this->never())->method('registerStylesheet');

        $this->assetService->checkAndLoadAssets();
    }

    public function testCheckAndLoadAssetsRegistersAssetsOnProductPageWithControllerName(): void
    {
        $this->controller = $this->getMockBuilder(\FrontController::class)
            ->setMockClassName('ProductController')
            ->getMock();
        $this->context->controller = $this->controller;
        $this->controller->expects($this->exactly(2))
            ->method('registerJavascript');
        $this->controller->expects($this->once())
            ->method('registerStylesheet');

        $this->assetService->checkAndLoadAssets();
    }

    public function testCheckAndLoadAssetsRegistersAssetsOnCartPageWithControllerName(): void
    {
        $this->controller = $this->getMockBuilder(\FrontController::class)
            ->setMockClassName('CartController')
            ->getMock();
        $this->context->controller = $this->controller;
        $this->controller->expects($this->exactly(2))
            ->method('registerJavascript');
        $this->controller->expects($this->once())
            ->method('registerStylesheet');

        $this->assetService->checkAndLoadAssets();
    }

    public function testCheckAndLoadAssetsRegistersAssetsOnHomePageWithControllerName(): void
    {
        $this->controller = $this->getMockBuilder(\FrontController::class)
            ->setMockClassName('IndexController')
            ->getMock();
        $this->context->controller = $this->controller;
        $this->controller->expects($this->exactly(2))
            ->method('registerJavascript');
        $this->controller->expects($this->once())
            ->method('registerStylesheet');

        $this->assetService->checkAndLoadAssets();
    }

    public function testCheckAndLoadAssetsDoesNotRegisterAssetsOnOtherPageWithControllerName(): void
    {
        $this->controller = $this->getMockBuilder(\FrontController::class)
            ->setMockClassName('OtherController')
            ->getMock();
        $this->context->controller = $this->controller;
        $this->controller->expects($this->never())
            ->method('registerJavascript');
        $this->controller->expects($this->never())
            ->method('registerStylesheet');

        $this->assetService->checkAndLoadAssets();
    }

    /**
     * @dataProvider controllerPagesDataProvider
     */
    public function testIsControllerAllowedReturnsTrueForAllowedPhpSelf($controllerPage): void
    {
        $this->context->controller->php_self = $controllerPage;
        $this->assertTrue($this->assetService->isControllerAllowed($this->controller, WidgetService::ALLOWED_CONTROLLERS));
    }

    public function testIsControllerAllowedReturnsFalseForDisallowedPhpSelf(): void
    {
        $this->context->controller->php_self = 'category';
        $this->assertFalse($this->assetService->isControllerAllowed($this->controller, WidgetService::ALLOWED_CONTROLLERS));
    }

    /**
     * @dataProvider controllerClassNameDataProvider
     */
    public function testIsControllerAllowedReturnsTrueForAllowedControllerName($className): void
    {
        $controller = $this->getMockBuilder(\FrontController::class)
            ->setMockClassName($className)
            ->getMock();
        $this->context->controller = $controller;
        $this->assertTrue($this->assetService->isControllerAllowed($controller, WidgetService::ALLOWED_CONTROLLERS));
    }

    public function testIsControllerAllowedReturnsFalseForOtherControllerName(): void
    {
        $controller = $this->getMockBuilder(\FrontController::class)
            ->setMockClassName('OtherController')
            ->getMock();
        $this->context->controller = $controller;
        $this->assertFalse($this->assetService->isControllerAllowed($controller, WidgetService::ALLOWED_CONTROLLERS));
    }

    /**
     * Data provider for controller pages
     */
    public function controllerPagesDataProvider(): array
    {
        return [
            ['product'],
            ['cart'],
            ['index'],
        ];
    }

    /**
     * Data provider for allowed controller class names
     */
    public function controllerClassNameDataProvider(): array
    {
        return [
            ['ProductController'],
            ['CartController'],
            ['IndexController'],
        ];
    }
}
