Migration documentation
===================================

## Context
The migration process allow to migrate configuration data from an old version of the module to a new one, without losing data and configuration.

## FeePlan Migration
For the feePlan migration we get the old feePlan configuration from the key `ALMA_FEE_PLANS`,
then we loop through the feePlans and create the new one with the keys:
- `enabled` => `state`
- `min` => `min_amount`
- `max` => `max_amount`
- `order` => `sort_order`

If the don't get the old feePlan configuration, we create it from the API with default values.

## Widget Migration
For the widget migration we get the old keys about widget:
- `ALMA_SHOW_PRODUCT_ELIGIBILITY` => `ALMA_PRODUCT_WIDGET_STATE`
- `ALMA_PRODUCT_WDGT_NOT_ELGBL` => `ALMA_PRODUCT_WIDGET_DISPLAY_NOT_ELIGIBLE`
- `ALMA_WIDGET_POSITION_CUSTOM` => We keep it to check if the product widget custom position is enabled or not
- `ALMA_WIDGET_POSITION_SELECTOR` => We keep it to use the position if the product widget position is enabled
- `ALMA_PRODUCT_PRICE_SELECTOR` => We removed it
- `ALMA_PRODUCT_ATTR_SELECTOR` => We removed it
- `ALMA_PRODUCT_ATTR_RADIO_SELECTOR` => We removed it
- `ALMA_PRODUCT_COLOR_PICK_SELECTOR` => We removed it
- `ALMA_PRODUCT_QUANTITY_SELECTOR` => We removed it
- `ALMA_SHOW_CART_ELIGIBILITY` => `ALMA_CART_WIDGET_STATE`
- `ALMA_CART_WDGT_NOT_ELGBL` => `ALMA_CART_WIDGET_DISPLAY_NOT_ELIGIBLE`
- `ALMA_CART_WIDGET_POSITION_CUSTOM` => We keep it to check if the cart widget custom position is enabled or not
- `ALMA_CART_WDGT_POS_SELECTOR` => We keep it to use the position if the cart widget position is enabled

## Payment button custom and Excluded categories message Migration
For the payment button custom migration we get the old keys about payment button custom:
- `ALMA_PAY_NOW_BUTTON_TITLE`
- `ALMA_PAY_NOW_BUTTON_DESC`
- `ALMA_PNX_BUTTON_TITLE`
- `ALMA_PNX_BUTTON_DESC`
- `ALMA_PNX_AIR_BUTTON_TITLE`
- `ALMA_PNX_AIR_BUTTON_DESC`
- `ALMA_DEFERRED_BUTTON_TITLE`
- `ALMA_DEFERRED_BUTTON_DESC`
- `ALMA_NOT_ELIGIBLE_CATEGORIES`

These key have a json value like `{"1":{"locale":"en-US","string":"Pay in %d installments"}}`
We will use the id key to get the language id and the `string` to get the translation.
And we will create the new keys like `ALMA_{PAYMENT_TYPE}_BUTTON_TITLE_{ID}`
The payment type was `PAY_NOW`, `PNX`, `PNX_AIR` or `DEFERRED` and will be `PAYNOW`, `PNX`, `CREDIT` or `PAYLATER`
And the not eligible message from `ALMA_NOT_ELIGIBLE_CATEGORIES` will be set to `ALMA_EXCLUDED_CATEGORIES_MESSAGE_{ID}`.

## Other keys Migration
For the other keys we just need to change the key name:
- `ALMA_CATEGORIES_WDGT_NOT_ELGBL` => `ALMA_EXCLUDED_CATEGORIES_WIDGET_DISPLAY_NOT_ELIGIBLE`
- `ALMA_STATE_REFUND_ENABLED` => `ALMA_REFUND_ON_CHANGE_STATE`
- `ALMA_STATE_REFUND` => `ALMA_STATE_REFUND_SELECT`
- `ALMA_ACTIVATE_INPAGE` => `ALMA_INPAGE_STATE`
- `ALMA_ACTIVATE_LOGGING_ON` => `ALMA_DEBUG_STATE`
If the key doesn't exist in the old version of the module, we create it with a default value.

And some keys are not changed because they are still used in the new version of the module:
- `ALMA_INPAGE_PAYMENT_BUTTON_SELECTOR` => `ALMA_INPAGE_PAYMENT_BUTTON_SELECTOR` // No change
- `ALMA_INPAGE_PLACE_ORDER_BUTTON_SELECTOR` => `ALMA_INPAGE_PLACE_ORDER_BUTTON_SELECTOR` // No change
- `ALMA_API_MODE` => `ALMA_API_MODE` // No change
- `ALMA_LIVE_API_KEY` => `ALMA_LIVE_API_KEY` // No change
- `ALMA_TEST_API_KEY` => `ALMA_TEST_API_KEY` // No change
