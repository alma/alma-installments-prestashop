<?php

namespace PrestaShop\Module\Alma\Infrastructure\Grid\Column\Type;

use PrestaShop\PrestaShop\Core\Grid\Column\AbstractColumn;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class IconBooleanColumn extends AbstractColumn
{
    public function getType(): string
    {
        return 'icon_boolean';
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['field'])
            ->setAllowedTypes('field', 'string');
    }
}
