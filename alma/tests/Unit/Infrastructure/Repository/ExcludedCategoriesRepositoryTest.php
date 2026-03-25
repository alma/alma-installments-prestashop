<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Repository;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Infrastructure\Form\ExcludedCategoriesAdminForm;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\ExcludedCategoriesRepository;

class ExcludedCategoriesRepositoryTest extends TestCase
{
    /**
     * @var ConfigurationRepository
     */
    private $configurationRepository;

    public function setUp(): void
    {
        $this->configurationRepository = $this->createMock(ConfigurationRepository::class);
        $this->excludedCategoriesRepository = new ExcludedCategoriesRepository(
            $this->configurationRepository
        );
    }

    public function testGetIdsWithIdsReturnArrayIds(): void
    {
        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->willReturn('[1,2,3]');

        $this->assertEquals([1, 2, 3], $this->excludedCategoriesRepository->getIds());
    }

    public function testGetIdsWithoutIdsReturnArrayEmpty(): void
    {
        $this->configurationRepository->expects($this->once())
            ->method('get')
            ->willReturn('');

        $this->assertEquals([], $this->excludedCategoriesRepository->getIds());
    }

    public function testUpdateWithIds(): void
    {
        $categoriesIds = [1, 2, 3];

        $this->configurationRepository->expects($this->once())
            ->method('updateValue')
            ->with(
                ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES,
                '[1,2,3]'
            );

        $this->excludedCategoriesRepository->update($categoriesIds);
    }

    public function testUpdateWithoutIds(): void
    {
        $categoriesIds = [];

        $this->configurationRepository->expects($this->once())
            ->method('updateValue')
            ->with(
                ExcludedCategoriesAdminForm::KEY_FIELD_EXCLUDED_CATEGORIES,
                '[]'
            );

        $this->excludedCategoriesRepository->update($categoriesIds);
    }
}
