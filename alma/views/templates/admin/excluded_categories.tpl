<p>
    {l
        s='Some products (gift cards, license keys, software, weapons, ...) cannot be sold with Alma, as per [1]our terms[/1] (see Exclusions paragraph).'
        tags=['<a href="https://getalma.eu/legal/terms/payment" target="_blank">']
        mod='alma'
    }
</p>

<p>{l s='If you are selling such products on your shop, you need to configure Alma so that it is not enabled when customers view or shop them.' mod='alma'}</p>

<p>
    {l
        s='Use the [1][2]category exclusions page[/2][/1] to comply with these restrictions.'
        tags=["<strong>", "<a href='$excludedCategoriesPageLink'>"]
        mod='alma'
    }
</p>
<p>
    <strong>{l s='Categories currently excluded : ' mod='alma'}</strong>
    {$excludedCategories|escape:'htmlall':'UTF-8'}
</p>
