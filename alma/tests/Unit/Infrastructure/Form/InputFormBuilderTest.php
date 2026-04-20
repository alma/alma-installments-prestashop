<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Form;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Infrastructure\Form\InputFormBuilder;
use PrestaShop\Module\Alma\Tests\Mocks\InputExpectedMock;

class InputFormBuilderTest extends TestCase
{
    /**
     * @var \PrestaShop\Module\Alma\Infrastructure\Form\InputFormBuilder
     */
    private InputFormBuilder $inputFormBuilder;

    public function setUp(): void
    {
        $this->inputFormBuilder = new InputFormBuilder();
    }

    public function testBuildInputText(): void
    {
        $result = $this->inputFormBuilder->build(
            'text',
            'KEY_NAME',
            'Label title',
            true,
            [
                'size' => 20,
                'desc' => 'Optional description',
            ]
        );

        $this->assertEquals(InputExpectedMock::text(), $result);
    }

    public function testBuildInputSelectWithoutOptionsThrowException(): void
    {
        $result = $this->inputFormBuilder->build(
            'select',
            'KEY_NAME',
            'Label title',
            false
        );
        $this->assertEquals(
            [
                'type' => 'html',
                'name' => 'error_message',
                'html_content' => '<div class="alert alert-danger">Options are required for select input type named: KEY_NAME</div>',
            ],
            $result
        );
    }

    public function testBuildInputSelect(): void
    {
        $result = $this->inputFormBuilder->build(
            'select',
            'KEY_NAME',
            'Label title',
            false,
            [
                'options' => [
                    'query' => [
                        ['id_option' => 1, 'name' => 'Option 1'],
                        ['id_option' => 2, 'name' => 'Option 2'],
                    ],
                    'id' => 'id_option',
                    'name' => 'name_option',
                ],
            ]
        );

        $this->assertEquals(InputExpectedMock::select(), $result);
    }

    public function testBuildInputCheckboxWithoutValuesReturnErrorMessage(): void
    {
        $result = $this->inputFormBuilder->build(
            'checkbox',
            'KEY_NAME',
            'Label title',
            false
        );
        $this->assertEquals(
            [
                'type' => 'html',
                'name' => 'error_message',
                'html_content' => '<div class="alert alert-danger">Values are required for checkbox input type named: KEY_NAME</div>',
            ],
            $result
        );
    }

    public function testBuildInputCheckbox(): void
    {
        $result = $this->inputFormBuilder->build(
            'checkbox',
            'KEY_NAME',
            'Label title',
            true,
            [
                'values' => [
                    'query' => [
                        ['id_option' => 1, 'name' => 'Option 1'],
                        ['id_option' => 2, 'name' => 'Option 2'],
                    ],
                    'id' => 'id_option',
                    'name' => 'name_option',
                ],
            ]
        );

        $this->assertEquals(InputExpectedMock::checkbox(), $result);
    }

    public function testBuildInputRadioWithoutValuesThrowException(): void
    {
        $result = $this->inputFormBuilder->build(
            'radio',
            'KEY_NAME',
            'Label title',
            false
        );
        $this->assertEquals(
            [
                'type' => 'html',
                'name' => 'error_message',
                'html_content' => '<div class="alert alert-danger">Values are required for checkbox input type named: KEY_NAME</div>',
            ],
            $result
        );
    }

    public function testBuildInputRadio(): void
    {
        $result = $this->inputFormBuilder->build(
            'radio',
            'KEY_NAME',
            'Label title',
            true,
            [
                'values' => [
                    ['id' => 'active_on', 'value' => 1, 'label' => 'Enabled'],
                    ['id' => 'active_off', 'value' => 0, 'label' => 'Disabled']
                ],
                'class' => 'my_class',
                'is_bool' => true,
            ]
        );

        $this->assertEquals(InputExpectedMock::radio(), $result);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
