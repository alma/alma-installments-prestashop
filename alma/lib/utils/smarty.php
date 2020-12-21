<?php

$smarty = Context::getContext()->smarty;

function smarty_prefilter_almaDisplayHtml($source, $template)
{
	return preg_replace(
		['/{almaDisplayHtml}/', '/{\/almaDisplayHtml}/'],
		[
			"{capture name='alma_html'}",
			"{/capture}\n{\$smarty.capture.alma_html|unescape:'html'}",
		],
		$source
	);
}

$smarty->registerFilter('pre', 'smarty_prefilter_almaDisplayHtml');

/**
 * @param $params
 * @param $smarty
 * @return string
 */
function smarty_function_almaFormatPrice($params, $smarty) {
	return almaFormatPrice($params['cents'], isset($params['currency']) ? $params['currency'] : null);
}

smartyRegisterFunction($smarty, 'function', 'almaFormatPrice', 'smarty_function_almaFormatPrice');

function smarty_modifier_almaJsonEncode($value) {
	if (version_compare(_PS_VERSION_, '1.7', '<')) {
		return Tools::jsonEncode($value);
	} else {
		return json_encode($value);
	}
}

smartyRegisterFunction($smarty, 'modifier', 'almaJsonEncode', 'smarty_modifier_almaJsonEncode');
