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
To check if we load the assets, we need to check the `php_self` of the controller or the name of the controller.
We load the asset for php_self `product`, `cart` and `index` or for controllers `ProductController`, `CartController` and `IndexController`.

### Architecture
- `WidgetService::ALLOWED_CONTROLLERS` defines the list of allowed controllers as a map `ClassName => php_self` (e.g. `'ProductController' => 'product'`). This is the single source of truth for pages where the widget is available.
- `AssetService::loadAssets()` orchestrates the asset loading: it checks if the current page is allowed via `isControllerAllowed()`, then delegates to `loadWidgetAssets()`.
- `AssetService::isControllerAllowed()` receives the allowed controllers list as a parameter and checks both the controller class name and the `php_self` property to handle PrestaShop controller overrides (see below).

### Why check both controller name and `php_self`?
PrestaShop allows themes and modules to override front controllers. When a controller is overridden, `get_class()` returns the subclass name (e.g. `MyThemeProductController`), not `ProductController`. In that case, `php_self` (inherited from the parent) still returns `product`.
Conversely, some module controllers do not set `php_self` (it can be empty or false), but the class name is reliable.
Checking both ensures the widget loads correctly in all scenarios.

## Display the widget

The module implements PrestaShop's `WidgetInterface`, which means it can be rendered anywhere via the `{widget}` Smarty tag.

The widget configuration is passed as a JSON `data-widget-config` attribute on the container `<div>`. It includes:
- `merchantId`
- `mode` (test or live)
- `containerId`
- `purchaseAmount` (in cents)
- `locale`
- `hideIfNotEligible`
- `plans` (active fee plans)

### How it works

Two services handle the frontend rendering:
- `WidgetFrontendService::renderWidget()` — selects the right template and renders it.
- `WidgetFrontendService::getWidgetVariables()` — builds the widget config from the current context (cart total, merchant settings, active plans).

#### Cart widget
The cart widget is rendered via the `displayShoppingCartFooter` hook (or the `alma.widget.cart` tag). It uses the current cart total as `purchaseAmount` and renders `widget/cart.tpl`.

Widget settings (enable/disable, display if not eligible) are configurable per page (product, cart) from the module's admin configuration page via `ProductWidgetAdminForm` and `CartWidgetAdminForm`.

```tpl
{* Widget tag to display the widget on the cart page *}
{widget name='alma' hook="alma.widget.cart"}
```

### Cart refresh

When the cart is updated (e.g. quantity change, product removal), the widget refreshes automatically without a page reload. `alma-widget.js` listens to PrestaShop's native `prestashop.on('updateCart')` event, reads the new cart total from `prestashop.cart.totals`, updates the `purchaseAmount` in the widget's `data-widget-config`, and re-initializes the widget with the new amount.
