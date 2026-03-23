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

    public function loadWidgetAssets(): void
    {
        if (!in_array($this->context->controller->php_self, ['product', 'cart'], true)) {
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
        $this->context->controller->registerStylesheet(
            'alma-widget-cdn',
            'https://cdn.jsdelivr.net/npm/@alma/widgets@4.x/dist/widgets.min.css',
            [
                'server' => 'remote',
                'media' => 'all',
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
    }
}
