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

        let itemsCart = document.querySelectorAll('.ajax_cart_block_remove_link');

        itemsCart.forEach((item) => {
            item.remove();
        });

        let itemsCartNames = document.querySelectorAll('.cart_block_product_name');

        itemsCartNames.forEach((item) => {
            item.href = '#';
        });


        buttonOrder = document.querySelector('.products');
        if(buttonOrder) {
            let message = document.createElement('div');
            message.style = "margin:10px";
            let p = document.createElement('p');
            p.innerHTML = document.getElementById('alma-mini-cart-insurance-message').value;
            buttonOrder.append(message);
            message.append(p);
        }

    });

})(jQuery);