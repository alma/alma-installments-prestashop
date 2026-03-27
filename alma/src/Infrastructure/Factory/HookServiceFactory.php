<?php

namespace PrestaShop\Module\Alma\Infrastructure\Factory;

use PrestaShop\Module\Alma\Application\Service\ExcludedCategoriesService;
use PrestaShop\Module\Alma\Application\Service\WidgetFrontendService;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ProductProxy;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\ExcludedCategoriesRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\LanguageRepository;

class HookServiceFactory
{
    public static function createWidgetService(\Context $context): WidgetFrontendService
    {
        $configurationRepository = new ConfigurationRepository();
        $excludedCategoriesRepository = new ExcludedCategoriesRepository($configurationRepository);
        $excludedCategoriesService = new ExcludedCategoriesService(
            $context,
            $excludedCategoriesRepository,
            $configurationRepository,
            new LanguageRepository(),
            $context->getTranslator(),
            new ProductProxy()
        );

        return new WidgetFrontendService(
            $context,
            $configurationRepository,
            $excludedCategoriesService
        );
    }
}
