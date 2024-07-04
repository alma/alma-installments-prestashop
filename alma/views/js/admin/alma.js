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
        if ($('.alma.share-of-checkout').length > 0) {
            $('.btn-share-of-checkout').on('click', function(event) {
                event.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'ajax-tab.php',
                    dataType: 'json',
                    data: {
                        ajax: true,
                        controller: 'AdminAlmaShareOfCheckout',
                        action: 'ConsentShareOfCheckout',
                        token: $(this).attr('data-token'),
                        consent: $(this).attr('data-consent')
                    },
                })
                .success(function() {
                    $('.alma.share-of-checkout').hide();
                })

                .error(function(e) {
                    if (e.status != 200) {
                        $('.alma.share-of-checkout').after('<div class="alert alert-danger">' + e.statusText + '</div>');
                    }
                });
            });
        }
        if ($('.soc_hidden').length > 0) {
            $('.soc_hidden').parents('.panel').hide();
        }
        if ($('#alma_config_form').length > 0) {
            initMoreOption('#fieldset_1', '.form-group:not(:nth-child(1)):not(:nth-child(2))', '#ALMA_SHOW_PRODUCT_ELIGIBILITY_ON');
            initMoreOption('#fieldset_2', '.form-group:not(:nth-child(1)):not(:nth-child(2))', '#ALMA_SHOW_ELIGIBILITY_MESSAGE_ON');

            $('#ALMA_SHOW_PRODUCT_ELIGIBILITY_ON').on('click', function() {
                initMoreOption('#fieldset_1', '.form-group:not(:nth-child(1)):not(:nth-child(2))', '#' + $(this)[0].id);
            });
            $('#ALMA_SHOW_ELIGIBILITY_MESSAGE_ON').on('click', function() {
                initMoreOption('#fieldset_2', '.form-group:not(:nth-child(1)):not(:nth-child(2))', '#' + $(this)[0].id);
            });

            function initMoreOption(selector, selectorNotHide, selectorInput) {
                if ($(selector).length === 0) {
                    selector = selector + '_' + selector.split('_')[1];
                }
                $(selector + ' ' + selectorNotHide).hide();
                if ($(selectorInput).prop("checked")) {
                    $(selector + ' .form-group').show();
                }
            }
        }
    })
})(jQuery);
