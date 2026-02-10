<?php

namespace PrestaShop\Module\Alma\Tests\Integration\Application\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Service\SettingsService;
use PrestaShop\Module\Alma\Infrastructure\Form\SettingsFormBuilder;
use PrestaShop\Module\Alma\Infrastructure\Repository\SettingsRepository;
use PrestaShop\Module\Alma\Infrastructure\Repository\ToolsRepository;

class SettingsServiceTest extends TestCase
{
    public function setup(): void
    {
        $this->module = $this->createMock(\Module::class);
        $this->settingsFormBuidler = $this->createMock(SettingsFormBuilder::class);
        $this->settings = $this->createMock(SettingsRepository::class);
        $this->tools = $this->createMock(ToolsRepository::class);
        $this->settingsService = new SettingsService(
            $this->module,
            $this->settingsFormBuidler,
            $this->settings,
            $this->tools
        );
    }

    public function testValidateTextRequiredWithEmptyValue()
    {
        $fields = [
            'field1' => [
                'type' => 'text',
                'required' => true,
            ],
        ];
        $this->tools->expects($this->once())
            ->method('getValue')
            ->with('field1')
            ->willReturn('');
        $this->assertEquals(
            ['Invalid Configuration value for field1'],
            $this->settingsService->validate($fields)
        );
    }

    public function testValidateTextNotRequiredWithEmptyValue()
    {
        $fields = [
            'field1' => [
                'type' => 'text',
                'required' => false,
            ],
        ];
        $this->tools->expects($this->never())
            ->method('getValue')
            ->with('field1')
            ->willReturn('');
        $this->assertEquals(
            [],
            $this->settingsService->validate($fields)
        );
    }

    public function testValidateSelectRequiredWithStringValue()
    {
        $fields = [
            'field1' => [
                'type' => 'select',
                'required' => true,
            ],
        ];
        $this->tools->expects($this->once())
            ->method('getValue')
            ->with('field1')
            ->willReturn('value');
        $this->assertEquals(
            [],
            $this->settingsService->validate($fields)
        );
    }

    public function testValidateTwoTextRequiredOneWithStringValueAndOneWithEmptyValue()
    {
        $fields = [
            'field1' => [
                'type' => 'text',
                'required' => true,
            ],
            'field2' => [
                'type' => 'text',
                'required' => true,
            ]
        ];
        $this->tools->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(['field1'], ['field2'])
            ->willReturnOnConsecutiveCalls('value1', '');
        $this->assertEquals(
            ['Invalid Configuration value for field2'],
            $this->settingsService->validate($fields)
        );
    }

    public function testValidateTwoTextRequiredBothWithEmptyValue()
    {
        $fields = [
            'field1' => [
                'type' => 'text',
                'required' => true,
            ],
            'field2' => [
                'type' => 'text',
                'required' => true,
            ]
        ];
        $this->tools->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(['field1'], ['field2'])
            ->willReturnOnConsecutiveCalls('', '');
        $this->assertEquals(
            ['Invalid Configuration value for field1', 'Invalid Configuration value for field2'],
            $this->settingsService->validate($fields)
        );
    }

    public function testValidateTextAndSelectRequiredWithStringValue()
    {
        $fields = [
            'field1' => [
                'type' => 'text',
                'required' => true,
            ],
            'field2' => [
                'type' => 'select',
                'required' => true,
            ]
        ];
        $this->tools->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(['field1'], ['field2'])
            ->willReturnOnConsecutiveCalls('value1', 'value2');
        $this->assertEquals(
            [],
            $this->settingsService->validate($fields)
        );
    }
}
