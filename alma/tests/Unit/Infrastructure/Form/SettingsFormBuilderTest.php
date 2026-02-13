<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Form;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Infrastructure\Form\SettingsFormBuilder;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;
use PrestaShop\Module\Alma\Tests\Mocks\FormExpectedMock;

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
        $this->settingsFormBuilder = new SettingsFormBuilder(
            $this->module,
            $this->helperForm,
            $this->settingsRepository
        );
    }

    public function testBuildSettingsForm(): void
    {
        $token = 'token';
        $defaultLang = 1;
        $forms = FormExpectedMock::form();
        $this->settingsRepository->expects($this->once())
            ->method('get')
            ->willReturn(['field1' => 'value1']);
        $this->helperForm->expects($this->once())
            ->method('generateForm')
            ->with($forms)
            ->willReturn('form_html');
        $this->assertEquals('form_html', $this->settingsFormBuilder->render($token, $defaultLang, $forms));
    }

    public function tearDown(): void
    {
        $this->module = null;
        $this->helperForm = null;
        $this->settingsRepository = null;
        $this->settingsFormBuilder = null;
    }
}
