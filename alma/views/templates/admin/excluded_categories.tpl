<p>
    {l
        s='Some products (gift cards, license keys, software, weapons, ...) cannot be sold with Alma, as per [1]our terms[/1] (see Exclusions paragraph).'
        sprintf=['<a href="https://getalma.eu/legal/terms/payment" target="_blank">']
        d='Modules.Alma.Settings'
    }
</p>

<p>{l s='If you are selling such products on your shop, you need to configure Alma so that it is not enabled when customers view or shop them.' d='Modules.Alma.Settings'}</p>

<p>
    {l
        s='Use the [1]category exclusions page[/1] to comply with these restrictions.'
        sprintf=["<strong><a href='$excludedCategoriesPageLink'>", "</a></strong>"]
        d='Modules.Alma.Settings'
    }
</p>
<p>
    <strong>{l s='Categories currently excluded : ' d='Modules.Alma.Settings'}</strong>
    {$excludedCategories|escape:'htmlall':'UTF-8'}
</p>
