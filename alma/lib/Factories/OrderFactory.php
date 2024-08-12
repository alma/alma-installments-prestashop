<?php

namespace Alma\PrestaShop\Factories;

class OrderFactory
{
    /**
     * @param $id
     *
     * @return \Order
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function create($id = null, $id_lang = null)
    {
        return new \Order($id, $id_lang);
    }
}
