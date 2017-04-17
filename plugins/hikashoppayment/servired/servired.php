<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
defined('_JEXEC') or die('Restricted access');

class plgHikashoppaymentservired extends hikashopPaymentPlugin {
	var $multiple = true;
	var $name = 'servired';
	var $pluginConfig = array(
		'url' => array('Servired URL', 'input'),
		'merchantId' => array('Shop Id', 'input'),
		'merchantName' => array('Shop Name', 'input'),
		'terminalId' => array('Terminal ID', 'input'),
		'encriptionKey' => array('Encryption Key', 'input'),
		'payment_methods' => array('Payment methods<br/>(you can add the letters T R D O C, one or several of them, based on which payment method you want to display. Leave empty for all of them)', 'big-textarea'),
		'debug' => array('DEBUG', 'boolean','0'),
		'return_url' => array('RETURN_URL', 'input'),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'pending_status' => array('PENDING_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);
	var $accepted_currencies = array('EUR','USD');
	var $debugData = array();

	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		parent::onAfterOrderConfirm($order,$methods,$method_id);

		$this->methods = $methods;
		$this->method_id = $method_id;
		$this->amount_total=round($order->cart->full_total->prices[0]->price_value_with_tax,2)*100;
		$this->id_pedido=$order->order_id;

		return $this->showPage('end');
	}

	function onPaymentNotification(&$statuses){

		$vars = array();
		$data = array();

		$filter = JFilterInput::getInstance();

		foreach($_POST as $key => $value){
			$key = $filter->clean($key);
			$value = JRequest::getString($key);
			$vars[$key]=$value;
		}

		$order_id = (int)@$vars['Ds_Order'];
		$dbOrder = $this->getOrder($order_id);
		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);
		if($this->payment_params->debug){
			echo print_r($dbOrder,true)."\n\n\n";
			echo print_r($vars,true)."\n\n\n";
		}

		if(empty($dbOrder)){
			echo "Could not load any order for your notification ".@$vars['Ds_Order'];
			return false;
		}

		$Sig_amount=$vars['Ds_Amount'];
		$Sig_order=$vars['Ds_Order'];
		$Sig_code=$this->payment_params->merchantId;
		$Sig_currency='978';
		$Sig_transactionType='0';
		$Sig_response=$vars['Ds_Response'];
		$Sig_clave=$this->payment_params->encriptionKey;
		$Sig_message = $Sig_amount.$Sig_order.$Sig_code.$Sig_currency.$Sig_response.$Sig_clave;
		$signature = strtoupper(sha1($Sig_message));
		$Ds_Signature=$vars['Ds_Signature'];

		if($Ds_Signature == $signature){
			$DS1_RESPONSE=(int)@$vars['Ds_Response'];
			if ( $DS1_RESPONSE>=0 && $DS1_RESPONSE<100) {

				$history = new stdClass();
				$history->notified=0;
				$history->data = ob_get_clean();
				$history->amount=@$vars['Ds_Amount'].$this->currency->currency_code;

				$this->modifyOrder($order_id, $this->payment_params->verified_status, $history, true);


				return true;
			}
			else //Failed operation received form pasarela
			{
				$this->modifyOrder($order_id, $this->payment_params->invalid_status, false, false);
				return false;
			}
		}
		else
		{
		 	return false;
		}
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='Servired';
		$element->payment_description='You can pay by credit card or paypal using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';

		$element->payment_params->notification=true;
		$element->payment_params->url='https://www.servired.com/in.php';
		$element->payment_params->secure_key='';
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
	}
}
