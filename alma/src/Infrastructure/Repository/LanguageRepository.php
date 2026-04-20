<?php

namespace PrestaShop\Module\Alma\Infrastructure\Repository;

use Language;

class LanguageRepository
{
    /**
     * @return array
     */
    public function getActiveLanguages(): array
    {
        return Language::getLanguages();
    }
}
