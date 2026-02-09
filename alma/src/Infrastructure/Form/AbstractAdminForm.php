<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

abstract class AbstractAdminForm
{
    /**
     * @var FormBuilder
     */
    protected FormBuilder $formBuilder;
    /**
     * @var InputFormBuilder
     */
    protected InputFormBuilder $inputFormBuilder;

    public function __construct(FormBuilder $formBuilder, InputFormBuilder $inputFormBuilder)
    {
        $this->formBuilder = $formBuilder;
        $this->inputFormBuilder = $inputFormBuilder;
    }

    abstract public function build(): array;
}
