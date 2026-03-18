<div class="alma ps-account alert alert-info">
    <h2>
        {l s='To use Alma, please follow these steps' d='Modules.Alma.Notifications'}
    </h2>
    <ol>
        <li>
            <strong>
                {l s='Associate PrestaShop account (just below)' d='Modules.Alma.Notifications'}
            </strong>
        </li>
        <li><strong>{l s='Create an Alma account' d='Modules.Alma.Notifications'}</strong>
            <p>
                <a href="https://support.getalma.eu/hc/fr/articles/360007913920-D%C3%A9marrer-avec-le-paiement-en-plusieurs-fois-Alma-sur-mon-site-e-commerce" target="_blank">
                    {l s='Consult our getting started guide' d='Modules.Alma.Notifications'}
                </a>
            </p>
        </li>
        <li>
            <strong>{l s='Enter your API key' d='Modules.Alma.Notifications'}</strong>
            <p>
                {l
                    s='Find your API live key on your [1]Alma dashboard[/1]'
                    sprintf=['<a href="https://dashboard.getalma.eu/api" target="_blank">']
                    d='Modules.Alma.Notifications'
                }
                <br />
                {l
                    s='To use Test mode, retrieve your Test API key from your [1]sandbox dashboard[/1]'
                    sprintf=['<a href="https://dashboard.sandbox.getalma.eu/api" target="_blank">']
                    d='Modules.Alma.Notifications'
                }
            </p>
        </li>
    </ol>
</div>
<div class="alma alert alert-dismissible alert-info">
    <p>
        {l s='Link your store to your PrestaShop account to take full advantage of the modules offered by the PrestaShop Marketplace and optimize your experience.' d='Modules.Alma.Notifications'}
    </p>
    {l
        s='You can find the module [1]here[/1]'
        sprintf=['<a href="https://addons.prestashop.com/en/administrative-tools/49648-prestashop-account.html" target="_blank">']
        d='Modules.Alma.Notifications'
    }
</div>
