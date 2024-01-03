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
const settings = JSON.parse(document.querySelector('#alma-widget-insurance-product-page').dataset.almaInsuranceSettings);
let insuranceSelected = false;
let selectedAlmaInsurance = null;
let addToCartFlow = false;
let productDetails = null;
let quantity = 1;

(function ($) {
    $(function () {
        //Insurance
        onloadAddInsuranceInputOnProductAlma();
        openModalOnAddToCart();
        if (typeof prestashop !== 'undefined') {
            prestashop.on(
                'updateProduct',
                function (event) {
                    let addToCart = document.querySelector('.add-to-cart');
                    let modalIsClosed = false;

                    if (event.event !== undefined) {
                        modalIsClosed = event.event.namespace === 'bs.modal' && event.event.type === 'hidden';
                        quantity = 1;
                    }
                    if (event.eventType === 'updatedProductQuantity') {
                        quantity = event.event.target.value;
                        removeInsurance();
                    }
                    if (modalIsClosed || event.eventType === 'updatedProductCombination') {
                        removeInsurance();
                    }
                    if(typeof event.selectedAlmaInsurance !== 'undefined' && event.selectedAlmaInsurance !== null ) {
                        insuranceSelected = true;
                        addInputsInsurance(event.selectedAlmaInsurance);
                    }
                    if (addToCartFlow) {
                        addToCart.click();
                        insuranceSelected = false;
                        addToCartFlow = false;
                    }
                }
            );
            prestashop.on(
                'updatedProduct',
                function () {
                    document.getElementById('quantity_wanted').value = quantity;
                    productDetails = JSON.parse(document.getElementById('product-details').dataset.product);

                    refreshWidget();
                    openModalOnAddToCart();
                }
            );
        }
    });
})(jQuery);

// ** Add input insurance in form to add to cart **
function onloadAddInsuranceInputOnProductAlma() {
    let currentResolve;

    window.addEventListener('message', (e) => {
        if (e.data.type === 'almaEligibilityAnswer') {
            let heightIframe = e.data.widgetSize.height + 25;
            document.getElementById('product-alma-iframe').style.height = heightIframe + "px";
        }
        if (e.data.type === 'getSelectedInsuranceData') {
            insuranceSelected = true;
            selectedAlmaInsurance = e.data.selectedInsuranceData;
            prestashop.emit('updateProduct', {selectedAlmaInsurance: selectedAlmaInsurance});
        } else if (currentResolve) {
            currentResolve(e.data);
        }
    });
}

function refreshWidget() {
    let cmsReference = productDetails.id_product + '-' + productDetails.id_product_attribute;
    let regularPriceToCents = productDetails.price_without_reduction * 100;

    getproductDataForApiCall(
        cmsReference,
        regularPriceToCents,
        settings.merchant_id,
        productDetails.quantity_wanted
    );
}

function addInputsInsurance(selectedAlmaInsurance) {
    let formAddToCart = document.getElementById('add-to-cart-or-refresh');

    handleInput('alma_id_insurance_contract', selectedAlmaInsurance.insuranceContractId, formAddToCart);
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
    resetInsurance();
    insuranceSelected = false;
    let inputsInsurance = document.getElementById('add-to-cart-or-refresh').querySelectorAll('.alma_insurance_input');
    inputsInsurance.forEach((input) => {
        input.remove();
    });
}

function openModalOnAddToCart() {
    if (settings.is_add_to_cart_popup_insurance_activated === 'true') {
        let addToCart = document.querySelector('.add-to-cart');
        addToCart.addEventListener("click", function (event) {
            if (!insuranceSelected) {
                event.preventDefault();
                event.stopPropagation();
                openModal('popupModal');
                insuranceSelected = true;
                addToCartFlow = true;
            }
            insuranceSelected = false;
        });
    }
}