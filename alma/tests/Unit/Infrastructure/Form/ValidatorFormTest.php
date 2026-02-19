<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Form;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Infrastructure\Form\ValidatorForm;

class ValidatorFormTest extends TestCase
{
    public function testValidateTextRequiredWithEmptyValue()
    {
        $fields = [
            'field1' => [
                'type' => 'text',
                'required' => true,
            ],
        ];
        $allValues = [
            'extra_value' => 'extra_value',
            'field1' => '',
        ];
        $this->assertEquals(
            ['Invalid Configuration value for field1'],
            ValidatorForm::legacyValidate($fields, $allValues)
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
        $allValues = [
            'extra_value' => 'extra_value',
            'field1' => '',
        ];
        $this->assertEquals(
            [],
            ValidatorForm::legacyValidate($fields, $allValues)
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
        $allValues = [
            'extra_value' => 'extra_value',
            'field1' => 'value',
        ];
        $this->assertEquals(
            [],
            ValidatorForm::legacyValidate($fields, $allValues)
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
        $allValues = [
            'extra_value' => 'extra_value',
            'field1' => 'value1',
            'field2' => '',
        ];
        $this->assertEquals(
            ['Invalid Configuration value for field2'],
            ValidatorForm::legacyValidate($fields, $allValues)
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
        $allValues = [
            'extra_value' => 'extra_value',
            'field1' => '',
            'field2' => '',
        ];
        $this->assertEquals(
            ['Invalid Configuration value for field1', 'Invalid Configuration value for field2'],
            ValidatorForm::legacyValidate($fields, $allValues)
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
        $allValues = [
            'extra_value' => 'extra_value',
            'field1' => 'value1',
            'field2' => 'value2',
        ];
        $this->assertEquals(
            [],
            ValidatorForm::legacyValidate($fields, $allValues)
        );
    }
}
