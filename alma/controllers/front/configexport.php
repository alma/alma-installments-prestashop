<?php

use Alma\API\Lib\PayloadFormatter;
use Alma\PrestaShop\Traits\AjaxTrait;
use Alma\API\Entities\MerchantData\CmsInfo;
use Alma\API\Entities\MerchantData\CmsFeatures;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * AlmaConfiguration_ExportModuleFrontController
 */
class AlmaConfigExportModuleFrontController extends ModuleFrontController
{
    use AjaxTrait;

    public function __construct()
    {
        parent::__construct();
    }

    public function postProcess()
    {
        parent::postProcess();
        $cmsInfoDataArray = [];
        $cmsFeatureDataArray = [];
        $cmsInfo = new CmsInfo([$cmsInfoDataArray]);
        $cmsFeature = new CmsFeatures($cmsFeatureDataArray);

        $payload = (new PayloadFormatter())->formatConfigurationPayload($cmsInfo, $cmsFeature);

        $this->ajaxRenderAndExit(json_encode(['success' =>  $payload ]));

    }
}