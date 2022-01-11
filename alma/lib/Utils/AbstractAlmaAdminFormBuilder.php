<?php

namespace Alma\PrestaShop\Utils;

use Alma;

/**
 * Class AbstractAlmaAdminFormBuilder
 *
 * @package Alma\PrestaShop\Utils
 */
abstract class AbstractAlmaAdminFormBuilder extends AbstractAdminFormBuilder
{

    /**
     * @var Alma
     */
    protected $module;

    public function __construct(Alma $module, $image)
    {
        AbstractAdminFormBuilder::__construct(
            $image,
            $this->getTitle()
        );
        $this->module = $module;
    }

    protected function getSubmitTitle()
    {
        return $this->module->l('Save');
    }

    /**
     * @return mixed
     */
    abstract protected function getTitle();
}
