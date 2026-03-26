function initAlmaWidget($, Alma) {
    if (!$("#alma-widget-ShoppingCartFooter").length && !$("#alma-widget-cart").length) {
        return null;
    }
    let widgetConfig = $("#alma-widget-ShoppingCartFooter").data('widget-config');
    if ($("#alma-widget-cart").length) {
        widgetConfig = $("#alma-widget-cart").data('widget-config');
    }

    const mode = (widgetConfig.mode === 'test') ? Alma.ApiMode.TEST : Alma.ApiMode.LIVE
    const widgets = Alma.Widgets.initialize(
        widgetConfig.merchantId,
        mode,
    )

    if (typeof widgetConfig.plans === 'string') {
        widgetConfig.plans = JSON.parse(widgetConfig.plans);
    }

    widgets.add(Alma.Widgets.PaymentPlans, {
        container: widgetConfig.containerId,
        purchaseAmount: widgetConfig.purchaseAmount,
        locale: widgetConfig.locale,
        hideIfNotEligible: widgetConfig.hideIfNotEligible,
        plans: widgetConfig.plans,
    })

    return widgets;
}

if (typeof module !== 'undefined') {
    console.log('Exporting initAlmaWidget for testing');
    module.exports = { initAlmaWidget };
} else {
    console.log('Initializing Alma Widget on page load');
    (function ($) {
        $(function () {
            initAlmaWidget($, Alma);
        });
    })(jQuery);
}
