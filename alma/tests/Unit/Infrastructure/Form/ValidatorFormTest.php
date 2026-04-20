<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Form;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Application\Exception\FeePlansException;
use PrestaShop\Module\Alma\Infrastructure\Form\ValidatorForm;
use PrestaShop\Module\Alma\Tests\Mocks\FeePlansMock;
use PrestaShopBundle\Translation\Translator;

class ValidatorFormTest extends TestCase
{
    /**
     * @var ValidatorForm
     */
    private ValidatorForm $validatorForm;
    /**
     * @var Translator
     */
    private $translator;

    public function setUp(): void
    {
        $this->translator = $this->createMock(Translator::class);
        $this->validatorForm = new ValidatorForm(
            $this->translator
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
        $languages = [
            ['id_lang' => 1],
            ['id_lang' => 2],
        ];
        $allValues = [
            'extra_value' => 'extra_value',
            'field1_1' => '',
            'field1_2' => '',
        ];
        $this->assertEquals(
            ['Invalid Configuration value for field1_1', 'Invalid Configuration value for field1_2'],
            ValidatorForm::legacyValidate($fields, $allValues, $languages)
        );
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testCheckLimitAmountPlanMaxAmountExceededThrowException()
    {
        $feePlan = FeePlansMock::feePlan(2);
        $this->translator->expects($this->once())
            ->method('trans')
            ->willReturn('The maximum purchase amount cannot be higher than the maximum allowed by Alma.');
        $this->expectException(FeePlansException::class);
        $this->expectExceptionMessage('The maximum purchase amount cannot be higher than the maximum allowed by Alma.');
        $this->validatorForm->checkLimitAmountPlan($feePlan, 10000, 200100);
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     * @throws \PrestaShop\Module\Alma\Application\Exception\FeePlansException
     */
    public function testCheckLimitAmountPlanMinAmountExceededThrowException()
    {
        $feePlan = FeePlansMock::feePlan(2);
        $this->translator->expects($this->once())
            ->method('trans')
            ->willReturn('The minimum purchase amount cannot be lower than the minimum allowed by Alma.');
        $this->expectException(FeePlansException::class);
        $this->expectExceptionMessage('The minimum purchase amount cannot be lower than the minimum allowed by Alma.');
        $this->validatorForm->checkLimitAmountPlan($feePlan, 5000, 200000);
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testCheckLimitAmountPlanMinAmountExceededMaxAmountThrowException()
    {
        $feePlan = FeePlansMock::feePlan(2);
        $this->translator->expects($this->once())
            ->method('trans')
            ->willReturn('The minimum purchase amount cannot be higher than the maximum.');
        $this->expectException(FeePlansException::class);
        $this->expectExceptionMessage('The minimum purchase amount cannot be higher than the maximum.');
        $this->validatorForm->checkLimitAmountPlan($feePlan, 200100, 200000);
    }

    /**
     * @throws \Alma\Client\Application\Exception\ParametersException
     */
    public function testCheckLimitAmountPlanMaxAmountExceededMinAmountThrowException()
    {
        $feePlan = FeePlansMock::feePlan(2);
        $this->translator->expects($this->once())
            ->method('trans')
            ->willReturn('The maximum purchase amount cannot be lower than the minimum.');
        $this->expectException(FeePlansException::class);
        $this->expectExceptionMessage('The maximum purchase amount cannot be lower than the minimum.');
        $this->validatorForm->checkLimitAmountPlan($feePlan, 10000, 5000);
    }

    public function testCheckLimitAmountPlanWithRightAmountReturnVoid()
    {
        $feePlan = FeePlansMock::feePlan(2);
        $this->assertNull($this->validatorForm->checkLimitAmountPlan($feePlan, 10000, 200000));
    }
}
