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
        $inputs = [];
        foreach (static::FIELDS_FORM as $key => $field) {
            $inputs[] = $this->inputFormBuilder->build(
                $field['type'],
                $key,
                $field['label'],
                $field['required'],
                $field['options'] ?? []
            );
        }

        return $this->formBuilder->build(static::TITLE, $inputs);
    }
}
