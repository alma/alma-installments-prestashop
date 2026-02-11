<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Form;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Infrastructure\Form\SettingsFormBuilder;
use PrestaShop\Module\Alma\Infrastructure\Repository\ConfigurationRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\ToolsRepository;
use PrestaShop\Module\Alma\Tests\Mocks\FormExpectedMother;

class SettingsFormBuilderTest extends TestCase
{
    public function setUp(): void
    {
        $this->module = $this->createMock(\Module::class);
        $this->module->name = 'alma';
        $this->helperForm = $this->createMock(\HelperForm::class);
        $this->helperForm->table = $this->module->name;
        $this->helperForm->name_controller = $this->module->name;
        $this->helperForm->submit = 'submit' . $this->module->name;
        $this->settingsRepository = $this->createMock(SettingsRepository::class);
        $this->tools = $this->createMock(ToolsRepository::class);
        $this->configuration = $this->createMock(ConfigurationRepository::class);
        $this->settingsFormBuilder = new SettingsFormBuilder(
            $this->module,
            $this->helperForm,
            $this->settingsRepository,
            $this->tools,
            $this->configuration
        );
    }

    public function testBuildSettingsForm(): void
    {
        $forms = FormExpectedMother::form();
        $this->tools->expects($this->once())
            ->method('getAdminTokenLite')
            ->with('AdminAlmaSettings')
            ->willReturn('token');
        $this->configuration->expects($this->once())
            ->method('get')
            ->with('PS_LANG_DEFAULT')
            ->willReturn('1');
        $this->settingsRepository->expects($this->once())
            ->method('get')
            ->willReturn(['field1' => 'value1']);
        $this->helperForm->expects($this->once())
            ->method('generateForm')
            ->with($forms)
            ->willReturn('form_html');
        $this->assertEquals('form_html', $this->settingsFormBuilder->build($forms));
    }

    public function tearDown(): void
    {
        $this->module = null;
        $this->helperForm = null;
        $this->settingsRepository = null;
        $this->tools = null;
        $this->configuration = null;
        $this->settingsFormBuilder = null;
    }
}
