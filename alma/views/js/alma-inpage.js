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

let inPage = undefined;
var $otherJqueryVersion = jQuery.noConflict() // stock in $otherJqueryVersion the oldest jquery version

if(undefined === $) // case of there is only one js version
{
    var $ = $otherJqueryVersion;
    $otherJqueryVersion = undefined; // we destroy the second version
}

window.onload = function () {
    const checkoutEvents = [];

    const removeCheckoutEvents = function () {
        let event = checkoutEvents.shift();
        while (event) {
            $('#payment-confirmation button').off('click', event);
            event = checkoutEvents.shift();
        }
    }

    const processAlmaPayment = function (paymentOptionId, inPage) {
        let form = $('#pay-with-' + paymentOptionId + '-form form');
        let url = form.attr("action");

        const eventAlma = function (e) {
            e.preventDefault();
            e.stopPropagation();
            ajaxPayment(url, inPage);
        };

        checkoutEvents.push(eventAlma);
        $('#payment-confirmation button').on('click', eventAlma);
    };

    const addLoader = function () {
        let loading = "<div class='loadingIndicator'><img src='https://cdn.almapay.com/img/animated-logo-a.svg' alt='Loading' /></div>";
        $( "body" ).append( "<div class='alma-loader--wrapper'>" + loading + "</div>" );
    };

    const ajaxPayment = function (url, inPage) {
        if (isAlmaPayment(url)) {
            addLoader();
            $.ajax({
                type: "POST",
                url: url,
                dataType: "json",
                data: {
                    ajax: true,
                    action: "payment",
                },
            })
                .done(function (paymentData) {
                    inPage.startPayment(
                        {
                            paymentId: paymentData.id,
                            onUserCloseModal: () => {
                                let selectorCheckboxPs17 = $('.ps-shown-by-js[type=checkbox]');
                                if (selectorCheckboxPs17.length > 0) {
                                    selectorCheckboxPs17.prop('checked', false);
                                }
                                $('.alma-loader--wrapper').remove();
                            }
                        }
                    );
                })
                .fail(function () {
                    let pathnameUrl = window.location.pathname;
                    let urlError = "/order";

                    if (pathnameUrl === '/index.php') {
                        urlError = "/index.php?controller=order";
                    }

                    window.location.href = urlError;
                });
        }
    }

    const isAlmaPayment = function (url) {
        return url.indexOf("module/alma/payment") !== -1 || url.indexOf("module=alma") !== -1;
    };

    const createIframe = function (paymentOptionId, selectorSetting, showPayButton, url = '') {
        let merchantId = selectorSetting.data('merchantid');
        let installment = selectorSetting.data('installment');
        let purchaseAmount = selectorSetting.data('purchaseamount');
        let locale = selectorSetting.data('locale');

        selectorSetting.attr('id', 'alma-inpage-' + paymentOptionId);

        let selectorIframeInPage = '#alma-inpage-' + paymentOptionId;

        if (showPayButton) {
            // No refactor inPage is use in callback function 1.6
            inPage = Alma.InPage.initialize(
                {
                    merchantId: merchantId,
                    amountInCents: purchaseAmount,
                    installmentsCount: installment,
                    locale: locale,
                    environment: selectorSetting.data("apimode"),
                    selector: selectorIframeInPage,
                    onIntegratedPayButtonClicked : () => {
                        ajaxPayment(url, inPage);
                    }
                }
            );

            return inPage;
        }

        return Alma.InPage.initialize(
            {
                merchantId: merchantId,
                amountInCents: purchaseAmount,
                installmentsCount: installment,
                locale: locale,
                environment: selectorSetting.data("apimode"),
                selector: selectorIframeInPage,
            }
        );
    }

    const almaInPageOnload = function () {
        if ($(".alma-inpage").length === 0) {
            $('<div class="alert alert-danger">Error : .alma-inpage class not found in DOM</div>').insertAfter('.payment-options');
            $('<div class="alert alert-danger">Error : .alma-inpage class not found in DOM</div>').insertBefore('#HOOK_PAYMENT');
            console.log('No alma-inpage class found in DOM');
            return
        }

        //Prestashop 1.7+
        $("input.ps-shown-by-js[name=payment-option]").click(function () {
            removeCheckoutEvents();
            if (inPage !== undefined) {
                inPage.unmount();
            }
            if ($(this).data('module-name') === 'alma') {
                let paymentOptionId = $(this).attr('id');
                let selectorSetting = $('#' + paymentOptionId + '-additional-information .alma-inpage');
                let showPayButton = false;
                let installmentButton = selectorSetting.data('installment');
                if (installmentButton === 1) {
                    selectorSetting.hide();
                }
                if (selectorSetting.length > 0) {
                    inPage = createIframe(paymentOptionId, selectorSetting, showPayButton);
                    processAlmaPayment(paymentOptionId, inPage);
                }
            }
        });

        //Prestashop 1.6-
        $(".alma-inpage.ps16").click(function (e) {
            e.preventDefault();
            let paymentOptionId = $(this).attr('id');
            let selectorSetting = $('#alma-inpage-' + paymentOptionId);
            let showPayButton = true;
            let url = $('#' + paymentOptionId).attr('href');

            if( inPage !== undefined) {
                inPage.unmount();
            }

            createIframe(paymentOptionId, selectorSetting, showPayButton, url);
        });
    }

    almaInPageOnload();
};

