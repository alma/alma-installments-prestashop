# Changelog

## v4.10.0 - 2025-07-28

### Changes

### 🚀 New Features

- feat: Add fee sharing for credit in Prestashop 1.6 (#705)

### 🐛 Bug Fixes

- fix: Handle the in-page event button for multiple payment buttons (#703)

#### Contributors

@Benjamin-Freoua-Alma, @alma-renovate-bot[bot], [alma-renovate-bot[bot]](https://github.com/apps/alma-renovate-bot) and [github-actions[bot]](https://github.com/apps/github-actions)

## v4.9.1 - 2025-05-12

### Changes

### 🐛 Bug Fixes

- fix: Unregister hook DisplayProductActions (#697)
- fix: Shipment data type error (#693)

#### Contributors

@Benjamin-Freoua-Alma, @alma-renovate-bot[bot], @remi-zuffinetti, [alma-renovate-bot[bot]](https://github.com/apps/alma-renovate-bot) and [github-actions[bot]](https://github.com/apps/github-actions)

## v4.9.0 - 2025-04-09

### Changes

### 🚀 New Features

- feat: Refactor mismatch with native Prestashop process (#684)
- feat: Re-wording in page back office field (#679)
- feat: set inpage by default on activation (#677)
- feat: update widget version to 4.x.x (#678)
- feat: Remove insurance feature without history order insurance (#667)

### 🐛 Bug Fixes

- fix: Validate injection for old Prestashop version (#680)

#### Contributors

@Benjamin-Freoua-Alma, @alma-renovate-bot[bot], @carine-bonnafous, @joyet-simon, @remi-zuffinetti, @webaaz, [alma-renovate-bot[bot]](https://github.com/apps/alma-renovate-bot) and [github-actions[bot]](https://github.com/apps/github-actions)

## v4.8.0 - 2025-03-17

### Changes

### 🚀 New Features

- feat: Update version compatibility with Prestashop 8.2.1 (#666)
- feat: Weird calls to /return-urls. Payment id is undefined or absent (#662)
- feat: Avoid prestashop issue with duplicate order for prestashop < 1.7.7.0 (#659)
- feat: Compatibility PSRLog for Prestashop 9 (#656)
- feat: Add error code 403 for invalid signature in gather cms data (#654)
- feat: Use new payment endpoint for order status (#652)

### 🐛 Bug Fixes

- fix: Handle In-page if payment option is checked before page loaded (#665)
- fix: Issue checkout process with old Prestashop version (#660)
- fix: Error in payment return when the order payment was not retrieved (#658)
- fix: Force type to string for cms data theme version (#653)

#### Contributors

@Benjamin-Freoua-Alma, @Francois-Gomis, @alma-renovate-bot[bot], @webaaz, [alma-renovate-bot[bot]](https://github.com/apps/alma-renovate-bot) and [github-actions[bot]](https://github.com/apps/github-actions)

## v4.7.1 - 2025-02-17

### Changes

### 🐛 Bug Fixes

- fix: Controller unknown for marketplace tool (#643)
- fix: Deduplicate business event calls (#646)
- fix: Handle hook displayHeader for some Prestashop versions (#645)

#### Contributors

@Benjamin-Freoua-Alma, @alma-renovate-bot[bot], [alma-renovate-bot[bot]](https://github.com/apps/alma-renovate-bot) and [github-actions[bot]](https://github.com/apps/github-actions)

## v4.7.0 - 2025-02-04

### Changes

### 🚀 New Features

- feat: Merchant business event (#631)
- feat: Add position at checkout for GatherCMSData (#633)
- feat: Refactor configuration form (#611)

### 🐛 Bug Fixes

- fix: Update HTML element in FrontHeaderHookController for better semantic (#636)
- fix: Avoid error about get customer for eligibility of cart widget (#632)
- fix: Update migration files from v1.1.0 (#622)
- fix: Inject module in OrderHelper to use translation function (#621)

#### Contributors

@Benjamin-Freoua-Alma, @alma-renovate-bot[bot], @carine-bonnafous, @joyet-simon, @remi-zuffinetti, [alma-renovate-bot[bot]](https://github.com/apps/alma-renovate-bot) and [github-actions[bot]](https://github.com/apps/github-actions)

## v4.6.0 - 2024-12-16

### Changes

### 🚀 New Features

- feat: Product analytics for plugin performance (#587)

### 🐛 Bug Fixes

- fix: Custom Description for payment button credit (#614)

#### Contributors

@Benjamin-Freoua-Alma, @alma-renovate-bot, @alma-renovate-bot[bot], @github-actions, @joyet-simon and @remi-zuffinetti

## v4.5.0 - 2024-11-13

### Changes

### 🚀 New Features

- feat: add inpage setting for selector payment buttons (#581)

### 🐛 Bug Fixes

- fix: Update wording for In-Page advanced feature (#594)
- fix: Prevent crash when there is not quantity input on product page for insurance (#598)
- fix: issue with some translation in payment buttons (#593)

#### Contributors

@Benjamin-Freoua-Alma, @alma-renovate-bot, @alma-renovate-bot[bot], @carine-bonnafous, @github-actions and @olance

## v4.4.1 - 2024-10-30

### 🐛 Bug Fixes

fix: Error configuration module with ps_accounts lower than 5.3.0

## v4.4.0 - 2024-10-07

### Changes

### 🚀 New Features

- feat: In page is now available for Credit (#575)
- feat: Remove insurance in cart when deactivated (#569)
- feat: HMAC verification on IPN callback for security (#561)

### 🐛 Bug Fixes

- fix: handle the height automatically of the insurance widget (#572)
- fix: install module if container of getService Ps_account is null (#565)
- fix: Inpage on prestashop 1.6 with third party module of checkout (#566)

#### Contributors

@Benjamin-Freoua-Alma, @FranceBe, @alma-renovate-bot, @alma-renovate-bot[bot], @github-actions and @joyet-simon

## v4.3.0 - 2024-08-12

### Changes

### 🚀 New Features

- feat: Shipment info analysis (#545)

#### Contributors

@Benjamin-Freoua-Alma, @Francois-Gomis, @alma-renovate-bot and @github-actions

## v4.2.0 - 2024-08-06

### Changes

### 🚀 New Features

- feat: Insurance compatability with PS 1.7 (#522)
- feat: Insurance product page cannot be added to cart (#530)

### 🐛 Bug Fixes

- fix: create payment with object category in product (#551)
- fix: load alma script on dashboard for ps17 (#549)
- fix: eligible limit of feePlans (#550)
- fix: display multiple notification dashboard on ps17 (#548)
- fix: install module with ps_account from ps17 to ps8 (#547)
- fix: openmodal insurance in the first load product page (#543)
- fix: change order status error (#542)
- fix: install module without ConfigCache about PS Account (#541)

#### Contributors

@Benjamin-Freoua-Alma, @alma-renovate-bot, @alma-renovate-bot[bot], @carine-bonnafous and @github-actions

## v4.1.4 - 2024-07-29

### Changes

### 🐛 Bug Fixes

- fix: display order details of insurance subscription (#534)
- fix: add quantity insurance in the product and display cart item (#533)
- fix: Issue price widget in Cart Item (#532)
- fix: compatibility PS 1.6 during installing module (#531)

#### Contributors

@Benjamin-Freoua-Alma, @alma-renovate-bot, @gdraynz and @github-actions

## v4.1.3 - 2024-07-18

### Changes

### 🐛 Bug Fixes

- fix: Declaration LinkFactory (#514)
- fix: Issue with insurance on mobile display (#515)

#### Contributors

@Benjamin-Freoua-Alma, @carine-bonnafous and @github-actions

## v4.1.2 - 2024-07-16

### Changes

### 🐛 Bug Fixes

- fix: Avoid change quantity with module pscartdropdown for insurance (#510)
- fix: Issue PS Account with PS 8.1.7 (#506)
- fix: Issue quantity insurance if we change quantity in cart (#508)
- fix: Issue insurance for product not eligible created after import (#509)

#### Contributors

@Benjamin-Freoua-Alma, @carine-bonnafous and @github-actions

## v4.1.1 - 2024-07-10

### Changes

- fix: add insurance from cart page with multiple execution (#499)

#### Contributors

@Benjamin-Freoua-Alma, @carine-bonnafous and @github-actions

## v4.1.0 - 2024-07-08

- feat: Link your store with PS Account
- feat: Insurance - add insurance in cart item
- feat: Insurance - compatibility with save cart opartsavecart module
- feat: Update cart data for PNX & Deferred
- fix: Compatibility with PHP 5.6

## v4.0.1 - 2024-06-04

### 🐛 Bug Fixes

fix: instance insurance productHelper

## v4.0.0

- feature : Alma Insurance
- improvement : Add unit tests + refacto code
- feature : Order State API

## v3.2.1

- fix: Issue with the credit payment method

## v3.2.0

- feature : In Page is available with deferred payments
- fix: Removed the setMedia on HookController for Prestashop 1.7+
- fix: Issue for save configuration for the module on Prestashop 1.5 with last Chrome
- fix: prevent soc to send data twice

## v3.1.6

- fix: Pay Now without inpage PS 1.5 / 1.6
- fix: ensure library is not invoked in CLI mode

## v3.1.5

- fix: Add verification of number on type in amount field for refund

## v3.1.4

- fix: Return for refunded payments

## v3.1.3

- feat: Remove merchant tag for in-page on Prestashop
- fix: CSS of Prestashop Switch button on Back-office setting

## v3.1.2

- hotfix: Select payment In-Page without open the modal and try to pay with other payment method
- fix: Add loader payment button on Prestashop 1.6 before loading In-Page

## v3.1.1

- hotfix: Pay now is broken if In-Page is disabled

## v3.1.0

- feat: In-Page: Now we use the setForm in payment option and refactored to vanilla Javascript (we don't use Jquery anymore)

## v3.0.3

- fix: Change event system for In-Page on Prestashop 1.7+
- fix: Issue modal In-Page on Prestashop before 16.0.12
- fix: Clear the partial refund amount after a partial refund
- fix: Issue error when merchant not allow to create payments

## v3.0.2

- fix: issue modal In-Page with jQuery noConflict

## v3.0.1

- fix: Compatibility from PHP5.6

## v3.0.0

- feat: In-Page payment
- feat: Improve purchaseAmount verification
- fix: SEPA payment
- fix: Change HTTP return code for mismatch for IPN

## v2.13.0

- Pay now with Alma
- Fix : attach Alma hooks depending on the Prestashop version

## v2.12.0

- Refund process clarified for order/payment mismatch error
- Add encrypted API_KEY
- Gather cart data for credit payments
- Fix widget display if quantity is 0
- Fix error in Hook Controller

## v2.11.0

- Unification of the widget hook per product page and for Prestashop 1.6

## v2.10.0

- Optimise your performance with insights on share of checkout
- Alma module is now compatible with Prestashop 8
- Updated translations
- Fix installing our module on PrestaShop 1.6
- Fix refund status for non-Alma payments
- Fix payment error in Prestashop 1.5.3.1
- Fix payment error for when shippingInfo is an array

## v2.9.0

- Enriched data for risk algorithm improvements

## v2.8.0

- New Alma branding 2022
- Add logo D+45

## v2.7.2

- Fix widget style in classic theme Prestashop 1.7
- Fix translation on the widget

## v2.7.1

- Fix execution StateHookController even if called from the Prestashop Webservice

## v2.7.0

- Add Portuguese language
- Add B2B compatibility
- Fix refund customer fees
- Fix hidden payment button if payment is not eligible in PS 1.6
- Fix page payment return if you uncheck validation order

## v2.6.3

- Fixed an error at the state change at the command

## v2.6.2

- Fixed widget in version 2.8.0

## v2.6.1

- Fixed at the installation of the module if you had php 5.6
- Fixed if you were not using the Class intl php on your server
- Fixed the disable of the payment option if not eligible

## v2.6.0

- Now you can pay upon trigger with Alma in Prestashop !
- You can differentiate the Alma title and description texts on Prestashop checkout between payment p>4 and p<4
- Our installment dates formats are now adapted according to the language
- You can now override our functions located in the lib folder for better customization based on the native Prestashop override
- Fixed warning messages after installing our module in debug mode
- Fixed display fees in installment for p4x
- Fixed amount fees with customer interest in confirmation page displayed after payment
- Fixed CSS switch so as not to impact the other modules
- Fixed error when update the module

## v2.5.3

- Fix config form block hidden by default in other module
- Fix order page is broken if show orders Test in Live mode
- Fix refund in order page from Prestashop 1.6 and 1.5

## v2.5.2

- Fix an error if you change order status with refund option enabled
- Fix no show widget if controller return php_self false
- Fix warning in return excluded categories

## v2.5.1

- Fix order page if is not alma payment

## v2.5.0

- Add more information on Prestashop features
- Replacement of checkbox by toggle button
- Add data order_id in custom_data
- Fix list of excluded categories pagination of more than 50 items.
- Fix if country returns false for eligibility endpoint
- Fix order page with refund if payment_id is empty because you use webservice
- Fix on bug through the 2.0.0 update file
- Fix if quote not escape in template page for display widget

## v2.4.1

- Fixes escape validator Prestashop
- Fixes function upgrade v2.3.0

## v2.4.0

- New interface for alma refunds directly in the order page
- Payment confirmation page is now with Alma payment information
- New design and internationalization of Alma Widget
- Fixes custom fields if you add new language

## v2.3.2

- Fix empty custom fields if language is disabled

## v2.3.1

- Alma module is now available in Italian and German

## v2.3.0

- Alma module is now available in Spanish, English, and Dutch
- Add locale datas to send for eligibility and payment api
- Add editable custom fields for each language active in store

## v2.2.0

- Now you can hide the message of product page or cart page if it contains an excluded product
- Fix Narrow no-break space in EN payment page
- Show badge if product price equal limit of eligibility and badge is hide
- Update all payment logo PnX and BNPL

## v2.1.0

- P10X is now available with Alma !
- Add interest info P10X
- Fix P10X legal informations
- Fix error on link breadcrumb alma module
- Fix error message min/max amount BNPL
- Fix error when upgrade module Prestashop 1.6.0.X and 1.5.X

## v2.0.0

- Adds Buy Now Pay Later with Alma
- Support for 10-installment plans
- Adds custom position for Alma badge in product page
- Adds possibility to hide Alma badge if product is non eligible
- Replace eligibility messages in cart by Alma badge
- Adds custom position for Alma badge in cart
- Adds possibility to hide Alma badge if cart is non eligible
- Makes some css/js optimizations in front
- Fixes some minor bugs in Alma configuration page
- Fixes some bugs in smarty templates
- Fixes delete Alma badge values in database when uninstall Alma module
- Removes useless files

## v1.4.4

- Fixes bug preventing orders status to be correctly handled in some situations

## v1.4.3

- Adds compatibility for PrestaShop 1.5.3.1+
- Fixes some missing French translations
- Fixes support for multi-carrier in shipping info data
- Fixes bug in module's config that would prevent saving configuration when changing min/max amounts of fee plans
- Makes sure Alma is activated for all carriers upon module installation
- Fixes Alma badge not showing up on product pages in some older PrestaShop instances
- Fixes Alma badge loading when used in conjunction with PrestaShop's JavaScript compaction feature
- Fixes bug with Alma badge when displayed product doesn't have a selectable wanted quantity
- Makes category exclusion work with secondary categories
- Prevents crash from `psAdmin` cookie being saved automatically by PrestaShop when it is destroyed
- Switches Alma badge script/css URLs to jsDelivr

## v1.4.2

- Register product price hooks on upgrade so that Alma badges are visible by default on product pages after upgrading
- Update UNPKG URLs to use the unpkg.com domain instead of unpkg.io one, which is apparently a test domain name
- Fixes the "show product eligibility" setting not being properly deactivated when unchecked
- Revert use of `use` syntax in `alma.php` as it makes PrestaShop fail on module code evaluation

## v1.4.1

- Fix paths case to prevent errors on case-sensitive file systems

## v1.4.0

- All logos have been updated to our latest branding
- Adds an "Excluded categories" custom tab/page to let merchants deactivate Alma for categories of
  products that are not compatible with our legal terms. The exclusion configuration is accessible via a new Tab in the
  PrestaShop backoffice.
- Display a detailed payment plan for the selected Alma payment method at checkout.
- Display a "badge" on product pages, which shows customers whether they can pay products with Alma and, if that's the
  case, what the payment plan would be.
- Correctly syncs fee plans display and information with Alma's API data
- Temporarily removes the sending of shipping information as it causes a bug with orders with multiple carriers.
- Fixes the appearance of config buttons in PrestaShop 1.5.
- Fixes a bug in the IPN processing code that caused an exception to be thrown when the order had already been
  processed.
- Fixes issues with eligibility messages display when there are multiple activated fee plans
- By default, display the module's own confirmation page template on PrestaShop 1.5 & 1.6 (only for new installations)
- Added some code quality tooling to the repository

## v1.3.1

- Fixes an issue that could prevent all payment options to show in PrestaShop 1.7

## v1.3.0

- Compatibility with the Advance EU Compliance module (PrestaShop 1.6)
- Round prices using PrestaShop's internal method, to comply with merchant's configured preferences
- Include cart contents & shipping information in payment data to improve customer UX & fraud detection

## v1.2.7

- Dependencies update
- Send order reference to Alma upon payment confirmation, to make it easier for merchants to
  associate Alma payments with actual orders in their PrestaShop orders

## v1.2.6

- Dependencies update
- Improves paid amount comparison in payment validation to limit false positives

## v1.2.5

- Dependencies update (to include bug fix in Alma PHP Client)

## v1.2.4

- Fixes a bug that could prevent Alma from working on PrestaShop installed in a subdirectory of the main domain

## v1.2.3

- New attempt at overcoming float rounding issues on payment validation
- Improves onboarding UX: more guidance for the merchant, and less constraints on API keys requirements

## v1.2.2

- Adds hybrid PnX from 1.5.6.2 to 1.6.x
- Fixes eligibility message on cart not being in sync with configured hybrid PnX amount bounds

## v1.2.1

- Fixes boundary checks on installment plans that triggered errors on disabled plans
- Fixes p3x payment option remaining enabled even when unchecked in the module configuration

## v1.2.0

- Adds refund capability with either PrestaShop's refund feature & order state change to "refund" state (configurable)
- Applies security suggestions from PrestaShop's security audit
- Updates dependencies (alma-php-client)
- Changes default values for some configurable messages

## v1.1.1

- Fixes usage of `empty` that can cause issues in older versions of PHP

## v1.1.0

- Adds support for multiple installments plans (2-, 3- and 4-installment plans) on PrestaShop 1.7+
- Adds order_total as a template variable for displayPayment
- Various fixes to comply with PSR1/PSR2 and PrestaShop validator
- Preparation for marketplace validation & release

## v1.0.1

Let's start following semver.

- Adds User-Agent string containing the module's version, PrestaShop version, PHP client and PHP versions, to all
  requests going to Alma's API.

## v1.0.0

This version evolved for a while without any version bump 🤷‍♂️
Features in the latest push to this release:

- Compatible from PrestaShop 1.5.6.2 to 1.7.x
- Module can be configured in Test and Live mode; Test mode only shows Alma's payment method to visitors who are also
  logged in to the shop's backoffice
- A message displays below the cart to indicate whether the purchase is eligible to monthly installments
- The module adds a payment method/payment option to the checkout, which redirects the user to Alma's payment page.
  If everything goes right (i.e. Customer doesn't cancel, pays the right amount, ... ), an order is created and validated
  upon customer return.
