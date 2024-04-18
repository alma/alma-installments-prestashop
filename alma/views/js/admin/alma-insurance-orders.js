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
        $('.js-choice-options .js-dropdown-item').each(function(i, e){
            $(e).attr("data-confirm_modal", "module-modal-confirm-refund-order-with-insurance");
            var rowOrder = $(e).parents('tr');
            var checkboxOrder = rowOrder.find('.bulk_action-type .js-bulk-action-checkbox');
            var buttonStatus = this;
            var statusId = $(buttonStatus).data('value');

            $(e).off('click');
            $(e).on('click', function(e) {
                e.stopImmediatePropagation();
                e.preventDefault();

                ajaxOrderAndConfirmModal(checkboxOrder, buttonStatus, statusId);
            });

        });

        function ajaxOrderAndConfirmModal(checkboxOrder, buttonStatus, statusId) {
            const updateConfirmModal = new window.ConfirmModal(
                {
                    id: 'confirm-refund-order-insurance-modal',
                    confirmTitle: window.InsuranceModalConfirm.confirmTitleText,
                    closeButtonLabel: window.InsuranceModalConfirm.closeButtonLabelText,
                    confirmButtonLabel: window.InsuranceModalConfirm.confirmButtonLabelText,
                    confirmButtonClass: 'btn-primary',
                    confirmMessage: window.InsuranceModalConfirm.confirmMessageLine1Text + '<br>' + window.InsuranceModalConfirm.confirmMessageLine2Text,
                    closable: true,
                    customButtons: [],
                },

                () => confirmAction('update', buttonStatus),
            );

            $.ajax({
                type: 'GET',
                url: 'ajax-tab.php',
                dataType: 'json',
                data: {
                    ajax: true,
                    controller: 'AdminAlmaInsuranceOrdersList',
                    action: 'OrdersList',
                    token: token,
                    orderId: checkboxOrder.val(),
                    statusId: statusId,
                },
            })
            .success(function (result) {
                if (!result.canRefund) {
                    updateConfirmModal.show();
                } else {
                    confirmAction('update', buttonStatus);
                }

            })
            .error(function (result) {
                console.log('error');
                console.log(result);
            });
        }

        function confirmAction(action, element) {
            const $parent = element.closest('.js-choice-options');
            const url = $($parent).data('url');

            submitForm(url, element);
        }

        /**
         * Submits the form.
         * @param {string} url
         * @param {jQuery} $button
         * @private
         */
        function submitForm(url, $button) {
            const selectedStatusId = $($button).data('value');

            const $form = $('<form>', {
                action: url,
                method: 'POST',
            }).append(
                $('<input>', {
                    name: 'value',
                    value: selectedStatusId,
                    type: 'hidden',
                }));

            $form.appendTo('body');
            $form.submit();
        }

    })
})(jQuery);
