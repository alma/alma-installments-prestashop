<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

abstract class AbstractAdminForm
{
    /**
     * @var FormBuilder
     */
    protected FormBuilder $formBuilder;

    public function __construct(FormBuilder $formBuilder)
    {
        $this->formBuilder = $formBuilder;
    }

    abstract public static function fieldsForm(string $templateHtml = '', array $dynamicForm = []): array;

    abstract public static function title(): string;

    /**
     * @param string $templateHtml
     * @param array $dynamicForm
     * @return array
     */
    public function build(string $templateHtml = '', array $dynamicForm = []): array
    {
        return $this->formBuilder->build(static::title(), static::fieldsForm($templateHtml, $dynamicForm));
    }
}
