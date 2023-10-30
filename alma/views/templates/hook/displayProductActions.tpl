<div class="alma-widget-insurance">
    <iframe id="alma-widget-insurance" src="https://protect.staging.almapay.com/almaProductInPageWidget.html"></iframe>
    <div id="modal"></div>
    <input type="radio" name="temp_insurance_alma" class="test-checkbox-insurance"   onclick="handleClickInsurance(this);" value="none" >none<br>
    <input type="radio" name="temp_insurance_alma" class="test-checkbox-insurance"   onclick="handleClickInsurance(this);" value="110-Casse" >Casse<br>
    <input type="radio" name="temp_insurance_alma"   class="test-checkbox-insurance"   onclick="handleClickInsurance(this);" value="110-Vol" ">Vol<br>
    <input type="radio" name="temp_insurance_alma" class="test-checkbox-insurance"    onclick="handleClickInsurance(this);" value="200-Casse + Vol" ">Casse + vol<br>
</div>
<script type="module" src="https://protect.staging.almapay.com/openInPageModal.js"></script>
<script>
    const almaInsuranceIframe = document.getElementById('alma-widget-insurance').contentWindow

    let currentResolve

    let selectedAlmaInsurance = null

    const messageCallback = (e) => {
        if (e.data.type === 'buttonClicked') {
            selectedAlmaInsurance = e.data.buttonText

            if(selectedAlmaInsurance !== undefined) {
                price = selectedAlmaInsurance.price
                // Call
            }
        } else if (currentResolve) {
            currentResolve(e.data)
        }
    }

    const getAlmaWidgetData = () => {
        const promise = new Promise((resolve) => {
            currentResolve = resolve
            window.addEventListener('message', messageCallback)
            almaInsuranceIframe.postMessage('getData', '*')

        }).then((e) => {
            window.removeEventListener('message', messageCallback)
        })
    }

    window.getAlmaWidgetData = getAlmaWidgetData

    let buttonAddToCart = document.querySelector('.add-to-cart');
    buttonAddToCart.addEventListener('click', function (e) {
        e.preventDefault();
        getAlmaWidgetData();
    });


    var currentValue = 0;
    function handleClickInsurance(myRadio) {
        currentValue = myRadio.value;

        almaInsurance = document.getElementById('alma_insurance_price');

        if(almaInsurance == null) {
            let formAddToCart = document.getElementById('add-to-cart-or-refresh');
            let inputInsuranceAlma = document.createElement('input')
            inputInsuranceAlma.setAttribute('value', currentValue)
            inputInsuranceAlma.setAttribute('name', 'alma_insurance_price')
            inputInsuranceAlma.setAttribute('id', 'alma_insurance_price')
            inputInsuranceAlma.setAttribute('type', 'hidden')
            formAddToCart.prepend(inputInsuranceAlma)
        } else {
            console.log('la');
            almaInsurance.setAttribute('value', currentValue)
        }
    }

</script>