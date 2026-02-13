<?php

namespace PrestaShop\Module\Alma\Tests\Mocks;

final class FormExpectedMock
{
    public static function form(): array
    {
        return array_merge([
            'form' => [
                'legend' => [
                    'title' => 'Form title',
                ],
                'input' => [
                    InputExpectedMock::text(),
                ],
                'submit' => [
                    'title' => 'Save',
                    'class' => 'btn btn-default pull-right'
                ],
            ],
        ]);
    }
}
