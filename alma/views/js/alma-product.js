/**
 * 2018-2023 Alma SAS
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
 * @copyright 2018-2023 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */
(function ($) {
    $(function () {
        function initWidget(
            merchantId,
            apiMode,
            containerId,
            purchaseAmount,
            locale,
            showIfNotEligible,
            plans
        ) {
            callApiMode = Alma.ApiMode.LIVE;
            if (apiMode === 'test') {
                callApiMode = Alma.ApiMode.TEST;
            }
            var widgets = Alma.Widgets.initialize(merchantId, callApiMode);

            widgets.add(Alma.Widgets.PaymentPlans, {
                container: containerId,
                purchaseAmount: purchaseAmount,
                locale: locale,
                hideIfNotEligible: !showIfNotEligible,
                plans: plans,
            });
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
                    settings.locale,
                    settings.showIfNotEligible,
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

        //Insurance Script
        let currentResolve;
        let selectedAlmaInsurance = null;

        window.addEventListener('message', (e) => {
            if (e.data.type === 'buttonClicked') {
                selectedAlmaInsurance = e.data.buttonText;
                addInputsInsurance(selectedAlmaInsurance);
            } else if (currentResolve) {
                currentResolve(e.data);
            }
        });

        function addInputsInsurance(selectedAlmaInsurance) {
            let formAddToCart = document.getElementById('add-to-cart-or-refresh');

            handleInput('alma_insurance_price', selectedAlmaInsurance.option.price, formAddToCart);
            handleInput('alma_insurance_name', selectedAlmaInsurance.name, formAddToCart);
        }

        function handleInput(inputName, value, form) {
            let elementInput = document.getElementById(inputName);
            if(elementInput == null) {
                let input = document.createElement('input');
                input.setAttribute('value', value);
                input.setAttribute('name', inputName);
                input.setAttribute('id', inputName);
                input.setAttribute('type', 'hidden');

                form.prepend(input);
            }  else {
                elementInput.setAttribute('value', value);
            }
        }

        // For Insurance, we need to create a customization of the product but this customization should be filled automatically
        // And should not be displayed for the customer to see
        function hideInsuranceCustomization() {
            // First, we find the customization section in the DOM (note: based on className, it's not really stable)
            const customizationSection = document.getElementsByClassName("product-customization")[0]
            let customizationItems = undefined
            if (customizationSection) {
                // If the customization section exists, we look for the list elements inside
                customizationItems = customizationSection.getElementsByTagName('li')
            }
            // If there is only 1 list element, and if this element includes our insurance_by_alma customization
            if (customizationItems && customizationItems.length === 1 && customizationItems[0].innerText.includes('insurance_by_alma')) {
                // Then, we can hide the whole customization section
                customizationSection.style.display = "none"
            }
            // If there are more than 1 list element as customization, it means that the items has others customization that should be displayed
            if (customizationItems && customizationItems.length > 1) {
                // So we need to target and hide only our insurance_by_alma customization
                 for (let i= 0; i < customizationItems.length; i++) {
                     // If one of the customization listed includes or insurance
                     if (customizationItems[i].innerText.includes('insurance_by_alma')) {
                         // Then we remove only the customization listed and keep the other ones
                         customizationItems[i].style.display = 'none'
                     }
                 }

            }
        }
        hideInsuranceCustomization()
    });
})(jQuery);
