<?php
/**
 * 2018-2023 Alma SAS.
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
 * @copyright 2018-2023 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Alma\PrestaShop\Model;

use Alma\PrestaShop\Helpers\CarrierHelper;
use Alma\PrestaShop\Helpers\PriceHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ShippingData
{
    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var CarrierHelper
     */
    protected $carrierHelper;

    /**
     * @param PriceHelper $priceHelper
     * @param CarrierHelper $carrierHelper
     *
     * @codeCoverageIgnore
     */
    public function __construct($priceHelper, $carrierHelper)
    {
        $this->priceHelper = $priceHelper;
        $this->carrierHelper = $carrierHelper;
    }

    /**
     * @param \Cart $cart
     *
     * @return array|null
     */
    public function shippingInfo($cart)
    {
        $addressId = $cart->id_address_delivery;

        $deliveryOption = $cart->getDeliveryOption(null, true);

        if ($deliveryOption === false) {
            $deliveryOption = $cart->getDeliveryOption();
        }

        // We don't have any shipping information for the shipping address
        if (
            $deliveryOption === false
            || !isset($deliveryOption[$addressId])
        ) {
            return null;
        }

        $deliveryOptionList = $cart->getDeliveryOptionList();
        $carriersListKey = $deliveryOption[$addressId];

        if (
            !isset($deliveryOptionList[$addressId])
            || !isset($deliveryOptionList[$addressId][$carriersListKey])
        ) {
            return null;
        }

        $carrierIdArray = $this->getCarrierIds($carriersListKey, $cart);

        return $this->buildShippingInfos(
            $carrierIdArray,
            $deliveryOptionList,
            $addressId,
            $carriersListKey,
            $cart->id_lang,
            $deliveryOption
        );
    }

    /**
     * @param $carriersListKey
     * @param $cart
     *
     * @return array
     */
    public function getCarrierIds($carriersListKey, $cart)
    {
        $carrierIdArray = [];

        if (!isset($cart->unique_carrier)) {
            $carrierIds = explode(',', $carriersListKey);
            foreach ($carrierIds as $id) {
                if (!empty($id)) {
                    $carrierIdArray[] = $id;
                }
            }

            return $carrierIdArray;
        }

        $carrierIdArray[] = $cart->id_carrier;

        return $carrierIdArray;
    }

    /**
     * @param $carrierIds
     * @param $deliveryOptionList
     * @param $addressId
     * @param $carriersListKey
     * @param $idLang
     * @param $deliveryOption
     *
     * @return array|array[]
     */
    public function buildShippingInfos($carrierIds, $deliveryOptionList, $addressId, $carriersListKey, $idLang, $deliveryOption)
    {
        $shippingInfo = ['selected_options' => []];
        foreach ($carrierIds as $carrierId) {
            $carrierInfo = $deliveryOptionList[$addressId][$carriersListKey]['carrier_list'][$carrierId];
            /** @var \Carrier $carrier */
            $carrier = $this->carrierHelper->createCarrier($carrierId, $idLang);
            if (!$carrier) {
                continue;
            }
            $shippingInfo['selected_options'][] = $this->shippingInfoData($carrier, $carrierInfo);
        }

        $knownOptions = $this->getKnownOptions($deliveryOptionList, $addressId, $idLang);

        $shippingInfo['available_options'] = array_values($knownOptions);

        if (count($deliveryOption) > 1) {
            $shippingInfo['is_multi_shipping'] = true;
            $shippingInfo['multi_shipping_info'] = [
                'addresses_selection' => $deliveryOption,
                'delivery_options' => $deliveryOptionList,
            ];
        }

        return $shippingInfo;
    }

    /**
     * @param $deliveryOptionList
     * @param $addressId
     * @param $idLang
     *
     * @return array
     */
    public function getKnownOptions($deliveryOptionList, $addressId, $idLang)
    {
        $knownOptions = [];
        foreach ($deliveryOptionList[$addressId] as $carriers) {
            foreach ($carriers['carrier_list'] as $id => $carrierOptionInfo) {
                $carrierOption = $this->carrierHelper->createCarrier($id, $idLang);
                if (!$carrierOption) {
                    continue;
                }

                $data = $this->shippingInfoData($carrierOption, $carrierOptionInfo);
                $knownOptions[md5(serialize($data))] = $data;
            }
        }

        return $knownOptions;
    }

    /**
     * @param \Carrier $carrier
     * @param array $carrierInfo
     *
     * @return array
     */
    public function shippingInfoData($carrier, $carrierInfo)
    {
        return [
            'amount' => $this->priceHelper->convertPriceToCents((float) $carrierInfo['price_with_tax']),
            'carrier' => $carrier->name,
            'title' => (is_array($carrier->delay)) ? implode(', ', $carrier->delay) : (string) $carrier->delay,
            'express_delivery' => null,
            'pickup_delivery' => null,
        ];
    }
}
