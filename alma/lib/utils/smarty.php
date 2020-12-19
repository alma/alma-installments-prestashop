<?php

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

/**
 * @param $params
 * @param $smarty
 * @return string
 */
function smarty_function_almaFormatPrice($params, $smarty) {
	return almaFormatPrice($params['cents'], isset($params['currency']) ? $params['currency'] : null);
}

$smarty = Context::getContext()->smarty;
$smarty->registerFilter('pre', 'smarty_prefilter_almaDisplayHtml');
smartyRegisterFunction($smarty, 'function', 'almaFormatPrice', 'smarty_function_almaFormatPrice');
