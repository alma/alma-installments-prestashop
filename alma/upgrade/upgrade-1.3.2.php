<?php
// File: /upgrade/upgrade-1.3.2.php

use Alma\API\Entities\Webhook;

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_ . "alma/includes/AlmaSettings.php";

function upgrade_module_1_3_2($module)
{
	if (AlmaSettings::isFullyConfigured()) {
		try {
			AlmaCapabilitiesModuleFrontController::registerEndpoint(AlmaSettings::getLiveKey());
		} catch (Exception $e) {
			// pass silently
		}
	}

    return true;
}
