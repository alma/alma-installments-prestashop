<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\ExcludedCategoriesService;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ExcludedCategoriesAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;

class ExcludedCategoriesServiceTest extends TestCase
{
    /**
     * @var \Context
     */
    private $context;
    /**
     * @var ConfigurationRepository
     */
    private $configurationRepository;

    public function setUp(): void
    {
        $this->context = $this->createMock(\Context::class);
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->excludedCategories = new ExcludedCategoriesService(
            $this->context,
            $this->configurationRepository
        );
    }

    public function testDefaultFieldsToSaveFirstSave()
    {
        $expectedFields = [
            ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES_WIDGET_DISPLAY_NOT_ELIGIBLE => 1,
            ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES_MESSAGE => 'Your cart is not eligible for payments with Alma.',
        ];

        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MERCHANT_ID)
            ->willReturn('');
        $this->assertEquals($expectedFields, $this->excludedCategories->defaultFieldsToSave());
    }

    public function testDefaultFieldsToSaveUpdateValues()
    {
        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MERCHANT_ID)
            ->willReturn('merchant_id');
        $this->assertEquals([], $this->excludedCategories->defaultFieldsToSave());
    }
}
