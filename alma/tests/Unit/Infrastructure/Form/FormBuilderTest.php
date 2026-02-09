<?php

namespace PrestaShop\Module\Alma\Tests\Unit\Infrastructure\Form;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\Alma\Infrastructure\Form\FormBuilder;
use PrestaShop\Module\Alma\Tests\Mocks\FormExpectedMother;

class FormBuilderTest extends TestCase
{
    /**
     * @var FormBuilder
     */
    private FormBuilder $formBuilder;

    public function setUp(): void
    {
        $this->formBuilder = new FormBuilder();
    }

    public function testBuildForm()
    {
        $this->assertEquals(FormExpectedMother::form(), $this->formBuilder->build('Form title'));
    }
}
