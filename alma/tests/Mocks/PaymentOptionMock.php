<?php

namespace PrestaShop\Module\Alma\Tests\Mocks;

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

final class PaymentOptionMock
{
    public static function paymentOption(): PaymentOption
    {
        $paymentOption = new PaymentOption();
        $paymentOption->setModuleName('alma');
        $paymentOption->setLogo(_PS_MODULE_DIR_ . 'alma/views/img/logos/p3x_logo.svg');
        $paymentOption->setAction('alma/payment');
        $paymentOption->setCallToActionText('Pay with Alma');
        $paymentOption->setInputs([
            'token' => [
                'name' => 'token',
                'type' => 'hidden',
                'value' => 'totoken',
            ],
        ]);
        return $paymentOption;
    }
}
