Widget documentation
===================================

## Context
The widget allows you to display the Alma monthly payment offer on the product, cart or home page of your e-commerce website.
We load the assets of the widget on home page because some merchant want to display the widget on the home page with their featured products.
You can enable or disable the widget from the configuration page of the module.

## Inject the widget CDN
We need to inject the widget CDN in the hook `actionFrontControllerSetMedia` to insert assets in the header of the page needed.
[Asset Management Doc](https://devdocs.prestashop-project.org/1.7/themes/getting-started/asset-management/#registering-assets)
To check if we load the assets, we need to check the `php_self` of the controller or the name of the controller.
We load the asset for php_self `product`, `cart` and `index` or for controllers `ProductController`, `CartController` and `IndexController`.
