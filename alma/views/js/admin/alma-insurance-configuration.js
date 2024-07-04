function loadConfigurationInsurance(domainInsuranceUrl, insuranceConfigurationController, token) {
    let currentResolve
    let save = document.getElementById('alma_config_form_submit_btn');
    save.addEventListener('click', async () => {
        let messageCallback = (e) => {
            console.log(e.data);
            if (currentResolve && e.origin === domainInsuranceUrl) {
                currentResolve(e.data)
                $.ajax({
                    type: 'POST',
                    url: 'ajax-tab.php',
                    dataType: 'json',
                    data: {
                        ajax: true,
                        controller: insuranceConfigurationController,
                        action: 'SaveConfigInsurance',
                        token: token,
                        config: e.data
                    },
                })
                    .success(function (data) {
                        $('.alma-success').html(data.message).show();
                    })
                    .error(function (e) {
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
}
