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
(function ($) {
    $(function () {
        if (!document.getElementById('alma-widget-insurance-product-page')) {
            throw new Error('[Alma] Product details not found. You need to add the hook displayProductActions in your template product page.');
        }

        const settings = getSettingsInsurance();
        let insuranceSelected = false;
        let selectedAlmaInsurance = null;
        let addToCartFlow = false;
        let productDetails = JSON.parse(document.querySelector('.alma-widget-insurance .js-product-details').dataset.product);
        let quantity = getQuantity();
        let almaEligibilityAnswer = false;

        //Insurance
        $("body").on("hidden.bs.modal", "#blockcart-modal", function (e) {
            removeInsurance();
        });
        handleInsuranceProductPage();
        btnLoaders('start');
        onloadAddInsuranceInputOnProductAlma();
        if (typeof prestashop !== 'undefined') {
            prestashop.on('updateProduct', function (event) {
                let addToCart = getAddToCartButton();

                if (event.event !== undefined) {
                    quantity = getQuantity();
                }
                if (event.eventType === 'updatedProductQuantity') {
                    quantity = getQuantity();
                    if (event.event) {
                        quantity = event.event.target.value;
                    }
                    removeInsurance();
                }
                if (event.eventType === 'updatedProductCombination') {
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
            });
            prestashop.on('updatedProduct', function (data) {
                // TODO:
                //  - use data.product_details to load latest product details from the data-product attribute of the div
                //  - fallback to #product-details[data-product] if for any reason we cannot find what we need in `data`
                //  - have a last fallback to making our own AJAX call?

                document.querySelector('.qty [name="qty"]').value = quantity;

                productDetails = JSON.parse(document.querySelector('.alma-widget-insurance .js-product-details').dataset.product);
                productDetails.quantity_wanted = parseInt(quantity);

                document.querySelector('.alma-widget-insurance .js-product-details').dataset.product = JSON.stringify(productDetails);
                refreshWidget();
                addModalListenerToAddToCart();
            });
        }


        function getQuantity() {
            let quantity = 1;
            if (document.querySelector('.qty [name="qty"]')) {
                quantity = parseInt(document.querySelector('.qty [name="qty"]').value);
            }
            return quantity
        }

        function getSettingsInsurance() {
            if (document.querySelector('#alma-widget-insurance-product-page')) {
                return JSON.parse(document.querySelector('#alma-widget-insurance-product-page').dataset.almaInsuranceSettings);
            }

            return null;
        }

        function btnLoaders(action) {
            const $addBtn = $(".add-to-cart");
            if (action === 'start') {
                $('<div id="insuranceSpinner" class="spinner"></div>').insertBefore($(".add-to-cart i"));
                $addBtn.attr("disabled", "disabled");
            }
            if (action === 'stop') {
                $(".spinner").remove();
                $addBtn.removeAttr("disabled");
                addModalListenerToAddToCart();
            }
        }

        // ** Add input insurance in form to add to cart **
        function onloadAddInsuranceInputOnProductAlma() {
            let currentResolve;

            window.addEventListener('message', (e) => {
                let widgetInsurance = document.getElementById('alma-widget-insurance-product-page');
                if (e.data.type === 'almaEligibilityAnswer') {
                    almaEligibilityAnswer = e.data.eligibilityCallResponseStatus.response.eligibleProduct;
                    btnLoaders('stop');
                    if (almaEligibilityAnswer) {
                        prestashop.emit('updateProduct', {
                            reason: {
                                productUrl: window.location.href,
                            }
                        });
                    } else {
                        widgetInsurance.style.display = 'none';
                        let addToCart = document.querySelector('.add-to-cart');
                        if (addToCart) {
                            addToCart.removeEventListener("click", insuranceListener)
                        }
                    }
                }
                if (e.data.type === 'changeWidgetHeight') {
                    widgetInsurance.style.height = e.data.widgetHeight + 'px';
                }
                if (e.data.type === 'getSelectedInsuranceData') {
                    if (parseInt(document.querySelector('.qty [name="qty"]').value) !== quantity) {
                        quantity = getQuantity();
                    }
                    insuranceSelected = true;
                    selectedAlmaInsurance = e.data.selectedInsuranceData;
                    prestashop.emit('updateProduct', {
                        reason: {
                            productUrl: window.location.href
                        },
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
            let priceAmount = productDetails.price_amount;
            if (productDetails.price_amount === undefined) {
                priceAmount = productDetails.price;
            }
            let staticPriceToCents = Math.round(priceAmount * 100);

            quantity = productDetails.quantity_wanted;
            if (productDetails.quantity_wanted <= 0) {
                quantity = 1;
            }

            getProductDataForApiCall(cmsReference, staticPriceToCents, productDetails.name, settings.merchant_id, quantity, settings.cart_id, settings.session_id, insuranceSelected);
        }

        function createCmsReference(productDetails) {
            if (!productDetails.id_product) {
                return
            }

            // TODO: check why comparing to string value
            if (productDetails.id_product_attribute <= '0') {
                return productDetails.id_product;
            }

            return productDetails.id_product + '-' + productDetails.id_product_attribute;
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
            if (settings.isAddToCartPopupActivated === true && almaEligibilityAnswer) {
                const addToCart = getAddToCartButton();
                if (addToCart) {
                    // If we change the quantity the DOM is reloaded then we need to remove and add the listener again
                    addToCart.removeEventListener("click", insuranceListener);
                    addToCart.addEventListener("click", insuranceListener);
                }
            }
        }

        function getAddToCartButton() {
            let addToCart = document.querySelector('button.add-to-cart');
            // TODO: Ravate specific to generalise with selector configuration
            if (!addToCart) {
                addToCart = document.querySelector('.add-to-cart a, .add-to-cart button').first();
            }

            return addToCart;
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

        function handleInsuranceProductPage() {
            if (productDetails.id === $('#alma-insurance-global').data('insurance-id')) {
                //$('.product-prices').hide(); // To hide the price of the insurance product page
                let tagInformationInsurance = '<div class="alert alert-info" id="alma-alert-insurance-product">' + $('#alma-insurance-global').data('message-insurance-page') + '</div>';
                $(tagInformationInsurance).insertAfter('.product-variants');
            }
        }
    });
})(jQuery);