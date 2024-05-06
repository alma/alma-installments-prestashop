<?php
/**
 * 2018-2023 Alma SAS.
 *
 * THE MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    Alma SAS <contact@getalma.eu>
 * @copyright 2018-2023 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Tests\Unit\Builders;

use Alma\PrestaShop\Builders\PlanHelperBuilder;
use Alma\PrestaShop\Factories\ContextFactory;
use Alma\PrestaShop\Factories\ModuleFactory;
use Alma\PrestaShop\Helpers\CustomFieldsHelper;
use Alma\PrestaShop\Helpers\DateHelper;
use Alma\PrestaShop\Helpers\PlanHelper;
use Alma\PrestaShop\Helpers\SettingsHelper;
use Alma\PrestaShop\Helpers\TranslationHelper;
use PHPUnit\Framework\TestCase;

class PlanHelperBuilderTest extends TestCase
{
    /**
     * @var PlanHelperBuilder
     */
    protected $planHelperBuilder
    ;

    public function setUp()
    {
        $this->planHelperBuilder = new PlanHelperBuilder();
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf(PlanHelper::class, $this->planHelperBuilder->getInstance());
    }

    public function testGetModuleFactory()
    {
        $this->assertInstanceOf(ModuleFactory::class, $this->planHelperBuilder->getModuleFactory());
        $this->assertInstanceOf(ModuleFactory::class, $this->planHelperBuilder->getModuleFactory(
            new ModuleFactory()
        ));
    }

    public function testGetContextFactory()
    {
        $this->assertInstanceOf(ContextFactory::class, $this->planHelperBuilder->getContextFactory());
        $this->assertInstanceOf(ContextFactory::class, $this->planHelperBuilder->getContextFactory(
            new ContextFactory()
        ));
    }

    public function testGetDateHelper()
    {
        $this->assertInstanceOf(DateHelper::class, $this->planHelperBuilder->getDateHelper());
        $this->assertInstanceOf(DateHelper::class, $this->planHelperBuilder->getDateHelper(
            new DateHelper()
        ));
    }

    public function testGetSettingsHelper()
    {
        $this->assertInstanceOf(SettingsHelper::class, $this->planHelperBuilder->getSettingsHelper());
        $this->assertInstanceOf(SettingsHelper::class, $this->planHelperBuilder->getSettingsHelper(
            $this->createMock(SettingsHelper::class)
        ));
    }

    public function testGetCustomFieldsHelper()
    {
        $this->assertInstanceOf(CustomFieldsHelper::class, $this->planHelperBuilder->getCustomFieldsHelper());
        $this->assertInstanceOf(CustomFieldsHelper::class, $this->planHelperBuilder->getCustomFieldsHelper(
            $this->createMock(CustomFieldsHelper::class)
        ));
    }

    public function testgetTranslationHelper()
    {
        $this->assertInstanceOf(TranslationHelper::class, $this->planHelperBuilder->getTranslationHelper());
        $this->assertInstanceOf(TranslationHelper::class, $this->planHelperBuilder->getTranslationHelper(
            $this->createMock(TranslationHelper::class)
        ));
    }
}
