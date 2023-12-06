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

// Insurance

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
        //Insurance
        let choiseOk = false;

        onloadAddInsuranceInputOnProductAlma();
        if (typeof prestashop !== 'undefined') {
            prestashop.on(
                'updateProduct',
                function (event) {
                    let modalIsClosed = false;
                    if (event.event !== undefined) {
                        modalIsClosed = event.event.namespace === 'bs.modal' && event.event.type === 'hidden';
                    }
                    if (modalIsClosed) {
                        removeInsurance();
                    }

                    if(typeof event.selectedAlmaInsurance !== 'undefined' && event.selectedAlmaInsurance !== null) {
                        addInputsInsurance(event.selectedAlmaInsurance);
                    }
                }
            );
        }
        let addToCart = document.querySelector('.add-to-cart');
        addToCart.addEventListener("click", function (event) {
            console.log(choiseOk);
            if (!choiseOk) {
                event.preventDefault();
                event.stopPropagation();
                openModal('popupModal');
                console.log(openModal('popupModal'));
                console.log('add to cart');
                choiseOk = true;
            }
        });
    });
})(jQuery);

// ** Add input insurance in form to add to cart **
function onloadAddInsuranceInputOnProductAlma() {
    let currentResolve;
    let selectedAlmaInsurance = null;
    window.addEventListener('message', (e) => {
        if (e.data.type === 'getSelectedInsuranceData') {
            selectedAlmaInsurance = e.data.selectedInsuranceData;
            prestashop.emit('updateProduct', {selectedAlmaInsurance: selectedAlmaInsurance});
        } else if (currentResolve) {
            currentResolve(e.data);
        }
    });
}

function addInputsInsurance(selectedAlmaInsurance) {
    let formAddToCart = document.getElementById('add-to-cart-or-refresh');

    handleInput('alma_insurance_price', selectedAlmaInsurance.option.price, formAddToCart);
    handleInput('alma_insurance_name', selectedAlmaInsurance.name, formAddToCart);
    let addToCart = document.querySelector('.add-to-cart');
    addToCart.click();
}

function handleInput(inputName, value, form) {
    let elementInput = document.getElementById(inputName);
    if(elementInput == null) {
        let input = document.createElement('input');
        input.setAttribute('value', value);
        input.setAttribute('name', inputName);
        input.setAttribute('class', 'alma_insurance_input');
        input.setAttribute('id', inputName);
        input.setAttribute('type', 'hidden');

        form.prepend(input);
    }  else {
        elementInput.setAttribute('value', value);
    }
}

function removeInsurance() {
    const almaInsuranceConfigIframeElement = document.getElementById('product-alma-iframe')
    const almaInsuranceConfigIframe =
        almaInsuranceConfigIframeElement instanceof HTMLIFrameElement
            ? almaInsuranceConfigIframeElement.contentWindow
            : null;
    almaInsuranceConfigIframe?.postMessage({ type: 'dataSentBackToWidget', data: null }, '*');

    let inputsInsurance = document.getElementById('add-to-cart-or-refresh').querySelectorAll('.alma_insurance_input');
    inputsInsurance.forEach((input) => {
        input.remove();
    });
}