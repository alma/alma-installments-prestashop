<?php

namespace Alma\PrestaShop\Utils;

/**
 * Class CartEligibilityAdminFormBuilder
 *
 * @package Alma\PrestaShop\Utils
 */
class CartEligibilityAdminFormBuilder extends AbstractAlmaAdminFormBuilder
{
    const ALMA_SHOW_ELIGIBILITY_MESSAGE = 'ALMA_SHOW_ELIGIBILITY_MESSAGE';

    protected function configForm()
    {
        // TODO: Implement configForm() method.
        return [
            $this->inputSwitchForm(
                self::ALMA_SHOW_ELIGIBILITY_MESSAGE,
                $this->module->l('Show cart eligibility', 'GetContentHookController'),
                $this->module->l(
                    'Displays a badge with eligible Alma plans with installments details',
                    'GetContentHookController'
                ),
                $this->module->l('Display the cart\'s eligibility.', 'GetContentHookController')
            ),
            $this->inputSwitchForm(
                'ALMA_CART_WDGT_NOT_ELGBL',
                $this->module->l('Display badge', 'GetContentHookController'),
                $this->module->l(
                    'Displays a badge when cart amount is too high or tow low',
                    'GetContentHookController'
                ),
                $this->module->l('Display badge when the cart is not eligible.', 'GetContentHookController')
            ),
            $this->inputRadioForm(
                'ALMA_CART_WIDGET_POSITION_CUSTOM',
                $this->module->l('Badge position', 'GetContentHookController'),
                $this->module->l('Display badge after cart (by default)', 'GetContentHookController'),
                $this->module->l('Display badge on custom css selector', 'GetContentHookController')
            ),
            $this->inputTextForm(
                'ALMA_CART_WDGT_POS_SELECTOR',
                $this->module->l('Display badge on custom css selector', 'GetContentHookController'),
                $this->module->l(
                    '%1$sAdvanced%2$s [Optional] Query selector for our scripts to display the badge on cart page',
                    'GetContentHookController'
                ),
                $this->module->l('E.g. #id, .class, ...', 'GetContentHookController')
            ),
        ];
    }

    protected function getTitle()
    {
        return $this->module->l('Cart eligibility message', 'GetContentHookController');
    }
}
