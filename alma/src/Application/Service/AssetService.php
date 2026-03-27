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

    public function checkAndLoadAssets(): bool
    {
        if ($this->isControllerAllowed($this->context->controller, WidgetService::ALLOWED_CONTROLLERS)) {
            $this->loadWidgetAssets();
        }

        return true;
    }

    public function isControllerAllowed(\FrontController $controller, array $allowedControllers): bool
    {
        if (in_array(get_class($controller), array_keys($allowedControllers), true)) {
            return true;
        }
        if (in_array($this->context->controller->php_self, array_values($allowedControllers), true)) {
            return true;
        }

        return false;
    }

    private function loadWidgetAssets(): void
    {
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
                'priority' => 200,
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
}
