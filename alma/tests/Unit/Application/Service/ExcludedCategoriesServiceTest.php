<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\ExcludedCategoriesService;
use PrestaShop\Module\Alma\Infrastructure\Form\ApiAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Form\ExcludedCategoriesAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Proxy\ProductProxy;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\ExcludedCategoriesRepository;
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
    /**
     * @var ExcludedCategoriesRepository
     */
    private $excludedCategoriesRepository;

    public function setUp(): void
    {
        $this->context = $this->createMock(\Context::class);
        $this->excludedCategoriesRepository = $this->createMock(ExcludedCategoriesRepository::class);
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->languageRepository = $this->createMock(LanguageRepository::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->productProxy = $this->createMock(ProductProxy::class);
        $this->excludedCategories = new ExcludedCategoriesService(
            $this->context,
            $this->excludedCategoriesRepository,
            $this->configurationRepository,
            $this->languageRepository,
            $this->translator,
            $this->productProxy
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
        $this->excludedCategoriesRepository->expects($this->once())
            ->method('getIds')
            ->willReturn([]);
        $this->excludedCategoriesRepository->expects($this->once())
            ->method('update')
            ->with($categoriesIds);

        $this->excludedCategories->addExcludeCategories($categoriesIds);
    }

    public function testAddExcludeCategoriesWithNewIdsCategoriesAndCategoriesSavedBefore(): void
    {
        $categoriesIdsInDb = [4, 2];
        $newCategoriesIds = [1, 2];
        $categoriesIdsToSave = [4, 2, 1];
        $this->excludedCategoriesRepository->expects($this->once())
            ->method('getIds')
            ->willReturn($categoriesIdsInDb);
        $this->excludedCategoriesRepository->expects($this->once())
            ->method('update')
            ->with($categoriesIdsToSave);

        $this->excludedCategories->addExcludeCategories($newCategoriesIds);
    }

    public function testRemoveExcludeCategoriesWithIdsCategoriesAndZeroCategoriesSavedBefore(): void
    {
        $categoriesIds = [1, 2, 3];
        $categoriesIdsToSave = [];
        $this->excludedCategoriesRepository->expects($this->once())
            ->method('getIds')
            ->willReturn([]);
        $this->excludedCategoriesRepository->expects($this->once())
            ->method('update')
            ->with($categoriesIdsToSave);

        $this->excludedCategories->removeExcludeCategories($categoriesIds);
    }

    public function testRemoveExcludeCategoriesWithNewIdsCategoriesAndCategoriesSavedBefore(): void
    {
        $categoriesIdsInDb = [4, 2];
        $categoriesIdsToRemove = [1, 2];
        $categoriesIdsToSave = [4];
        $this->excludedCategoriesRepository->expects($this->once())
            ->method('getIds')
            ->willReturn($categoriesIdsInDb);
        $this->excludedCategoriesRepository->expects($this->once())
            ->method('update')
            ->with($categoriesIdsToSave);

        $this->excludedCategories->removeExcludeCategories($categoriesIdsToRemove);
    }

    public function testIsExcludedReturnsFalseWhenNoExcludedCategories(): void
    {
        $this->excludedCategoriesRepository->method('getIds')->willReturn([]);

        $this->assertFalse($this->excludedCategories->isExcluded([]));
    }

    public function testIsExcludedReturnsFalseWhenCartIsEmpty(): void
    {
        $this->excludedCategoriesRepository->method('getIds')->willReturn([10, 20]);

        $this->assertFalse($this->excludedCategories->isExcluded([]));
    }

    public function testIsExcludedReturnsFalseWhenNoProductMatchesExcludedCategory(): void
    {
        $this->excludedCategoriesRepository->method('getIds')->willReturn([10, 20]);
        $this->productProxy->method('getCategories')
            ->willReturnOnConsecutiveCalls([5, 6], [7, 8]);

        $this->assertFalse($this->excludedCategories->isExcluded([
            ['id_product' => 1],
            ['id_product' => 2],
        ]));
    }

    public function testIsExcludedReturnsTrueWhenOneProductMatchesExcludedCategory(): void
    {
        $this->excludedCategoriesRepository->method('getIds')->willReturn([10, 20]);
        $this->productProxy->method('getCategories')
            ->willReturnOnConsecutiveCalls([5, 6], [8, 20]); // category 20 is excluded

        $this->assertTrue($this->excludedCategories->isExcluded([
            ['id_product' => 1],
            ['id_product' => 2],
        ]));
    }

    public function testIsExcludedReturnsTrueOnFirstExcludedProduct(): void
    {
        $this->excludedCategoriesRepository->method('getIds')->willReturn([10]);
        $this->productProxy->method('getCategories')
            ->willReturnOnConsecutiveCalls([10], [3]); // excluded at first product

        $this->assertTrue($this->excludedCategories->isExcluded([
            ['id_product' => 1],
            ['id_product' => 2],
        ]));
    }

    public function testIsWidgetDisplayNotEligibleEnabledDelegatesToRepository(): void
    {
        $this->excludedCategoriesRepository->expects($this->once())
            ->method('isWidgetDisplayNotEligibleEnabled')
            ->willReturn(true);

        $this->assertTrue($this->excludedCategories->isWidgetDisplayNotEligibleEnabled());
    }

    public function testGetExcludedMessageDelegatesToRepository(): void
    {
        $this->excludedCategoriesRepository->expects($this->once())
            ->method('getMessage')
            ->with(1)
            ->willReturn('Your cart contains excluded products.');

        $this->assertSame(
            'Your cart contains excluded products.',
            $this->excludedCategories->getExcludedMessage(1)
        );
    }
}
