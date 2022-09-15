<?php
/**
 * 2018-2022 Alma SAS
 *
 * THE MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    Alma SAS <contact@getalma.eu>
 * @copyright 2018-2022 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Utils;

use Alma\PrestaShop\Model\AddressData;
use Alma\PrestaShop\Model\CartData;
use Alma\PrestaShop\Model\CustomerData;
use Alma\PrestaShop\Model\ShippingData;
use Cart;
use Context;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class PaymentDataHelper.
 *
 * Payment Data
 */
class PaymentDataHelper
{
    public function __construct(
        Cart $cart,
        Context $context,
        array $feePlan
    ) {
        $customerData = new CustomerData($context, $cart);

        $this->cart = $cart;
        $this->context = $context;
        $this->feePlan = $feePlan;
        $this->customer = $customerData->get();
    }

    /**
     * Return data for payment
     *
     * @return array paymentData
     */
    public function paymentData()
    {
        $purchase_amout = CartData::getPurchaseAmount($this->cart);
        $addressData = new AddressData($this->cart);
        $customerHelper = new CustomerHelper($this->customer, $this->context, $this->cart);
        $shippingAddress = $addressData->getShippingAddress();
        $billingAddress = $addressData->getBillingAddress();

        $dataPayment = [
            'website_customer_details' => $customerHelper->getWebsiteDetails(),
            'payment' => [
                'installments_count' => $this->feePlan['installmentsCount'],
                'deferred_days' => $this->feePlan['deferredDays'],
                'deferred_months' => $this->feePlan['deferredMonths'],
                'purchase_amount' => CartData::getPurchaseAmountInCent($this->cart),
                'customer_cancel_url' => $this->context->link->getPageLink('order'),
                'return_url' => $this->context->link->getModuleLink('alma', 'validation'),
                'ipn_callback_url' => $this->context->link->getModuleLink('alma', 'ipn'),
                'shipping_address' => [
                    'line1' => $shippingAddress->address1,
                    'postal_code' => $shippingAddress->postcode,
                    'city' => $shippingAddress->city,
                    'country' => $addressData->getShippingCountry(),
                    'county_sublocality' => null,
                    'state_province' => $addressData->getShippingStateProvince(),
                ],
                'shipping_info' => ShippingData::shippingInfo($this->cart),
                'cart' => CartData::cartInfo($this->cart),
                'billing_address' => [
                    'line1' => $billingAddress->address1,
                    'postal_code' => $billingAddress->postcode,
                    'city' => $billingAddress->city,
                    'country' => $addressData->getBillingCountry(),
                    'county_sublocality' => null,
                    'state_province' => $addressData->getBillingStateProvince(),
                ],
                'custom_data' => [
                    'cart_id' => $this->cart->id,
                    'purchase_amount_new_conversion_func' => almaPriceToCents_str($purchase_amout),
                    'cart_totals' => $purchase_amout,
                    'cart_totals_high_precision' => number_format($purchase_amout, 16),
                ],
                'locale' => LocaleHelper::getLocale(),
            ],
            'customer' => $customerHelper->getData(),
        ];

        if (Settings::isDeferredTriggerLimitDays($this->feePlan)) {
            $dataPayment['payment']['deferred'] = 'trigger';
            $dataPayment['payment']['deferred_description'] = SettingsCustomFields::getDescriptionPaymentTriggerByLang($this->context->language->id);
        }

        return $dataPayment;
    }
}
