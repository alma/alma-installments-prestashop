<div class="alert alert-success alma-success"  style="display:none" data-alert="success"></div>
<div class="alert alert-danger alma-danger" style="display:none" data-alert="danger"></div>

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
    let currentResolve
    let save = document.getElementById('alma_config_form_submit_btn');
    save.addEventListener('click', async () => {
        let messageCallback = (e) => {
            if (currentResolve && e.origin === '{Alma\PrestaShop\Helpers\ConstantsHelper::DOMAIN_URL_INSURANCE}') {
                currentResolve(e.data)
                $.ajax({
                    type: 'POST',
                    url: 'ajax-tab.php',
                    dataType: 'json',
                    data: {
                        ajax: true,
                        controller: '{Alma\PrestaShop\Helpers\ConstantsHelper::BO_CONTROLLER_INSURANCE_CONFIGURATION_CLASSNAME}',
                        action: 'SaveConfigInsurance',
                        token: '{$token}',
                        config: e.data
                    },
                })
                    .success(function(data) {
                        $('.alma-success').html(data.message).show();
                    })
                    .error(function(e) {
                        if (e.status !== 200) {
                            let jsonData = JSON.parse(e.responseText);
                            $('.alma-danger').html(jsonData.error.msg).show();
                        }
                    });
            }
        }

        const almaGetInsuranceConfigurationData = () => {
            const iframe = document.getElementById('config-alma-iframe').contentWindow

            const promise = new Promise((resolve) => {
                currentResolve = resolve
                iframe.postMessage('send value', '*')
                window.addEventListener('message', messageCallback)
            }).then((data) => {
                currentResolve = null
                window.removeEventListener('message', messageCallback)
            })
        }
        window.getData = almaGetInsuranceConfigurationData
        almaGetInsuranceConfigurationData()
    });
</script>