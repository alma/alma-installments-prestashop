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
        onloadAddInsuranceInputOnProductAlma();

        $(document).off('click', '#add_to_cart').on('click', '#add_to_cart', function(e){
            e.preventDefault();

            if(
                null != document.getElementById('alma_id_insurance_contract')
            ) {
                var almaUrl = document.getElementById('alma-widget-insurance-product-page').getAttribute('data-link16')
                var almaToken = document.getElementById('alma-widget-insurance-product-page').getAttribute('data-token')
                var productId = document.getElementById('product_page_product_id').value;
                var productAttributeId = document.getElementById('idCombination').value;
                var qty = document.getElementById('quantity_wanted').value;
                var insuranceContractId = document.getElementById('alma_id_insurance_contract').value;


                $.ajax({
                    type: 'POST',
                    url: almaUrl,
                    dataType: 'json',
                    data: {
                        ajax: true,
                        token : almaToken,
                        id_product : productId,
                        id_product_attribute : productAttributeId,
                        qty : qty,
                        alma_id_insurance_contract : insuranceContractId,
                    },
                })
                    .success(function () {
                        removeInsurance();
                    })

                    .error(function (e) {
                    });
            }

        });

        $(document).on('click', '#layer_cart .cross, #layer_cart .continue, .layer_cart_overlay', function(e) {
            location.reload();
        });
    });



})(jQuery);

function updateProduct(event) {
    let modalIsClosed = false;
    if (event.event !== undefined) {
        modalIsClosed = event.event.namespace === 'bs.modal' && event.event.type === 'hidden';
    }
    if (modalIsClosed) {
        removeInsurance();
    }

  if (
        typeof event.data.selectedInsuranceData !== 'undefined'
        && event.data.selectedInsuranceData !== null
    ) {
        addInputsInsurance(event.data.selectedInsuranceData);
    }
}

// Insurance
// ** Add input insurance in form to add to cart **
function onloadAddInsuranceInputOnProductAlma() {
    // To hide insurance widget on a quick view product
    $(document).off('click', '.quick-view').on('click', '.quick-view', function(e){
        e.preventDefault();

        $('#alma-widget-insurance-product-page').hide();
    });

    let currentResolve;
    let selectedAlmaInsurance = null;
    window.addEventListener('message', (e) => {
        if (e.data.type === 'getSelectedInsuranceData') {
            selectedAlmaInsurance = e.data.selectedInsuranceData;
            updateProduct(e);
        } else if (currentResolve) {
            currentResolve(e.data);
        }
    });
}

function addInputsInsurance(selectedAlmaInsurance) {
    var formAddToCart = document.getElementById('buy_block');

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
    const almaInsuranceConfigIframeElement = document.getElementById('alma-widget-insurance')
    const almaInsuranceConfigIframe =
        almaInsuranceConfigIframeElement instanceof HTMLIFrameElement
            ? almaInsuranceConfigIframeElement.contentWindow
            : null;
    almaInsuranceConfigIframe?.postMessage({ type: 'dataSentBackToWidget', data: null }, '*');
    document.getElementById('alma_id_insurance_contract').remove();
}
