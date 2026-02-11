<?php

namespace PrestaShop\Module\Alma\Tests\Mocks;

final class FormExpectedMother
{
    public static function form(): array
    {
        return array_merge([
            'form' => [
                'legend' => [
                    'title' => 'Form title',
                ],
                'input' => [
                    InputExpectedMother::text(),
                ],
                'submit' => [
                    'title' => 'Save',
                    'class' => 'btn btn-default pull-right'
                ],
            ],
        ]);
    }
}
