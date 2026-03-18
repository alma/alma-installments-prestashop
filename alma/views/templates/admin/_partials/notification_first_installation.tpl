<div class="alma first-installation alert alert-info" id="alma_first_installation">
    <h2>{l s='Thanks for installing Alma!' d='Modules.Alma.Notifications'}</h2>
    <p>
        <strong>{l s='You need to create an Alma account before proceeding.' d='Modules.Alma.Notifications'}</strong>
        <br />
        <a href="https://support.getalma.eu/hc/fr/articles/360007913920-D%C3%A9marrer-avec-le-paiement-en-plusieurs-fois-Alma-sur-mon-site-e-commerce" target="_blank">
            {l s='Read our getting started guide' d='Modules.Alma.Notifications'}
        </a>
    </p>
    <p>
        <b>{l s='You can then fill in your API keys:' d='Modules.Alma.Notifications'}</b>
        <br />
        {l
            s='You can find your Live API key in [1]your Alma dashboard[/1]'
            sprintf=['<a href="https://dashboard.getalma.eu/api" target=\"_blank\">']
            d='Modules.Alma.Notifications'
        }
        <br />
        {l
            s='To use the Test mode, you need your Test API key from [1]your sandbox dasboard[/1]'
            sprintf=['<a href="https://dashboard.sandbox.getalma.eu/api" target=\"_blank\">']
            d='Modules.Alma.Notifications'
        }
        <br />
    </p>
    <p>
        {l
            s='If you have any problems, please contact us by email at [1]support@getalma.eu[/1]'
            sprintf=['<a href="mailto:support@getalma.eu">']
            d='Modules.Alma.Notifications'
        }
    </p>
</div>
