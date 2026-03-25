<?php

namespace PrestaShop\Module\Alma\Infrastructure\Factory;

use PrestaShop\Module\Alma\Application\Service\WidgetFrontendService;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;

class HookServiceFactory
{
    public static function createWidgetService(\Context $context): WidgetFrontendService
    {
        return new WidgetFrontendService(
            $context,
            new ConfigurationRepository()
        );
    }
}
