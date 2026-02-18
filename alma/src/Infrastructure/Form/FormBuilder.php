<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class FormBuilder
{
    /**
     * @var InputFormBuilder
     */
    private InputFormBuilder $inputFormBuilder;

    public function __construct(InputFormBuilder $inputFormBuilder)
    {
        $this->inputFormBuilder = $inputFormBuilder;
    }

    /**
     * Build the form array for PrestaShop HelperForm
     *
     * @param string $title
     * @param array $fieldsForm
     *
     * @return array
     */
    public function build(string $title, array $fieldsForm): array
    {
        $inputs = [];
        foreach ($fieldsForm as $key => $field) {
            $inputs[] = $this->inputFormBuilder->build(
                $field['type'],
                $key,
                $field['label'],
                $field['required'],
                $field['options'] ?? []
            );
        }

        return [
            'form' => [
                'legend' => [
                    'title' => $title,
                ],
                'input' => $inputs,
                'submit' => [
                    'title' => 'Save',
                    'class' => 'btn btn-default pull-right'
                ],
            ],
        ];
    }
}
