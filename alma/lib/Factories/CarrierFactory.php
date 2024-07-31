<?php
namespace Alma\PrestaShop\Factories;

class CarrierFactory
{

    /**
     * @param int $id
     * @return \Carrier
     */
    public function create($id)
    {
        return new \Carrier($id);
    }
}