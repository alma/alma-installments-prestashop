<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class InputFormBuilder
{
    public function build($type, $keyName, $label, $required = true, $options = []): array
    {
        $input = [
            'type' => $type,
            'label' => $label,
            'name' => $keyName,
            'size' => 20,
            'required' => $required,
        ];

        return $input;
    }
}
