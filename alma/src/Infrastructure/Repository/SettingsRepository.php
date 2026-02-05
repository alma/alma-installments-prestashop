<?php

namespace PrestaShop\Module\Alma\Infrastructure\Repository;

use Configuration;
use Tools;

class SettingsRepository
{
    public function get()
    {
    }

    public function save()
    {
        Configuration::updateValue('ALMA_API_KEY', Tools::getValue('ALMA_API_KEY'));
        Configuration::updateValue('ALMA_API_KEY_LIVE', Tools::getValue('ALMA_API_KEY_LIVE'));
        Configuration::updateValue('ALMA_WIDGET', Tools::getValue('ALMA_WIDGET'));
    }
}
