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
        // Insurance
        onloadInsuranceItemCartAlma();
        // Reload item cart for Prestashop 1.7+ when quantity change
        if (window.prestashop != null && window.prestashop.on != null) {
            prestashop.on("updatedCart", onloadInsuranceItemCartAlma);
        }
    });
})(jQuery);

function onloadInsuranceClickEvents() {
     $('.alma-remove-product').on( "click", function(e) {
         e.preventDefault();
         addLoaderDot(e);
         $.ajax({
             type: 'POST',
             url: $(this).attr("data-link"),
             dataType: 'json',
             data: {
                 ajax: true,
                 token: $(this).attr('data-token'),
                 product_id: $(this).attr("data-product-id"),
                 attribute_id: $(this).attr("data-product-attribute-id"),
                 customization_id: $(this).attr("data-product-customization-id"),
             },
         })
             .success(function() {
                 location.reload();
             })

             .error(function(e) {
                 location.reload();
             });
     });

     $('.alma-remove-association').on( "click", function(e) {
         e.preventDefault();
         addLoaderDot(e);
         $.ajax({
             type: 'POST',
             url: $(this).attr("data-link"),
             dataType: 'json',
             data: {
                 ajax: true,
                 token: $(this).attr('data-token'),
                 alma_insurance_product_id: $(this).attr("data-alma-association-id")
             },
         })
             .success(function() {
                 location.reload();
             })

             .error(function(e) {
                 location.reload();
             });
     });

     $('.alma-remove-insurance-product').on( "click", function(e) {
         e.preventDefault();
         addLoaderDot(e);
         $.ajax({
             type: 'POST',
             url: $(this).attr("data-link"),
             dataType: 'json',
             data: {
                 ajax: true,
                 token: $(this).attr('data-token'),
                 alma_insurance_product_id: $(this).attr("data-alma-association-id")
             },
         })
             .success(function() {
                 location.reload();
             })

             .error(function(e) {
                 location.reload();
             });
     });
 }

// ** Display extra info for insurance under the item product on cart **
function onloadInsuranceItemCartAlma() {
    let itemsCart = document.querySelectorAll('.cart-items .cart-item');

    itemsCart.forEach((item) => {
        let dataProduct = item.querySelector('.alma-data-product');
        let isAlmaInsuranceProduct = parseInt(dataProduct.dataset.isAlmaInsurance);
        let noInsuranceAssociated = parseInt(dataProduct.dataset.noInsuranceAssociated);
        let actionsInsuranceProduct = dataProduct.querySelector('.actions-alma-insurance-product');

        if (!isAlmaInsuranceProduct && noInsuranceAssociated) {
            if (actionsInsuranceProduct) {
                actionsInsuranceProduct.style.display = 'block';
                item.append(actionsInsuranceProduct);
            }
            let clearfix = document.createElement('div');
            clearfix.classList.add('clearfix');
            item.append(clearfix);

            let formQty = item.querySelector('.input-group');

            if (formQty) {
                formQty.querySelector('input').disabled = true
                // Remove change number of input when you wheel (scroll) inside the input
                formQty.querySelector('input').addEventListener('wheel', function (e) {
                    document.activeElement.blur();
                });
                let btnsQty = formQty.querySelectorAll('[class^=input-group-btn]');
                btnsQty.forEach((btnQty) => {
                    btnQty.remove();
                });
            }
        }
        if (isAlmaInsuranceProduct) {
            item.remove();
        }
    });

    onloadInsuranceClickEvents();
}

function addLoaderDot(e) {
    let actionAlmaInsuranceProduct = e.currentTarget.closest('.actions-alma-insurance-product');
    actionAlmaInsuranceProduct.classList.add('loading');
    actionAlmaInsuranceProduct.append(createLoaderDot());
}

function createLoaderDot() {
    let containerDot = document.createElement('span');
    containerDot.classList.add('alma-loader-dot-container');

    let arrayDots = [
        'one',
        'two',
        'three',
    ];
    arrayDots.forEach((key) => {
        let dot = document.createElement('span');
        dot.classList.add('dot', key)
        containerDot.appendChild(dot);
    });

    return containerDot;
}
