/**
 * 2018-2024 Alma SAS
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
 * @copyright 2018-2024 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */
let inPage = undefined;
let paymentButtonEvents = [];
let inPageSettings = {};

window.addEventListener("load", function() {
    if (!document.getElementById('alma-inpage-global')) {
        throw new Error('[Alma] In Page Settings is missing.');
    }
    inPageSettings = JSON.parse(document.querySelector('#alma-inpage-global').dataset.settings);
    onloadAlma();
    window.__alma_refreshInpage = onloadAlma;
});

function removeLoaderButtonPayment(button) {
    button.classList.remove('disabled', 'loading');
    let dots = button.querySelector('.alma-loader-dot-container');
    if (dots) {
        dots.remove();
    }
}

function onloadAlma() {
    let radioButtons = document.querySelectorAll('input[name="payment-option"]');

    //Prestashop 1.7+
    radioButtons.forEach(function (input) {
        input.addEventListener("change", () => handleSelectedPaymentOption(input));

        if (input.checked) {
            handleSelectedPaymentOption(input);
        }
    });

    //Prestashop 1.6-
    let paymentButtonsPs16 = document.querySelectorAll(".alma-inpage.ps16");
    paymentButtonsPs16.forEach(function (button) {
        removeLoaderButtonPayment(button);
        button.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            let paymentOptionId = this.getAttribute('id');
            let url = this.getAttribute('href');
            let settingInpage = document.querySelector('#alma-inpage-' + paymentOptionId);

            if( inPage !== undefined) {
                inPage.unmount();
            }

            createAlmaIframe(settingInpage, true, url);
        });
    });
}

/**
 * Handle the change of the payment option for Prestashop 1.7+
 * @param input
 */
function handleSelectedPaymentOption(input) {
    let paymentOptionId = input.getAttribute('id');
    let blockForm = document.querySelector('#pay-with-' + paymentOptionId + '-form');

    removeAlmaEventsFromPaymentButton();
    if (inPage !== undefined) {
        inPage.unmount();
    }
    if ($(input).is(inPageSettings.paymentButtonSelector)) {
        let formInpage = blockForm.querySelector('.alma-inpage');
        if (input.checked && formInpage) {
            let installment = formInpage.dataset.installment;
            let deferredDays = formInpage.dataset.deferreddays;
            let deferredMonths = formInpage.dataset.deferredmonths;

            if (
                installment === '1'
                && deferredDays === '0'
                && deferredMonths === '0'
            ) {
                blockForm.hidden = true;
            }
            let url = formInpage.dataset.action;

            inPage = createAlmaIframe(formInpage);
            mapPaymentButtonToAlmaPaymentCreation(url, inPage, input);
        }
    }
}

function createAlmaIframe(form, showPayButton = false, url = '') {
    let merchantId = form.dataset.merchantid;
    let installment = form.dataset.installment;
    let deferredDays = form.dataset.deferreddays;
    let deferredMonths = form.dataset.deferredmonths;
    let purchaseAmount = form.dataset.purchaseamount;
    let locale = form.dataset.locale;

    let selectorIframeInPage = form.querySelector('.alma-inpage-iframe');
    if (showPayButton) {
        inPage = Alma.InPage.initialize(
            {
                merchantId: merchantId,
                amountInCents: purchaseAmount,
                installmentsCount: installment,
                deferredDays: deferredDays,
                deferredMonths: deferredMonths,
                locale: locale,
                environment: form.dataset.apimode,
                selector: selectorIframeInPage.getAttribute('id'),
                onIntegratedPayButtonClicked : async () => {
                    await createPayment(url, inPage);
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
            deferredDays: deferredDays,
            deferredMonths: deferredMonths,
            locale: locale,
            environment: form.dataset.apimode,
            selector: selectorIframeInPage.getAttribute('id'),
        }
    );
}

function mapPaymentButtonToAlmaPaymentCreation(url, inPage, input) {
    let paymentButton = document.querySelector(inPageSettings.placeOrderButtonSelector);

    const eventAlma = async function (e) {
        e.preventDefault();
        e.stopPropagation();
        await createPayment(url, inPage, input);
    };

    paymentButtonEvents.push(eventAlma);
    paymentButton.addEventListener('click', eventAlma);
}

async function createPayment(url, inPage, input = null) {
    if (isAlmaPayment(url)) {
        displayLoader();
        try {
            let response = await fetch(url);
            let paymentData = await response.json();

            inPage.startPayment(
                {
                    paymentId: paymentData.id,
                    onUserCloseModal: () => {
                        uncheckTermsAndConditions();

                        document.querySelector('.alma-loader--wrapper').remove();
                        if (input) {
                            input.checked = false
                            inPage.unmount();
                        }
                        removeAlmaEventsFromPaymentButton();
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

function removeAlmaEventsFromPaymentButton() {
    let event = paymentButtonEvents.shift();
    while (event) {
        document.querySelector(inPageSettings.placeOrderButtonSelector).removeEventListener('click', event);
        event = paymentButtonEvents.shift();
    }
}

function displayLoader() {
    let loader = document.createElement('div');
    loader.classList.add('loadingIndicator');
    loader.innerHTML = '<img src="https://cdn.almapay.com/img/animated-logo-a.svg" alt="Loading" />';
    let wrapperLoader = document.createElement('div');
    wrapperLoader.classList.add('alma-loader--wrapper');
    wrapperLoader.appendChild(loader);
    document.body.appendChild(wrapperLoader);
}

function isAlmaPayment(url) {
    return url.indexOf("module/alma/payment") !== -1 || url.indexOf("module=alma") !== -1;
}

function uncheckTermsAndConditions() {
    let listOfTermsOfService = document.querySelectorAll('#conditions-to-approve > ul > li');

    listOfTermsOfService.forEach((input) => {
        let checkboxTermsOfService = input.querySelector('.ps-shown-by-js[type=checkbox]');
        if (checkboxTermsOfService !== null) {
            checkboxTermsOfService.checked = false;
        }
    });
}
