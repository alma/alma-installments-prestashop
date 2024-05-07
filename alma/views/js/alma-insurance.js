(function ($) {
    $(function () {
        if ($('.cartdrop-overview').length) {
            prestashop.on(
                'updateCart',
                function(event) {
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
                            // TODO : Need to edit the url of delete product for remove an insurance product
                            $(e).find('.remove-from-cart').remove()
                        }
                    })
                });
        }
    });
})(jQuery)
