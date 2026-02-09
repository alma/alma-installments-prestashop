<?php

namespace PrestaShop\Module\Alma\Tests\Mocks;

final class InputExpectedMother
{
    public static function text(array $overrides = []): array
    {
        return array_merge([
            'type' => 'text',
            'label' => 'Label title',
            'name' => 'KEY_NAME',
            'size' => 20,
            'desc' => 'Optional description',
            'required' => true,
        ], $overrides);
    }

    public static function select(array $overrides = []): array
    {
        return array_merge([
            'type' => 'select',
            'label' => 'Label title',
            'name' => 'KEY_NAME',
            'required' => false,
            'options' => [
                'query' => [
                    ['id_option' => 1, 'name' => 'Option 1'],
                    ['id_option' => 2, 'name' => 'Option 2'],
                ],
                'id' => 'id_option',
                'name' => 'name_option',
            ],
        ], $overrides);
    }

    public static function checkbox(array $overrides = []): array
    {
        return array_merge([
            'type' => 'checkbox',
            'label' => 'Label title',
            'name' => 'KEY_NAME',
            'required' => true,
            'values' => [
                'query' => [
                    ['id_option' => 1, 'name' => 'Option 1'],
                    ['id_option' => 2, 'name' => 'Option 2'],
                ],
                'id' => 'id_option',
                'name' => 'name_option',
            ],
        ], $overrides);
    }

    public static function radio(array $overrides = []): array
    {
        return array_merge([
            'type' => 'radio',
            'label' => 'Label title',
            'name' => 'KEY_NAME',
            'required' => true,
            'values' => [
                ['id' => 'active_on', 'value' => 1, 'label' => 'Enabled'],
                ['id' => 'active_off', 'value' => 0, 'label' => 'Disabled']
            ],
            'class' => 'my_class',
            'is_bool' => true,
        ], $overrides);
    }
}
