Widget documentation
===================================

## Context
The widget allows you to display the Alma monthly payment offer on the product, cart or home page of your e-commerce website.
We load the assets of the widget on home page because some merchant want to display the widget on the home page with their featured products.
You can enable or disable the widget from the configuration page of the module.
We will use the widget Interface to display our widget in the front office.

## Inject the widget CDN
We need to inject the widget CDN in the hook `actionFrontControllerSetMedia` to insert assets in the header of the page needed.
[Asset Management Doc](https://devdocs.prestashop-project.org/1.7/themes/getting-started/asset-management/#registering-assets)

### Architecture
- `WidgetService::ALLOWED_CONTROLLERS` defines the list of allowed controllers as a map `ClassName => php_self` (e.g. `'ProductController' => 'product'`). This is the single source of truth for pages where the widget is available.
- `AssetService::loadAssets()` orchestrates the asset loading: it checks if the current page is allowed via `isControllerAllowed()`, then delegates to `loadWidgetAssets()`.
- `AssetService::isControllerAllowed()` receives the allowed controllers list as a parameter and checks both the controller class name and the `php_self` property to handle PrestaShop controller overrides (see below).

### Why check both controller name and `php_self`?
PrestaShop allows themes and modules to override front controllers. When a controller is overridden, `get_class()` returns the subclass name (e.g. `MyThemeProductController`), not `ProductController`. In that case, `php_self` (inherited from the parent) still returns `product`.
Conversely, some module controllers do not set `php_self` (it can be empty or false), but the class name is reliable.
Checking both ensures the widget loads correctly in all scenarios.

## Display the widget
To display the widget, we need to set some data:
- Merchant id
- Mode (test or live)
- Container id
- Purchase amount
- Locale
- hideIfNotEligible
- Fee Plan

### How it works
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
