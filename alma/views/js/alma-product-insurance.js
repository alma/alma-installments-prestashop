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
        btnLoaders('start');
        onloadAddInsuranceInputOnProductAlma();
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
                    if (typeof event.selectedAlmaInsurance !== 'undefined' && event.selectedAlmaInsurance !== null) {
                        insuranceSelected = true;
                        addInputsInsurance(event);
                    }
                    if (typeof event.selectedInsuranceData !== 'undefined' && event.selectedInsuranceData) {
                        removeInputInsurance();
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
                    document.querySelector('.qty [name="qty"]').value = quantity;
                    productDetails = JSON.parse(document.getElementById('product-details').dataset.product);
                    refreshWidget();
                    addModalListenerToAddToCart();
                }
            );
        }
    });
})(jQuery);


function btnLoaders(action) {
    const addBtn = $(".add-to-cart");
    if (action === 'start') {
        $('<div id="insuranceSpinner" class="spinner"></div>').insertBefore($(".add-to-cart i"));
        addBtn.attr("disabled", "disabled");
    }
    if (action === 'stop') {
        $(".spinner").remove();
        addBtn.removeAttr("disabled");
        addModalListenerToAddToCart();
    }
}

// ** Add input insurance in form to add to cart **
function onloadAddInsuranceInputOnProductAlma() {
    let currentResolve;

    window.addEventListener('message', (e) => {
        if (e.data.type === 'almaEligibilityAnswer') {
            btnLoaders('stop');
            if (e.data.eligibilityCallResponseStatus.response.eligibleProduct === true) {
                let heightIframe = e.data.widgetSize.height;
                let stringHeightIframe = heightIframe + 'px';
                if (heightIframe <= 45) {
                    stringHeightIframe = '100%';
                }

                document.getElementById('alma-widget-insurance-product-page').style.height = stringHeightIframe;
            } else {
                let addToCart = document.querySelector('.add-to-cart');
                addToCart.removeEventListener("click",insuranceListener)
            }
        }
        if (e.data.type === 'getSelectedInsuranceData') {
            insuranceSelected = true;
            selectedAlmaInsurance = e.data.selectedInsuranceData;
            prestashop.emit('updateProduct', {
                selectedAlmaInsurance: selectedAlmaInsurance,
                selectedInsuranceData: e.data.declinedInsurance,
                selectedInsuranceQuantity: e.data.selectedInsuranceQuantity
            });
        } else if (currentResolve) {
            currentResolve(e.data);
        }
    });
}

function refreshWidget() {
    let cmsReference = createCmsReference(productDetails);
    let regularPriceToCents = Math.round(productDetails.price_without_reduction * 100);

    quantity = productDetails.quantity_wanted;
    if (productDetails.quantity_wanted <= 0) {
        quantity = 1;
    }

    getProductDataForApiCall(
        cmsReference,
        regularPriceToCents,
        productDetails.name,
        settings.merchant_id,
        quantity,
        settings.cart_id,
        settings.session_id
    );
}

function createCmsReference(productDetails) {
    if (productDetails.id_product !== null) {
        if (productDetails.id_product_attribute <= '0') {
            return productDetails.id_product;
        }

        return productDetails.id_product + '-' + productDetails.id_product_attribute;
    }

    return undefined;
}

function addInputsInsurance(event) {
    let formAddToCart = document.getElementById('add-to-cart-or-refresh');
    let selectedInsuranceQuantity = event.selectedInsuranceQuantity;

    if (selectedInsuranceQuantity > quantity) {
        selectedInsuranceQuantity = quantity
    }

    handleInput('alma_id_insurance_contract', event.selectedAlmaInsurance.insuranceContractId, formAddToCart);
    handleInput('alma_quantity_insurance', selectedInsuranceQuantity, formAddToCart);

}

function handleInput(inputName, value, form) {
    let elementInput = document.getElementById(inputName);
    if (elementInput == null) {
        let input = document.createElement('input');
        input.setAttribute('value', value);
        input.setAttribute('name', inputName);
        input.setAttribute('class', 'alma_insurance_input');
        input.setAttribute('id', inputName);
        input.setAttribute('type', 'hidden');

        form.prepend(input);
    } else {
        elementInput.setAttribute('value', value);
    }
}

function removeInsurance() {
    resetInsurance();
    insuranceSelected = false;
    removeInputInsurance();
}

function removeInputInsurance() {
    let inputsInsurance = document.getElementById('add-to-cart-or-refresh').querySelectorAll('.alma_insurance_input');
    inputsInsurance.forEach((input) => {
        input.remove();
    });
}

function addModalListenerToAddToCart() {
    if (settings.isAddToCartPopupActivated === true) {
        let addToCart = document.querySelector('.add-to-cart');
        addToCart.removeEventListener("click",insuranceListener)
        addToCart.addEventListener("click", insuranceListener);
    }
}
function insuranceListener(event) {
        if (!insuranceSelected) {
            event.preventDefault();
            event.stopPropagation();
            openModal('popupModal', quantity);
            insuranceSelected = true;
            addToCartFlow = true;
        }
        insuranceSelected = false;
}
