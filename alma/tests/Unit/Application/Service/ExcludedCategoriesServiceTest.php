<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\ExcludedCategoriesService;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ExcludedCategoriesAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\LanguageRepository;
use PrestaShopBundle\Translation\TranslatorInterface;

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
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->excludedCategories = new ExcludedCategoriesService(
            $this->context,
            $this->configurationRepository,
            $this->languageRepository,
            $this->translator
        );
    }

    public function testDefaultFieldsToSaveFirstSaveWithOneLanguageEn(): void
    {
        $expected = [
            ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES_WIDGET_DISPLAY_NOT_ELIGIBLE => 1,
            ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES_MESSAGE . '_1' => 'Your cart is not eligible for payments with Alma.',
        ];

        $languages = [
            ['id_lang' => 1, 'iso_code' => 'en', 'locale' => 'en-US'],
        ];

        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MERCHANT_ID)
            ->willReturn('');

        $this->languageRepository->expects($this->once())
            ->method('getActiveLanguages')
            ->willReturn($languages);

        $this->translator->expects($this->once())
            ->method('trans')
            ->willReturn('Your cart is not eligible for payments with Alma.');

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
            ['id_lang' => 1, 'iso_code' => 'en', 'locale' => 'en-US'],
            ['id_lang' => 2, 'iso_code' => 'fr', 'locale' => 'fr-FR'],
        ];

        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ApiAdminForm::KEY_FIELD_MERCHANT_ID)
            ->willReturn('');

        $this->languageRepository->expects($this->once())
            ->method('getActiveLanguages')
            ->willReturn($languages);

        $this->translator->expects($this->any())
            ->method('trans')
            ->willReturnOnConsecutiveCalls(
                'Your cart is not eligible for payments with Alma.',
                'Paiements avec Alma indisponibles'
            );

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

    public function testAddExcludeCategoriesWithIdsCategoriesAndZeroCategoriesSavedBefore(): void
    {
        $categoriesIds = [1, 2, 3];
        $categoriesIdsToSave = json_encode($categoriesIds);
        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES)
            ->willReturn('');
        $this->configurationRepository->expects($this->once())
            ->method('updateValue')
            ->with(ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES, $categoriesIdsToSave);

        $this->excludedCategories->addExcludeCategories($categoriesIds);
    }

    public function testAddExcludeCategoriesWithNewIdsCategoriesAndCategoriesSavedBefore(): void
    {
        $categoriesIdsInDb = json_encode([4, 2]);
        $newCategoriesIds = [1, 2];
        $categoriesIdsToSave = json_encode([4, 2, 1]);
        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES)
            ->willReturn($categoriesIdsInDb);
        $this->configurationRepository->expects($this->once())
            ->method('updateValue')
            ->with(ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES, $categoriesIdsToSave);

        $this->excludedCategories->addExcludeCategories($newCategoriesIds);
    }

    public function testRemoveExcludeCategoriesWithIdsCategoriesAndZeroCategoriesSavedBefore(): void
    {
        $categoriesIds = [1, 2, 3];
        $categoriesIdsToSave = json_encode([]);
        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES)
            ->willReturn('');
        $this->configurationRepository->expects($this->once())
            ->method('updateValue')
            ->with(ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES, $categoriesIdsToSave);

        $this->excludedCategories->removeExcludeCategories($categoriesIds);
    }

    public function testRemoveExcludeCategoriesWithNewIdsCategoriesAndCategoriesSavedBefore(): void
    {
        $categoriesIdsInDb = json_encode([4, 2]);
        $categoriesIdsToRemove = [1, 2];
        $categoriesIdsToSave = json_encode([4]);
        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->with(ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES)
            ->willReturn($categoriesIdsInDb);
        $this->configurationRepository->expects($this->once())
            ->method('updateValue')
            ->with(ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES, $categoriesIdsToSave);

        $this->excludedCategories->removeExcludeCategories($categoriesIdsToRemove);
    }
}
