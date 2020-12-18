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

	$currency = Context::getContext()->currency;
	if (array_key_exists('currency', $params))
	{
		$paramCurrency = Currency::getCurrencyInstance((int)($params['currency']));
		if (Validate::isLoadedObject($paramCurrency)) {
			$currency = $paramCurrency;
		}
	}

	if ($legacy) {
		return Tools::displayPrice($price, $currency, false);
	} else {
		$locale = Context::getContext()->currentLocale;
		return $locale->formatPrice($price, $currency->iso_code);
	}
}

$smarty = Context::getContext()->smarty;
$smarty->registerFilter('pre', 'smarty_prefilter_almaDisplayHtml');
smartyRegisterFunction($smarty, 'function', 'almaFormatPrice', 'smarty_function_almaFormatPrice');
