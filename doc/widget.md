Render Widget
===================================

## Context
We will use the widget Interface to display our widget in the front office.

## How it works
By default, we will set the widget in the hook used natively `displayProductAdditionalInfo` for the product page and `displayShoppingCartFooter` for the cart page,
but the merchant cans use our tag `alma.widget.product` and `alma.widget.cart` to customize the position of the widget in the template.
If our tags is used, we will not display the widget in the default hooks.
// TODO: Need to validate this process with Product and EM.

```tpl
// Widget tag to display the widget in a template product
{widget name='alma' hook="alma.widget.product" product=$product}

// Widget tag to display the widget in a template cart
{widget name='alma' hook="alma.widget.cart" cart=$cart}
```
