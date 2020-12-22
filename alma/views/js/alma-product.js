/**
 * 2018-2020 Alma SAS
 *
 * THE MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    Alma SAS <contact@getalma.eu>
 * @copyright 2018-2020 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

(function ($) {
    $(function () {
        var selectors = window.__alma_widgetQuerySelectors;

        function initWidget(merchantId, apiMode, containerId, purchaseAmount, plans) {
            var widgets = Alma.Widgets.initialize(merchantId, apiMode);

            widgets.add(Alma.Widgets.PaymentPlans, {
                container: '#' + containerId,
                purchaseAmount: purchaseAmount,
                plans: plans
            });

            widgets.render();
        }

        function refreshWidgets() {
            $(".alma-pp-container").each(function () {
                var $widget = $(this).find(".alma-widget-container");
                if (!$widget.length) {
                    return;
                }

                var settings = $widget.data("settings");

                var purchaseAmount = settings.amount;
                if (settings.refreshPrice) {
                    var $price = $(selectors.price);
                    if ($price.length === 0) {
                        throw new Error(
                            'Could not find price element with query selector "' +
                            selectors.price +
                            '"'
                        )
                    }

                    purchaseAmount = $price.text()
                        .replace(settings.thousandSeparator, '')
                        .replace(settings.decimalSeparator, '.')
                        .replace(/[^\d.]/g, '');

                    purchaseAmount = Alma.Utils.priceToCents(purchaseAmount);

                    var $quantityWanted = $(selectors.quantity);
                    if ($quantityWanted.length) {
                        purchaseAmount *= Number($quantityWanted.val());
                    }
                }

                initWidget(
                    settings.merchantId,
                    settings.apiMode,
                    $widget.attr("id"),
                    purchaseAmount,
                    settings.plans,
                )
            })
        }

        if (window.prestashop != null && window.prestashop.on != null) {
            prestashop.on("updatedProduct", refreshWidgets);
        } else {
            let $body = $("body");

            function delayedRefresh() {
                setTimeout(refreshWidgets, 1);
            }

            $body.on("change", selectors.attrSelect, delayedRefresh);
            $body.on("click", selectors.attrRadio, delayedRefresh);
            $body.on("click", selectors.colorPick, delayedRefresh);
            $body.on("change", selectors.quantity, delayedRefresh);
        }

        refreshWidgets();
        window.__alma_refreshWidgets = refreshWidgets;
    });
})(jQuery)
