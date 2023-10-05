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
let checkoutEvents = [];

$(function() {
    onloadAlma();
});

function onloadAlma() {
    let radioButtons = document.querySelectorAll('input[name="payment-option"][data-module-name=alma]');
    let paymentButton = document.querySelector('#payment-confirmation button');

    //Prestashop 1.7+
    radioButtons.forEach(function (input) {
        input.addEventListener("change", function () {
            let paymentOptionId = input.getAttribute('id');
            let blockForm = document.querySelector('#pay-with-' + paymentOptionId + '-form');
            let formInpage = blockForm.querySelector('.alma-inpage');
            removeCheckoutEvents(paymentButton);
            if (inPage !== undefined) {
                inPage.unmount();
            }
            let installment = formInpage.dataset.installment;
            if (installment === '1') {
                blockForm.hidden = true;
            }
            if (this.dataset.moduleName === 'alma' && this.checked && formInpage) {
                let url = formInpage.dataset.action;

                inPage = createIframe(formInpage);

                const eventAlma = function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    ajaxPayment(url, inPage);
                };

                checkoutEvents.push(eventAlma);
                paymentButton.addEventListener('click', eventAlma);
            }
        });
    });

    //Prestashop 1.6-
    let paymentButtonsPs16 = document.querySelectorAll(".alma-inpage.ps16");
    paymentButtonsPs16.forEach(function (button) {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            let paymentOptionId = this.getAttribute('id');
            let url = this.getAttribute('href');
            let settingInpage = document.querySelector('#alma-inpage-' + paymentOptionId);

            if( inPage !== undefined) {
                inPage.unmount();
            }

            createIframe(settingInpage, true, url);
        });
    });
}

function createIframe(formSetting, showPayButton = false, url = '') {
    let merchantId = formSetting.dataset.merchantid;
    let installment = formSetting.dataset.installment;
    let purchaseAmount = formSetting.dataset.purchaseamount;
    let locale = formSetting.dataset.locale;

    let selectorIframeInPage = formSetting.querySelector('.alma-inpage-iframe');

    if (showPayButton) {
        // No refactor inPage is use in callback function 1.6
        inPage = Alma.InPage.initialize(
            {
                merchantId: merchantId,
                amountInCents: purchaseAmount,
                installmentsCount: installment,
                locale: locale,
                environment: formSetting.dataset.apimode,
                selector: selectorIframeInPage.getAttribute('id'),
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
            environment: formSetting.dataset.apimode,
            selector: selectorIframeInPage.getAttribute('id'),
        }
    );
}

async function ajaxPayment(url, inPage) {
    if (isAlmaPayment(url)) {
        addLoader();
        try {
            let ajaxInPageResponse = await fetch(url);
            let paymentData = await ajaxInPageResponse.json();

            inPage.startPayment(
                {
                    paymentId: paymentData.id,
                    onUserCloseModal: () => {
                        let checkboxTermsOfService = document.querySelector('.ps-shown-by-js[type=checkbox]');
                        if (checkboxTermsOfService !== null) {
                            checkboxTermsOfService.checked = false;
                        }
                        document.querySelector('.alma-loader--wrapper').remove();
                        onloadAlma();
                    }
                }
            );
        } catch(e) {
            console.log(e);
            let pathnameUrl = window.location.pathname;
            let urlError = "/order";

            if (pathnameUrl === '/index.php') {
                urlError = "/index.php?controller=order";
            }

            window.location.href = urlError;
        }
    }
}

function removeCheckoutEvents(paymentButton) {
    let event = checkoutEvents.shift();
    while (event) {
        paymentButton.removeEventListener('click', event);
        event = checkoutEvents.shift();
    }
}

function addLoader() {
    let loading = "<div class='loadingIndicator'><img src='https://cdn.almapay.com/img/animated-logo-a.svg' alt='Loading' /></div>";
    document.body.innerHTML += "<div class='alma-loader--wrapper'>" + loading + "</div>";
}

function isAlmaPayment(url) {
    return url.indexOf("module/alma/payment") !== -1 || url.indexOf("module=alma") !== -1;
}