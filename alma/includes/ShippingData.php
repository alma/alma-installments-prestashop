<?php
/**
 * 2018-2020 Alma SAS
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
 * @copyright 2018-2020 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_ . 'alma/includes/AlmaLogger.php';
include_once _PS_MODULE_DIR_ . 'alma/includes/functions.php';

class ShippingData
{
    /**
     * @param Cart $cart
     * @return array|null
     */
    public static function shippingInfo($cart) {
        $addressId = $cart->id_address_delivery;
        $deliveryOption = $cart->getDeliveryOption(null, true);

        // We don't have any shipping information for the shipping address
        if ($deliveryOption === false || !isset($deliveryOption[$addressId])) {
            return null;
        }

        $deliveryOptionList = $cart->getDeliveryOptionList();
        $carriersListKey = $deliveryOption[$addressId];
        if (!isset($deliveryOptionList[$addressId]) || !isset($deliveryOptionList[$addressId][$carriersListKey])) {
            return null;
        }

        $carrierId = $cart->id_carrier;
        if (!$carrierId) {
            $carrierId = key($deliveryOptionList[$addressId][$carriersListKey]);
        }

        $carrierInfo = $deliveryOptionList[$addressId][$carriersListKey]['carrier_list'][$carrierId];
        /** @var Carrier $carrier */
        $carrier = new Carrier($carrierId, $cart->id_lang);
        if (!$carrier) {
            return null;
        }

        $shippingInfo = self::shippingInfoData($carrier, $carrierInfo);

        $shippingInfo["available_options"] = array();
        $knownOptions = array();
        foreach($deliveryOptionList[$addressId] as $key => $carriers) {
            foreach ($carriers['carrier_list'] as $id => $carrierOptionInfo) {
                $carrierOption = new Carrier($id, $cart->id_lang);
                if (!$carrierOption) {
                    continue;
                }

                $data = self::shippingInfoData($carrierOption, $carrierOptionInfo);
                $knownOptions[md5(serialize($data))] = $data;
            }
        }

        $shippingInfo["available_options"] = array_values($knownOptions);

        if (count($deliveryOption) > 1) {
            $shippingInfo["is_multi_shipping"] = true;
            $shippingInfo["multi_shipping_info"] = array(
                "addresses_selection" => $deliveryOption,
                "delivery_options" => $deliveryOptionList
            );
        }

        return $shippingInfo;
    }

    /**
     *
     * @param $carrier
     * @param $carrierInfo
     * @return array
     */
    private static function shippingInfoData($carrier, $carrierInfo) {
        return array(
            "amount" => almaPriceToCents((float)$carrierInfo["price_with_tax"]),
            "carrier" => $carrier->name,
            "title" => $carrier->delay,
            "express_delivery" => self::isExpressShipping($carrierInfo),
            "pickup_delivery" => self::isPickupShipping($carrierInfo),
        );
    }

    private static function isExpressShipping($carrierInfo) {
        return null;
    }

    private static function isPickupShipping($carrierInfo) {
        return null;
    }
}
