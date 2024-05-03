(function ($) {
    $(function () {
        if ($('.cartdrop-overview').length) {
            const insuranceId = $('#alma-insurance-global').data('insurance-id')
            $('.cartdrop-overview .productcard').each(function (i, e) {
                if ($(e).hasClass(`cart-item-${insuranceId}`)) {
                    $(e).find('input').prop('disabled', true)
                    // Remove change number of input when you wheel (scroll) inside the input
                    e.querySelector('input').addEventListener('wheel', function (e) {
                        document.activeElement.blur();
                    });
                    $(e).find('.pscartdropdown-product-line-grid-right .input-group-btn-vertical').remove()
                    $(e).find('a.label').removeAttr('href')
                }
            })
            // TODO : Need to edit the url for remove an insurance product
            //<a data-alma-association-id="14" data-action="remove-insurance-product" data-token="48fa711ffbafbe349ad4751b4015ad16" href="#" class="alma-remove-insurance-product" data-link="https://sync-ipln.serverapps.io/module/alma/insurance?action=removeInsuranceProduct">
            //                                     Retirer l'assurance
            // 								</a>
        }
    });
})(jQuery)
