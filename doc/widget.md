Widget documentation
===================================

## Context
The widget allows you to display the Alma monthly payment offer on the product, cart or home page of your e-commerce website.
We load the assets of the widget on home page because some merchant want to display the widget on the home page with their featured products.
You can enable or disable the widget from the configuration page of the module.

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
