<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Form;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Infrastructure\Form\FormBuilder;
use PrestaShop\Module\Alma\Infrastructure\Form\InputFormBuilder;
use PrestaShop\Module\Alma\Tests\Mocks\FormExpectedMock;
use PrestaShop\Module\Alma\Tests\Mocks\InputExpectedMock;

class FormBuilderTest extends TestCase
{
    /**
     * @var FormBuilder
     */
    private FormBuilder $formBuilder;
    /**
     * @var InputFormBuilder
     */
    private $inputFormBuilder;

    public function setUp(): void
    {
        $this->inputFormBuilder = $this->createMock(InputFormBuilder::class);
        $this->formBuilder = new FormBuilder(
            $this->inputFormBuilder,
        );
    }

    public function testBuildForm()
    {
        $formFields = [
            'KEY_NAME' => [
                'type' => 'text',
                'label' => 'Label text',
                'required' => true,
                'form' => 'form',
                'options' => [
                    'size' => 20,
                    'desc' => 'Optional description',
                ]
            ]
        ];
        $this->inputFormBuilder->expects($this->once())
            ->method('build')
            ->with(
                $formFields['KEY_NAME']['type'],
                key($formFields),
                $formFields['KEY_NAME']['label'],
                $formFields['KEY_NAME']['required'],
                $formFields['KEY_NAME']['options']
            )
            ->willReturn(InputExpectedMock::text());
        $this->assertEquals(FormExpectedMock::form(), $this->formBuilder->build(
            'Form title',
            $formFields
        ));
    }

    public function tearDown(): void
    {
        $this->inputFormBuilder = null;
    }
}
