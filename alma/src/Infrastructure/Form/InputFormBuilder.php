<?php

namespace PrestaShop\Module\Alma\Infrastructure\Form;

class InputFormBuilder
{
    /**
     * @param string $type
     * @param string $keyName
     * @param string $label
     * @param bool $required
     * @param array $options
     * @return array
     */
    public function build(string $type, string $keyName, string $label, bool $required = true, array $options = []): array
    {
        $input = [
            'type' => $type,
            'label' => $label,
            'name' => $keyName,
            'required' => $required,
        ];

        if ($type === 'select' && empty($options['options'])) {
            $input = [
                'type' => 'html',
                'name' => 'error_message',
                'html_content' => '<div class="alert alert-danger">Options are required for select input type named: ' . $keyName . '</div>',
            ];
        }

        if (($type === 'checkbox' || $type === 'radio') && empty($options['values'])) {
            $input = [
                'type' => 'html',
                'name' => 'error_message',
                'html_content' => '<div class="alert alert-danger">Values are required for checkbox input type named: ' . $keyName . '</div>',
            ];
        }

        if (!empty($options)) {
            $input = array_merge($input, $options);
        }

        return $input;
    }
}
