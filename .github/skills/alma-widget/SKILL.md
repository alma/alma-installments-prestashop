---
name: alma-widget
description: Procedure for adding an Alma widget in the PrestaShop module.
---

## Add cart widget

1. Add the hook used in the cart page to the `ModuleInstallerService` in the `HOOK_LIST` constant
2. Create the hook method in the main module class (e.g. `hookDisplayShoppingCartFooter()`).
3. Manage the template to use for the cart in the `renderWidget` function of the `WidgetFrontendService`
4. Manage the cart data retrieved from the context to return the total cart amount and the list of cart products in the `getWidgetVariables` function to return it in a template
