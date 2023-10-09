<div class="alma--insurance-iframe">
    <iframe id="config-alma-iframe" width="100%" height="100%" src="https://poc-iframe.dev.almapay.com/backOffice.html?option1=true"></iframe>
</div>
<button>Save</button>
<script type="module">
    window.addEventListener('message', async function(event) {
        try {
            let response = await fetch('ajax-tab.php'
            {
                method: "POST"
                headers: {
                    "Content-Type": "application/json"
                },
                data: {
                    ajax: true,
                    controller: 'AdminAlmaInsurance',
                }
            });
            let data = await response.json();

        } catch(e) {
            console.log(e);
        }
        if (event.data) {
            // faire un check de l'origine
            console.log('checked', event.data)}
        }
    )
</script>