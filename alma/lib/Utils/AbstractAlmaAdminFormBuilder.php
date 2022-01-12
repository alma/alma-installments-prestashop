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

    /**
     * @param Alma $module
     * @param      $image
     */
    public function __construct(Alma $module, $image)
    {
        $this->module = $module;
        parent::__construct(
            $image,
            $this->getTitle()
        );
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
