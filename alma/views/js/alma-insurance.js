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
        loadInsuranceMiniCart();
        prestashop.on(
            'updateCart',
            function(event) {
                loadInsuranceMiniCart()
            });
        prestashop.on(
            'updatedCart',
            function(event) {
                loadInsuranceMiniCart()
            });
    });
})(jQuery)

function loadInsuranceMiniCart() {
    if ($('.cartdrop-overview').length) {
        const insuranceId = $('#alma-insurance-global').data('insurance-id');
        $('.cartdrop-overview .productcard').each(function (i, e) {
            if ($(e).hasClass(`cart-item-${insuranceId}`)) {
                $(e).find('input').prop('disabled', true)
                // Remove change number of input when you wheel (scroll) inside the input
                e.querySelector('input').addEventListener('wheel', function (e) {
                    document.activeElement.blur();
                });
                $(e).find('.pscartdropdown-product-line-grid-right .input-group-btn-vertical').remove()
                $(e).find('a.label').removeAttr('href')
                // TODO : Need to edit the url of delete product for remove an insurance product
                $(e).find('.remove-from-cart').remove()
            }
        });
    }
}
