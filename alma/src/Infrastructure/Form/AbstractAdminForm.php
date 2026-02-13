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
     * Get all fields from all child classes in a namespace
     *
     * @return array
     */
    public static function getAllFieldsFromNamespace(): array
    {
        $allFields = [];

        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, self::class) &&
                strpos($class, __NAMESPACE__) === 0 &&
                defined("{$class}::FIELDS_FORM")) {
                $allFields = array_merge($allFields, constant("{$class}::FIELDS_FORM"));
            }
        }

        return $allFields;
    }

    /**
     * @return array
     */
    public function build(): array
    {
        return $this->formBuilder->build(static::TITLE, static::FIELDS_FORM);
    }
}
