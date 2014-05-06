<?php

include_once dirname(__FILE__).'/../../../../config/config.inc.php';
include_once dirname(__FILE__).'/../../../../init.php';
include_once dirname(__FILE__).'/../../pagseguro.php';

foreach (Language::getLanguages(false) as $language) {
    if (strcmp($language["iso_code"], 'br') == 0) {
        $idLang = $language["id_lang"];    
    }
}

$ajaxRequest = Tools::getValue('action') ;
$pagseguro = new PagSeguro();

switch ($ajaxRequest) {

    case 'singleemail':

        $recoveryCode = Tools::getValue('recovery');
        $idCustomer = Tools::getValue('customer');
        
        $customer = new Customer((int)($idCustomer));
        
        $orderMessage = OrderMessage::getOrderMessages($idLang);
        $template = '';
        $message = '';
        foreach ($orderMessage as $key => $value) {
            if(strcmp($value["id_order_message"], Configuration::get('PAGSEGURO_MESSAGE_ORDER_ID')) == 0 ) {
               $template = $value['name'];
               $message = $value['message'];        
            }
        }

        $params = array(
            '{message}' =>  $message,
            '{link}' => '<a href="https://pagseguro.uol.com.br/checkout/v2/resume.html?r="'.$recoveryCode.'" target="_blank"> click aqui para continuar sua compra </a>'
        );

        $isSend = @Mail::Send($idLang, 'recovery_cart', $template, $params, $customer->email,
        	$customer->firstname.' '.$customer->lastname, NULL, NULL, NULL, NULL, _PS_ROOT_DIR_ . '/modules/pagseguro/mails/', true);
        
        if ($isSend) {
            echo '<div class="module_confirmation conf confirm" '.Util::getWidthVersion(_PS_VERSION_).' ">'
                . $pagseguro->l('Email enviado com sucesso') . '</div>';
        }
        else {
            echo '<div class="module_error alert error" '.Util::getWidthVersion(_PS_VERSION_).' ">'
                . $pagseguro->l('Falha ao enviar email') . '</div>';
        }
        break;
    case 'multiemails':
    
        $testeteste = Tools::getValue('send_emails');
        
        foreach ($testeteste as $key => $value) {
            parse_str($value);
            
            $customer = new Customer((int)($customer));
            
            $orderMessage = OrderMessage::getOrderMessages($idLang);
            $template = '';
            $message = '';
            foreach ($orderMessage as $key => $value) {
                if(strcmp($value["id_order_message"], Configuration::get('PAGSEGURO_MESSAGE_ORDER_ID')) == 0 ) {
                   $template = $value['name'];
                   $message = $value['message'];        
                }
            }
    
            $params = array(
                '{message}' =>  $message,
                '{link}' => '<a href="https://pagseguro.uol.com.br/checkout/v2/resume.html?r="'.$recovery.'" target="_blank"> click aqui para continuar sua compra </a>'
            );
    
            $isSend = @Mail::Send($idLang, 'recovery_cart', $template, $params, $customer->email,
            	$customer->firstname.' '.$customer->lastname, NULL, NULL, NULL, NULL, _PS_ROOT_DIR_ . '/modules/pagseguro/mails/', true);
                
            if (!$isSend) {
                echo '<div class="module_error alert error" '.Util::getWidthVersion(_PS_VERSION_).' ">'
                    . $pagseguro->l('Falha ao enviar email') . '</div>';
                die();
            }    
        }

        echo '<div class="module_confirmation conf confirm" '.Util::getWidthVersion(_PS_VERSION_).' ">'
            . $pagseguro->l('Emails enviados com sucesso') . '</div>';
        break;
    case 'searchtable':
        echo $pagseguro->getAbandonedTabHtml();
        break;
}
