<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\ExcludedCategoriesService;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ExcludedCategoriesAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\LanguageRepository;

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
    /**
     * @var ExcludedCategoriesService
     */
    private ExcludedCategoriesService $excludedCategories;

    public function setUp(): void
    {
        $this->context = $this->createMock(\Context::class);
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->languageRepository = $this->createMock(LanguageRepository::class);
        $this->excludedCategories = new ExcludedCategoriesService(
            $this->context,
            $this->configurationRepository,
            $this->languageRepository
        );
    }

    public function testDefaultFieldsToSaveFirstSaveWithOneLanguageEn(): void
    {
        $expected = [
            ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES_WIDGET_DISPLAY_NOT_ELIGIBLE => 1,
            ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES_MESSAGE . '_1' => 'Your cart is not eligible for payments with Alma.',
        ];

        $languages = [
            ['id_lang' => 1, 'iso_code' => 'en']
        ];

        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MERCHANT_ID)
            ->willReturn('');

        $this->languageRepository->expects($this->once())
            ->method('getActiveLanguages')
            ->willReturn($languages);

        $this->assertEquals($expected, $this->excludedCategories->defaultFieldsToSave());
    }

    public function testDefaultFieldsToSaveFirstSaveWithLanguagesEnAndFr(): void
    {
        $expected = [
            ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES_WIDGET_DISPLAY_NOT_ELIGIBLE => 1,
            ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES_MESSAGE . '_1' => 'Your cart is not eligible for payments with Alma.',
            ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES_MESSAGE . '_2' => 'Paiements avec Alma indisponibles',
        ];

        $languages = [
            ['id_lang' => 1, 'iso_code' => 'en'],
            ['id_lang' => 2, 'iso_code' => 'fr'],
        ];

        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MERCHANT_ID)
            ->willReturn('');

        $this->languageRepository->expects($this->once())
            ->method('getActiveLanguages')
            ->willReturn($languages);

        $this->assertEquals($expected, $this->excludedCategories->defaultFieldsToSave());
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
