<div class="panel" id="fieldset_0">
    <div class="panel-heading">
        <img src="/modules/alma/views/img/logos/alma_tiny.svg" alt="{l s='Configuration' mod='alma'}">{l s='Configuration' mod='alma'}
    </div>
    <div class="form-wrapper">
        <div class="form-group">
            <div class="alma--insurance-iframe">
                <iframe id="config-alma-iframe" class="alma-insurance-iframe" width="100%" height="100%" src="{$iframeUrl}"></iframe>
            </div>
        </div>
    </div><!-- /.form-wrapper -->
    <div class="panel-footer">
        <button type="submit" value="1" id="alma_config_form_submit_btn" name="alma_config_form" class="button btn btn-default pull-right">
            <i class="process-icon-save"></i> {l s='Save' mod='alma'}
        </button>
    </div>
</div>

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