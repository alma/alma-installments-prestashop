<?php

namespace Alma\PrestaShop\Utils;

use Alma;
use Context;

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
     * @var Context
     */
    protected $context;

    protected $config;
    

    /**
     * @param Alma      $module
     * @param Context   $context
     * @param string    $image
     * @param array     $config
     */
    public function __construct(Alma $module, Context $context, $image, $config = [])
    {
        $this->module = $module;
        $this->context = $context;
        $this->config = $config;
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
