# Changelog

## v2.1.0

-   P10X is now available with Alma !
-   Add interest info P10X
-   Fix P10X legal informations
-   Fix error on link breadcrumb alma module
-   Fix error message min/max amount BNPL
-   Fix error when upgrade module Prestashop 1.6.0.X and 1.5.X

## v2.0.0

-   Adds Buy Now Pay Later with Alma
-   Support for 10-installment plans
-   Adds custom position for Alma badge in product page
-   Adds possibility to hide Alma badge if product is non eligible
-   Replace eligibility messages in cart by Alma badge
-   Adds custom position for Alma badge in cart
-   Adds possibility to hide Alma badge if cart is non eligible
-   Makes some css/js optimizations in front
-   Fixes some minor bugs in Alma configuration page
-   Fixes some bugs in smarty templates
-   Fixes delete Alma badge values in database when uninstall Alma module
-   Removes useless files

## v1.4.4

-   Fixes bug preventing orders status to be correctly handled in some situations

## v1.4.3

-   Adds compatibility for PrestaShop 1.5.3.1+
-   Fixes some missing French translations
-   Fixes support for multi-carrier in shipping info data
-   Fixes bug in module's config that would prevent saving configuration when changing min/max amounts of fee plans
-   Makes sure Alma is activated for all carriers upon module installation
-   Fixes Alma badge not showing up on product pages in some older PrestaShop instances
-   Fixes Alma badge loading when used in conjunction with PrestaShop's JavaScript compaction feature
-   Fixes bug with Alma badge when displayed product doesn't have a selectable wanted quantity
-   Makes category exclusion work with secondary categories
-   Prevents crash from `psAdmin` cookie being saved automatically by PrestaShop when it is destroyed
-   Switches Alma badge script/css URLs to jsDelivr

## v1.4.2

-   Register product price hooks on upgrade so that Alma badges are visible by default on product pages after upgrading
-   Update UNPKG URLs to use the unpkg.com domain instead of unpkg.io one, which is apparently a test domain name
-   Fixes the "show product eligibility" setting not being properly deactivated when unchecked
-   Revert use of `use` syntax in `alma.php` as it makes PrestaShop fail on module code evaluation

## v1.4.1

-   Fix paths case to prevent errors on case-sensitive file systems

## v1.4.0

-   All logos have been updated to our latest branding
-   Adds an "Excluded categories" custom tab/page to let merchants deactivate Alma for categories of
    products that are not compatible with our legal terms. The exclusion configuration is accessible via a new Tab in the
    PrestaShop backoffice.
-   Display a detailed payment plan for the selected Alma payment method at checkout.
-   Display a "badge" on product pages, which shows customers whether they can pay products with Alma and, if that's the
    case, what the payment plan would be.
-   Correctly syncs fee plans display and information with Alma's API data
-   Temporarily removes the sending of shipping information as it causes a bug with orders with multiple carriers.
-   Fixes the appearance of config buttons in PrestaShop 1.5.
-   Fixes a bug in the IPN processing code that caused an exception to be thrown when the order had already been
    processed.
-   Fixes issues with eligibility messages display when there are multiple activated fee plans
-   By default, display the module's own confirmation page template on PrestaShop 1.5 & 1.6 (only for new installations)
-   Added some code quality tooling to the repository

## v1.3.1

-   Fixes an issue that could prevent all payment options to show in PrestaShop 1.7

## v1.3.0

-   Compatibility with the Advance EU Compliance module (PrestaShop 1.6)
-   Round prices using PrestaShop's internal method, to comply with merchant's configured preferences
-   Include cart contents & shipping information in payment data to improve customer UX & fraud detection

## v1.2.7

-   Dependencies update
-   Send order reference to Alma upon payment confirmation, to make it easier for merchants to
    associate Alma payments with actual orders in their PrestaShop orders

## v1.2.6

-   Dependencies update
-   Improves paid amount comparison in payment validation to limit false positives

## v1.2.5

-   Dependencies update (to include bug fix in Alma PHP Client)

## v1.2.4

-   Fixes a bug that could prevent Alma from working on PrestaShop installed in a subdirectory of the main domain

## v1.2.3

-   New attempt at overcoming float rounding issues on payment validation
-   Improves onboarding UX: more guidance for the merchant, and less constraints on API keys requirements

## v1.2.2

-   Adds hybrid PnX from 1.5.6.2 to 1.6.x
-   Fixes eligibility message on cart not being in sync with configured hybrid PnX amount bounds

## v1.2.1

-   Fixes boundary checks on installment plans that triggered errors on disabled plans
-   Fixes p3x payment option remaining enabled even when unchecked in the module configuration

## v1.2.0

-   Adds refund capability with either PrestaShop's refund feature & order state change to "refund" state (configurable)
-   Applies security suggestions from PrestaShop's security audit
-   Updates dependencies (alma-php-client)
-   Changes default values for some configurable messages

## v1.1.1

-   Fixes usage of `empty` that can cause issues in older versions of PHP

## v1.1.0

-   Adds support for multiple installments plans (2-, 3- and 4-installment plans) on PrestaShop 1.7+
-   Adds order_total as a template variable for displayPayment
-   Various fixes to comply with PSR1/PSR2 and PrestaShop validator
-   Preparation for marketplace validation & release

## v1.0.1

Let's start following semver.

-   Adds User-Agent string containing the module's version, PrestaShop version, PHP client and PHP versions, to all
    requests going to Alma's API.

## v1.0.0

This version evolved for a while without any version bump 🤷‍♂️
Features in the latest push to this release:

-   Compatible from PrestaShop 1.5.6.2 to 1.7.x
-   Module can be configured in Test and Live mode; Test mode only shows Alma's payment method to visitors who are also
    logged in to the shop's backoffice
-   A message displays below the cart to indicate whether the purchase is eligible to monthly installments
-   The module adds a payment method/payment option to the checkout, which redirects the user to Alma's payment page.
    If everything goes right (i.e. Customer doesn't cancel, pays the right amount, ... ), an order is created and validated
    upon customer return.
