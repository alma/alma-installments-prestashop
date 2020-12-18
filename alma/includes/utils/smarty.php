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

function smarty_function_almaFormatPrice($params, $smarty) {
	$legacy = version_compare(_PS_VERSION_, '1.7.6.0', '<');
	$price = $params['price'];

	if (array_key_exists('currency', $params))
	{
		$currency = Currency::getCurrencyInstance((int)($params['currency']));
		if (Validate::isLoadedObject($currency))
			if ($legacy) {
				return Tools::displayPrice($price, $currency, false);
			} else {
				return Locale::formatPrice($price, $currency->iso_code);
			}
	}
	return $legacy ? Tools::displayPrice($price) : Locale::formatPrice($price, Context::getContext()->currency->iso_code);
}

$smarty = Context::getContext()->smarty;
$smarty->registerFilter('pre', 'smarty_prefilter_almaDisplayHtml');
smartyRegisterFunction($smarty, 'function', 'almaFormatPrice', 'smarty_function_almaFormatPrice');
