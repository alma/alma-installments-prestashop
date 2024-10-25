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

        let loaded = false;
        let insuranceSelected = false;
        let selectedAlmaInsurance = null;
        let addToCartFlow = false;
        let quantity = getQuantity();
        let almaEligibilityAnswer = false;

        // Reset the insurance widget & input when customer chooses "continue shopping" from the "added to cart" modal
        $("body").on("hidden.bs.modal", "#blockcart-modal", removeInsurance);

        // Display warning to customer if they're seeing the insurance "product" page
        handleInsuranceProductPage();

        // Add spinner to add to cart button & disable it until insurance is loaded
        showLoadingSpinner();

        // Listening to messages from our widget
        window.addEventListener('message', handleWidgetMessage);

        if (prestashop) {
            prestashop.on('updateProduct', function (args) {
                // TODO: is this really useful?
                if (args.event !== undefined) {
                    quantity = getQuantity();
                }

                // Update quantity & reset insurance choices when quantity changes
                if (args.eventType === 'updatedProductQuantity') {
                    if (args.event) {
                        quantity = Number(args.event.target.value);
                    } else {
                        quantity = getQuantity();
                    }
                    removeInsurance();
                }

                // Reset insurance choice when product changes
                if (args.eventType === 'updatedProductCombination') {
                    removeInsurance();
                }
            });

            prestashop.on('updatedProduct', function (data) {
                // Update product details data from the PrestaShop-sent data
                if (data.product_details) {
                    const shadowDiv = document.createElement('div');
                    $(shadowDiv).html(data.product_details);

                    const psProductDetails = shadowDiv.querySelector('[data-product]');
                    if (!psProductDetails || !psProductDetails.dataset.product) {
                        AlmaInsurance.productDetails = null
                    } else {
                        AlmaInsurance.productDetails = JSON.parse(psProductDetails.dataset.product);
                    }
                }

                // If we did not get the data from PS's AJAX request, get it from the product-details panel
                const psProductDetails = document.querySelector('[data-product]');
                if (!AlmaInsurance.productDetails && psProductDetails && psProductDetails.dataset.product) {
                    AlmaInsurance.productDetails = JSON.parse(psProductDetails.dataset.product);
                }

                if (!AlmaInsurance.productDetails) {
                    // TODO: fallback on making an AJAX call ourselves?
                    console.error("Could not find product details");
                    return;
                }

                // There's a bug in some PS 1.7 versions where quantity_wanted is not set to the actual quantity value,
                // while in later versions it's correctly set but the quantity input value is reset to 1
                AlmaInsurance.productDetails.quantity_wanted = quantity
                setQuantity(AlmaInsurance.productDetails.quantity_wanted)

                refreshWidget();
                addModalListenerToAddToCart();
            });
        }


        // Retrieve wanted product quantity from the quantity selector
        function getQuantity() {
            let quantity = 1;

            const qtyInput = document.querySelector('.qty [name="qty"]');
            if (qtyInput) {
                quantity = Number(qtyInput.value);
            }

            if (quantity <= 0) {
                quantity = 1;
            }

            return quantity
        }

        function setQuantity(quantity) {
            if (quantity <= 0) {
                quantity = 1;
            }

            const qtyInput = document.querySelector('.qty [name="qty"]');
            qtyInput.value = quantity;
        }

        // Display/hide a spinner on the add to cart button
        function showLoadingSpinner(show = true) {
            const $addBtn = $(getAddToCartButton());

            if (show) {
                loaded = false;
                $addBtn.prepend($('<div id="insuranceSpinner" class="spinner"></div>'));
                $addBtn.attr("disabled", "disabled");
            } else {
                $("#insuranceSpinner").remove();
                $addBtn.attr("disabled", null);
            }
        }

        // Handle incoming messages from the widget
        function handleWidgetMessage(message) {
            let widgetInsurance = document.getElementById('alma-widget-insurance-product-page');

            switch (message.data.type) {
                // Widget is sending us the result of the eligibility call for the current product
                case 'almaEligibilityAnswer':
                    almaEligibilityAnswer = message.data.eligibilityCallResponseStatus.response.eligibleProduct;

                    // TODO: we should receive a "loaded" message from the widget
                    if (!loaded) {
                        loaded = true;
                        showLoadingSpinner(false);
                        addModalListenerToAddToCart();
                    }

                    if (!almaEligibilityAnswer) {
                        widgetInsurance.style.display = 'none';
                        let addToCart = document.querySelector('.add-to-cart');
                        if (addToCart) {
                            addToCart.removeEventListener("click", addToCartListener)
                        }
                    }
                    break;

                // Widget is asking us to adjust the iframe's height
                case 'changeWidgetHeight':
                    widgetInsurance.style.height = message.data.widgetHeight + 'px';
                    break;

                // Widget is sending us selected insurance data
                case 'getSelectedInsuranceData':
                    if (parseInt(document.querySelector('.qty [name="qty"]').value) !== quantity) {
                        quantity = getQuantity();
                    }

                    const data = {
                        selectedAlmaInsurance: message.data.selectedInsuranceData,
                        hasRemovedInsurance: message.data.declinedInsurance,
                        selectedInsuranceQuantity: message.data.selectedInsuranceQuantity
                    }

                    if (Boolean(data.selectedAlmaInsurance)) {
                        // An insurance offer has been chosen
                        insuranceSelected = true;
                        addInsuranceInputs(data);
                    } else if (data.hasRemovedInsurance) {
                        // Insurance choice has been withdrawn
                        insuranceSelected = false;
                        removeInsuranceInputs();
                    }

                    // If we had intercepted the add to cart flow, resume it to effectively add the product to the cart
                    if (addToCartFlow) {
                        getAddToCartButton().click();
                    }
                    break;
            }
        }

        // Ask widget to refresh with updated product data
        function refreshWidget() {
            let cmsReference = createCmsReference(AlmaInsurance.productDetails);
            let priceAmount = AlmaInsurance.productDetails.price_amount;
            if (priceAmount === undefined) {
                priceAmount = AlmaInsurance.productDetails.price;
            }
            let staticPriceToCents = Math.round(priceAmount * 100);

            // !! Global function provided by openInPageModal script
            getProductDataForApiCall(
                cmsReference,
                staticPriceToCents,
                AlmaInsurance.productDetails.name,
                AlmaInsurance.settings.merchant_id,
                quantity,
                AlmaInsurance.settings.cart_id,
                AlmaInsurance.settings.session_id,
                insuranceSelected
            );
        }

        // Concatenate product ID & its combination ID for a unique identifier
        function createCmsReference(productDetails) {
            if (!productDetails.id_product) {
                return
            }

            if (productDetails.id_product_attribute <= 0) {
                return productDetails.id_product;
            }

            return productDetails.id_product + '-' + productDetails.id_product_attribute;
        }

        // Add hidden inputs to the add to cart form so that our insurance product is added along with the main product
        function addInsuranceInputs(event) {
            let formAddToCart = document.getElementById('add-to-cart-or-refresh');
            let selectedInsuranceQuantity = event.selectedInsuranceQuantity;

            if (selectedInsuranceQuantity > quantity) {
                selectedInsuranceQuantity = quantity
            }

            // We need both the selected insurance contract ID, and how many subscriptions to add
            addInsuranceInput('alma_id_insurance_contract', event.selectedAlmaInsurance.insuranceContractId, formAddToCart);
            addInsuranceInput('alma_quantity_insurance', selectedInsuranceQuantity, formAddToCart);
        }

        // Add a single insurance input to the add to cart form
        function addInsuranceInput(inputName, value, form) {
            let $elementInput = $(`#${inputName}`)
            if (!$elementInput.length) {
                const $input = $(`<input type="hidden" id="${inputName}" name="${inputName}" class="alma_insurance_input" value="${value}">`)
                $(form).prepend($input);
            } else {
                $elementInput.val(value);
            }
        }

        // Remove our insurance hidden fields from the add to cart form
        function removeInsuranceInputs() {
            $('.alma_insurance_input').remove()
        }

        function removeInsurance() {
            // !! Global function provided by openInPageModal script
            resetInsurance();
            insuranceSelected = false;
            removeInsuranceInputs();
        }

        function addModalListenerToAddToCart() {
            if (AlmaInsurance.settings.isAddToCartPopupActivated === true && almaEligibilityAnswer) {
                const addToCart = getAddToCartButton();
                if (addToCart) {
                    // If we change the quantity the DOM is reloaded then we need to remove and add the listener again
                    addToCart.removeEventListener("click", addToCartListener);
                    addToCart.addEventListener("click", addToCartListener);
                }
            }
        }

        // Find the selector for the add to cart button
        function getAddToCartBtnSelector() {
            // TODO: move selectors to a module configuration option
            const selectors = [
                '[data-button-action=add-to-cart]:visible', // generic PrestaShop add to cart button
                'button.add-to-cart:visible',
                '.add-to-cart a:visible',
                '.add-to-cart button:visible',
                'a[role=button][href$=addToCart]:visible' // elementor addToCart widget
            ];

            // Return first selector that successfully matches an element in the DOM
            return selectors.find(selector => $(selector).length > 0)
        }

        function getAddToCartButton() {
            return $(getAddToCartBtnSelector())[0];
        }

        function addToCartListener(event) {
            if (!insuranceSelected && !addToCartFlow) {
                event.preventDefault();
                event.stopPropagation();

                addToCartFlow = true;
                openModal('popupModal', quantity);
            } else {
                addToCartFlow = false;
            }
        }

        // This is only used to display a callout message when the product page for the actual insurance "product" from
        // the catalog is being viewed by a customer
        // TODO: This should probably be removed in favor of conditional templating/hooks on the product page itself
        function handleInsuranceProductPage() {
            const $almaInsuranceGlobal = $('#alma-insurance-global');
            if (AlmaInsurance.productDetails.id === $almaInsuranceGlobal.data('insurance-id')) {
                //$('.product-prices').hide(); // To hide the price of the insurance product page
                let tagInformationInsurance = '<div class="alert alert-info" id="alma-alert-insurance-product">' + $almaInsuranceGlobal.data('message-insurance-page') + '</div>';
                $(tagInformationInsurance).insertAfter('.product-variants');
            }
        }
    });
})(jQuery);