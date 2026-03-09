<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Form;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\FeePlansException;
use PrestaShop\Module\Alma\Infrastructure\Form\ValidatorForm;
use PrestaShop\Module\Alma\Tests\Mocks\FeePlansMock;

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

    public function testValidateSelectWithoutRequiredField()
    {
        $fields = [
            'field1' => [
                'type' => 'select',
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

    public function testValidateTextWithMultiLangRequiredWithEmptyValue()
    {
        $fields = [
            'field1' => [
                'type' => 'text',
                'required' => true,
                'lang' => true,
            ],
        ];
        $allValues = [
            'extra_value' => 'extra_value',
            'field1_1' => '',
            'field1_2' => '',
        ];
        $this->assertEquals(
            ['Invalid Configuration value for field1_1', 'Invalid Configuration value for field1_2'],
            ValidatorForm::legacyValidate($fields, $allValues)
        );
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testCheckLimitAmountPlanMaxAmountExceededThrowException()
    {
        $feePlan = FeePlansMock::feePlan(2);
        $this->expectException(FeePlansException::class);
        $this->expectExceptionMessage('The maximum purchase amount cannot be higher than the maximum allowed by Alma.');
        ValidatorForm::checkLimitAmountPlan($feePlan, 10000, 200100);
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     * @throws \PrestaShop\Module\Alma\Application\Exception\FeePlansException
     */
    public function testCheckLimitAmountPlanMinAmountExceededThrowException()
    {
        $feePlan = FeePlansMock::feePlan(2);
        $this->expectException(FeePlansException::class);
        $this->expectExceptionMessage('The minimum purchase amount cannot be lower than the minimum allowed by Alma.');
        ValidatorForm::checkLimitAmountPlan($feePlan, 5000, 200000);
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testCheckLimitAmountPlanMinAmountExceededMaxAmountThrowException()
    {
        $feePlan = FeePlansMock::feePlan(2);
        $this->expectException(FeePlansException::class);
        $this->expectExceptionMessage('The minimum purchase amount cannot be higher than the maximum.');
        ValidatorForm::checkLimitAmountPlan($feePlan, 200100, 200000);
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testCheckLimitAmountPlanMaxAmountExceededMinAmountThrowException()
    {
        $feePlan = FeePlansMock::feePlan(2);
        $this->expectException(FeePlansException::class);
        $this->expectExceptionMessage('The maximum purchase amount cannot be lower than the minimum.');
        ValidatorForm::checkLimitAmountPlan($feePlan, 10000, 5000);
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     * @throws \PrestaShop\Module\Alma\Application\Exception\FeePlansException
     */
    public function testCheckLimitAmountPlanWithRightAmountReturnVoid()
    {
        $feePlan = FeePlansMock::feePlan(2);
        $this->assertNull(ValidatorForm::checkLimitAmountPlan($feePlan, 10000, 200000));
    }
}
