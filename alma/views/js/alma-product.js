/**
 * 2018-2021 Alma SAS
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
 * @copyright 2018-2021 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

(function ($) {
    $(function () {
        function initWidget(
            merchantId,
            apiMode,
            containerId,
            purchaseAmount,
            plans
        ) {
            var widgets = Alma.Widgets.initialize(merchantId, apiMode);

            widgets.add(Alma.Widgets.PaymentPlans, {
                container: containerId,
                purchaseAmount: purchaseAmount,
                plans: plans,
            });

            widgets.render();
        }

        function debounce(func, wait, immediate) {
            var timeout;
            return function () {
                var context = this,
                    args = arguments;
                var later = function () {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        }

        var refreshWidgets = debounce(function (evt, initialRefresh) {
            var selectors = JSON.parse($("#alma-widget-config").val());
            $(".alma-pp-container").each(function () {
                var $widget = $(this).find(".alma-widget-container");
                if (!$widget.length) {
                    return;
                }

                var settings = $widget.data("settings");

                var purchaseAmount = settings.amount;
                if (!initialRefresh || settings.refreshPrice) {
                    var $price = $(selectors.price);
                    if ($price.length === 0) {
                        throw new Error(
                            'Could not find price element with query selector "' +
                                selectors.price +
                                '"'
                        );
                    }

                    purchaseAmount = $price
                        .text()
                        .replace(settings.thousandSeparator, "")
                        .replace(settings.decimalSeparator, ".")
                        .replace(/[^\d.]/g, "");

                    purchaseAmount = Alma.Utils.priceToCents(purchaseAmount);

                    var $quantityWanted = $(selectors.quantity);
                    if ($quantityWanted.length) {
                        purchaseAmount *= Number($quantityWanted.val());
                    }
                }

                let position =
                    1 == selectors.isCustom
                        ? selectors.position
                        : "#" + $widget.attr("id");

                initWidget(
                    settings.merchantId,
                    settings.apiMode,
                    position,
                    purchaseAmount,
                    settings.plans
                );
            });
        }, 150);

        if ($("#alma-widget-config").length) {
            var selectors = JSON.parse($("#alma-widget-config").val());
            if (window.prestashop != null && window.prestashop.on != null) {
                prestashop.on("updatedProduct", refreshWidgets);
            } else {
                let $body = $("body");
                $body.on("change", selectors.attrSelect, refreshWidgets);
                $body.on("click", selectors.attrRadio, refreshWidgets);
                $body.on("click", selectors.colorPick, refreshWidgets);
                $body.on("keyup", selectors.quantity, refreshWidgets);
                $body.on("change", selectors.quantity, refreshWidgets);
            }

            refreshWidgets(null, true);
        }

        window.__alma_refreshWidgets = refreshWidgets;
    });
})(jQuery);
