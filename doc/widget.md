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
- `WidgetFrontendService::getWidgetVariables()` — builds the widget config from the current context (cart total or product price, merchant settings, active plans).

Both the cart and product widgets share the same `views/templates/widget/widget.tpl` template.

Widget settings (enable/disable, display if not eligible) are configurable per page (product, cart) from the module's admin configuration page via `ProductWidgetAdminForm` and `CartWidgetAdminForm`.

#### Cart widget
The cart widget is rendered via the `displayShoppingCartFooter` hook or the `{widget}` tag with `alma.widget.cart`. It uses the current cart total as `purchaseAmount`.

```tpl
{* Widget tag to display the widget on the cart page *}
{widget name='alma' hook="alma.widget.cart"}
```

#### Product widget
The product widget is rendered via the `displayProductPriceBlock` hook (filtered to `type === after_price` only) or the `{widget}` tag with `alma.widget.product`. It uses the current product price (tax included) as `purchaseAmount`, retrieved from the page's `FrontController`.

The `after_price` filter ensures the widget is only injected at the right position on the product page — directly after the price — and not at other positions where `displayProductPriceBlock` is also called (e.g. `weight`, `before_price`).

When both the hook and the widget tag containers are present on the page, the widget tag (`#alma-widget-product`) takes priority over the hook container (`#alma-widget-ProductPriceBlock`).

```tpl
{* Widget tag to display the widget on the product page *}
{widget name='alma' hook="alma.widget.product"}
```

#### Legacy custom widget position (module v5 backward compatibility)

Module v5 allowed merchants to define a custom CSS selector to control where the cart widget was injected in the DOM. This setting is preserved and still honored in the current version.

When `ALMA_CART_WIDGET_POSITION_CUSTOM` is enabled, `WidgetFrontendService` uses the CSS selector stored in `ALMA_WDGT_POS_SELECTOR` as the widget's `containerId` instead of the default `#alma-widget-cart`. If the option is disabled, the standard container ID is used.

- `ConfigurationRepository::getCartWidgetOldPositionCustom()` — returns `true` if the legacy custom position is active.
- `ConfigurationRepository::getCartWidgetOldPositionSelector()` — returns the saved CSS selector (e.g. `#my-custom-selector`).

### Cart refresh

When the cart is updated (e.g. quantity change, product removal), the widget refreshes automatically without a page reload. `alma-widget.js` listens to PrestaShop's native `prestashop.on('updateCart')` event, reads the new cart total from `prestashop.cart.totals`, updates the `purchaseAmount` in the widget's `data-widget-config`, and re-initializes the widget with the new amount.

### Product widget dynamic update

On the product page, the widget amount updates automatically when the selected combination or quantity changes.

**Combination change** — PrestaShop fires `prestashop.on('updatedProduct', eventData)` when a combination is selected. `alma-widget.js` extracts the new unit price from `eventData.product_prices` (the re-rendered price HTML) by reading the `content` attribute of `.current-price-value` via native `getAttribute()` (jQuery's `.attr()` is unreliable for non-standard attributes on `<span>`). The price is converted to cents and stored as the new unit price.

**Quantity change** — A `change` listener on `[name="qty"]` recalculates the total amount (`unit price × quantity`) and re-initializes the widget.

Both paths call `updateProductWidget()` which updates `purchaseAmount` in `data-widget-config` and re-initializes the widget.

## Excluded categories

Merchants can exclude specific product categories from Alma eligibility via the **Excluded categories** page in the back office. When a cart or product belongs to an excluded category, the widget is hidden.

If the **Display message** option is enabled (configurable in the module settings), a non-eligibility message is shown in place of the widget, along with the Alma logo. The message is customizable per language.

### How it works

- `ExcludedCategoriesService::isExcluded()` checks whether any product belongs to an excluded category by comparing the product's categories against the stored exclusion list. For the cart widget it receives the full cart products list; for the product widget it receives a single-item array with the current product.
- `WidgetFrontendService::getWidgetVariables()` calls this service and passes `isExcluded`, `showExcludedMessage`, and `excludedMessage` to the template.
- The `widget.tpl` template renders the widget normally when no product is excluded. When a product is excluded, it either hides the widget entirely or displays the configured message depending on the **Display message** setting.
