<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class FormBuilder
{
    public function build($title, $inputs = []): array
    {
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
