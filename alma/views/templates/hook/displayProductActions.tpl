<div class="alma-widget-insurance">
    <iframe id="alma-widget-insurance" src="https://poc-iframe.dev.almapay.com/almaProductInPageWidget.html"></iframe>
    <div id="modal"></div>
</div>
<script type="module" src="https://poc-iframe.dev.almapay.com/openInPageModal.js"></script>
<script>
    window.onload = function() {
        let button = document.querySelector('.add-to-cart');
        button.addEventListener('click', function (e) {
            console.log('test ici');
            // todo : need fix openModal()
            openModal();
        });
    };
</script>