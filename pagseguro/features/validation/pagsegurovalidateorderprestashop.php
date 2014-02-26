<?php

/*
 * 2007-2013 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2013 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

include_once dirname(__FILE__).'/../../../../init.php';
include_once dirname(__FILE__) . '/../../../../config/config.inc.php';
include_once dirname(__FILE__) . '/../../pagseguro.php';
include_once dirname(__FILE__) . '/../../backward_compatibility/backward.php';
include_once dirname(__FILE__) . '/../../features/util/converterorderforpaymentrequest.php';

class PagSeguroValidateOrderPrestashop
{

    private $context;

    private $converter;

    private $module;

    public function __construct($module)
    {
        $this->module = $module;
        $this->context = Context::getContext();
        $this->converter = new ConverterOrderForPaymentRequest($module);
    }

    public function validate()
    {
        try {
            $this->verifyPaymentOptionAvailability();
            $this->validateCart();
            $this->converter->convertToRequestData();
            $this->converter->setAdditionalRequest($this->validateOrder());
        } catch (PagSeguroServiceException $e) {
            throw $e;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function request($isLightBox)
    {
        try {
            return $this->converter->request($isLightBox);
        } catch (PagSeguroServiceException $e) {
            throw $e;
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function verifyPaymentOptionAvailability()
    {
        
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'pagseguro') {
                $authorized = true;
                break;
            }
        }
        
        if (! $authorized) {
            die($this->module->l('Este método de pagamento não está disponível', 'validation'));
        }
    }

    private function validateCart()
    {
        if ($this->context->cart->id_customer == 0 || $this->context->cart->id_address_delivery == 0 ||
             $this->context->cart->id_address_invoice == 0 || ! $this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }
    }

    private function validateOrder()
    {
        $customer = new Customer($this->context->cart->id_customer);
        
        if (! Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }
        
        $this->module->validateOrder(
            (int) $this->context->cart->id,
            Configuration::get('PS_OS_PAGSEGURO'),
            (float) $this->context->cart->getOrderTotal(true, Cart::BOTH),
            $this->module->displayName,
            null,
            null,
            (int) $this->context->currency->id,
            false,
            $customer->secure_key
        );
        
        return array(
            'id_cart' => (int) $this->context->cart->id,
            'id_module' => $this->module->id,
            'id_order' =>$this->module->currentOrder,
            'key' => $customer->secure_key
        );
    }
}
