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

    public function __construct(FormBuilder $formBuilder)
    {
        $this->formBuilder = $formBuilder;
    }

    /**
     * @return array
     */
    public function build(): array
    {
        return $this->formBuilder->build(static::TITLE, static::FIELDS_FORM);
    }
}
