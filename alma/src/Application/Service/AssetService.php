<?php

namespace PrestaShop\Module\Alma\Application\Service;

class AssetService
{
    private \Module $module;
    private \Context $context;

    public function __construct(
        \Module $module,
        \Context $context
    ) {
        $this->module = $module;
        $this->context = $context;
    }

    public function loadAssets(): bool
    {
        $this->loadWidgetAssets();
        return true;
    }

    /**
     * Load widget assets on product and cart pages
     */
    public function loadWidgetAssets(): void
    {
        if (!$this->checkCanLoadWidget($this->context->controller)) {
            return;
        }

        $this->context->controller->registerJavascript(
            'alma-widget-cdn',
            'https://cdn.jsdelivr.net/npm/@alma/widgets@4.x/dist/widgets.umd.js',
            [
                'server' => 'remote',
                'position' => 'bottom',
                'priority' => 10,
            ]
        );
        $this->context->controller->registerJavascript(
            'alma-widget',
            'modules/' . $this->module->name . '/views/js/alma-widget.js',
            [
                'priority' => 20,
                'attribute' => 'async',
            ]
        );
        $this->context->controller->registerStylesheet(
            'alma-widget-cdn',
            'https://cdn.jsdelivr.net/npm/@alma/widgets@4.x/dist/widgets.min.css',
            [
                'server' => 'remote',
                'media' => 'all',
                'priority' => 10,
            ]
        );
    }

    private function checkCanLoadWidget(\FrontController $controller): bool
    {
        if (get_class($controller) === 'ProductController' || get_class($controller) === 'CartController' || get_class($controller) === 'IndexController') {
            return true;
        }
        if (in_array($this->context->controller->php_self, ['product', 'cart', 'index'], true)) {
            return true;
        }

        return false;
    }
}
