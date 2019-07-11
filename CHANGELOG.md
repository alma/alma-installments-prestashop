Changelog
=========

v1.2.2
------

* Adds hybrid PnX from 1.5.6.2 to 1.6.x
* Fixes eligibility message on cart not being in sync with configured hybrid PnX amount bounds 

v1.2.1
------

* Fixes boundary checks on installment plans that triggered errors on disabled plans
* Fixes p3x payment option remaining enabled even when unchecked in the module configuration

v1.2.0
------

* Adds refund capability with either PrestaShop's refund feature & order state change to "refund" state (configurable)
* Applies security suggestions from PrestaShop's security audit
* Updates dependencies (alma-php-client)
* Changes default values for some configurable messages

v1.1.1
------

* Fixes usage of `empty` that can cause issues in older versions of PHP

v1.1.0
------

* Adds support for multiple installments plans (2-, 3- and 4-installment plans) on PrestaShop 1.7+
* Adds order_total as a template variable for displayPayment
* Various fixes to comply with PSR1/PSR2 and PrestaShop validator
* Preparation for marketplace validation & release


v1.0.1
------

Let's start following semver.

* Adds User-Agent string containing the module's version, PrestaShop version, PHP client and PHP versions, to all
requests going to Alma's API.

v1.0.0
------

This version evolved for a while without any version bump 🤷‍♂️
Features in the latest push to this release:

* Compatible from PrestaShop 1.5.6.2 to 1.7.x
* Module can be configured in Test and Live mode; Test mode only shows Alma's payment method to visitors who are also
logged in to the shop's backoffice
* A message displays below the cart to indicate whether the purchase is eligible to monthly installments
* The module adds a payment method/payment option to the checkout, which redirects the user to Alma's payment page.
If everything goes right (i.e. Customer doesn't cancel, pays the right amount, ... ), an order is created and validated
upon customer return.
