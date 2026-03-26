(function ($) {
    $(function () {
        if (!$("#alma-widget-ShoppingCartFooter").length && !$("#alma-widget-cart").length) {
            return;
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

        widgets.add(Alma.Widgets.PaymentPlans, {
            container: widgetConfig.containerId,
            purchaseAmount: widgetConfig.purchaseAmount,
            locale: widgetConfig.locale,
            hideIfNotEligible: widgetConfig.hideIfNotEligible,
            plans: widgetConfig.plans,
        })
    });
})(jQuery);
