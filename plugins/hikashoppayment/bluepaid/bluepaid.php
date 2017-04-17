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
class plgHikashoppaymentBluepaid extends hikashopPaymentPlugin
{
	var $accepted_currencies = array(
		'EUR','CHF','USD','GBP','JPY','CAD','AUD'
	);
	var $multiple = true;
	var $name = 'bluepaid';
	var $debugData = array();


	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		$vars = array(
			"devise" => $this->currency->currency_code,
			"montant" => round($order->cart->full_total->prices[0]->price_value_with_tax,(int)$this->currency->currency_locale['int_frac_digits']),
		);

		$vars["email_client"]=$this->user->user_email;
		$vars["id_boutique"]=$this->payment_params->shop_id;

		$vars["langue"] = strtoupper($this->locale);
		if(!in_array($this->locale,array('EN', 'DE', 'ES', 'FR', 'IT', 'NL', 'PT')))
			$vars["langue"] = 'EN';

		if(!empty($order->cart->shipping_address->address_country->zone_code_3))
			$vars["pays_liv"]=@$order->cart->shipping_address->address_country->zone_code_3;
		else if(!empty($order->cart->billing_address->address_country->zone_code_3))
			$vars["pays_liv"]=@$order->cart->billing_address->address_country->zone_code_3;

		$vars["id_client"]=$order->order_user_id;
		$vars["divers"]=$order->order_id;

		$this->removeCart = true;

		$this->vars = $vars;

		$this->showPage('end');
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


		$order_id = (int)@$vars['divers'];
		$dbOrder = $this->getOrder($order_id);
		if(!empty($dbOrder)){
			$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order->order_id;
			$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
			$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
		}else{
			echo "Could not load any order for your notification ".@$vars['divers'];
			return false;
		}

		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);

		if($this->payment_params->debug){
			echo print_r($vars,true)."\n\n\n";
			echo print_r($dbOrder,true)."\n\n\n";
		}

		if(!empty($this->payment_params->ips)){
			$ip = hikashop_getIP();
			$ips = str_replace(array('.','*',','),array('\.','[0-9]+','|'),$this->payment_params->ips);
			if(!preg_match('#('.implode('|',$ips).')#',$ip)){
				$body = str_replace('<br/>',"\r\n",JText::sprintf('NOTIFICATION_REFUSED_FROM_IP','Bluepaid',$ip,implode("\r\n",$this->payment_params->ips)))."\r\n\r\n".$order_text;


				$email = new stdClass();
				$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Bluepaid').' '.JText::sprintf('IP_NOT_VALID',$dbOrder->order_number);
				$email->body = $body;

				$this->modifyOrder($order_id, $this->payment_params->invalid_status, false, $email);

				JError::raiseError( 403, JText::_( 'Access Forbidden' ));
				return false;
			}
		}

		if ($vars['secure_key']!=@$this->payment_params->secure_key) {
			$body = JText::sprintf("Hello,\r\n A Bluepaid notification was refused because the response from the Bluepaid server was invalid")."\r\n\r\n".$order_text;

			$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Bluepaid').'invalid response';
			$email->body = $body;

			$this->modifyOrder($order_id, $this->payment_params->invalid_status, false, $email);

			if($element->payment_params->debug){
				echo 'invalid response'."\n\n\n";
			}
			return false;
		}

		$vars['status'] = strtolower(@$vars['etat']);

		if(!in_array($vars['status'],array("attente","ok"))) {

			if($vars['status']=="annu"){
				$vars['payment_status']='Cancelled';
			}elseif($vars['status']=="ko"){
				$vars['payment_status']='Failed';
			}else{
				$vars['payment_status']='Unknown';
			}
			$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Bluepaid',$vars['payment_status'])).' '.JText::_('STATUS_NOT_CHANGED')."\r\n\r\n".$order_text;

			$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Bluepaid',$vars['payment_status'],$dbOrder->order_number);
			$email->body = $body;

			$this->modifyOrder($order_id, null, false, $email);

			if($element->payment_params->debug){
				echo 'payment with code '.@$vars['status'].(!empty($vars['failed_reason_code'])?' : '.@$vars['failed_reason_code']:'')."\n\n\n";
			}
			return false;
		 }

		$history = new stdClass();
		$history->notified = 0;
		$history->amount = @$vars['montant'].@$vars['devise'];
		$history->data = ob_get_clean();

	 	$price_check = round($dbOrder->order_full_price,(int)$this->currency->currency_locale['int_frac_digits']).$this->currency->currency_code;
	 	if($price_check != @$vars['montant'].@$vars['devise']){
	 		$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Bluepaid').JText::_('INVALID_AMOUNT'));
			$body = str_replace('<br/>',"\r\n",JText::sprintf('AMOUNT_RECEIVED_DIFFERENT_FROM_ORDER','Bluepaid',$order->history->amount,$price_check))."\r\n\r\n".$order_text;

			$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Bluepaid').JText::_('INVALID_AMOUNT');
			$email->body = $body;

			$this->modifyOrder($order_id, $this->payment_params->invalid_status, $history, $email);

	 		return false;
	 	}

	 	if($vars['status']=="ok"){
	 		$order_status = $this->payment_params->verified_status;
	 		$vars['payment_status']='Accepted';
	 	}else{
	 		$order_status = $this->payment_params->pending_status;
	 		$order_text ="Payment is pending\r\n\r\n".$order_text;
	 		$vars['payment_status']='Pending';
	 	}

	 	$config =& hikashop_config();
		if($config->get('order_confirmed_status','confirmed') == $order_status){
			$history->notified = 1;
		}

		$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Bluepaid',$vars['payment_status'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$statuses[$order->order_status])."\r\n\r\n".$order_text;

		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Bluepaid',$vars['payment_status'],$dbOrder->order_number);
		$email->body = $body;

		$this->modifyOrder($order_id, $order_status, $history, $email);


		return true;
	}

	function onPaymentConfiguration(&$element){
		$subtask = JRequest::getCmd('subtask','');
		if($subtask=='ips'){
			$ips = null;
			echo implode(',',$this->_getIPList($ips));
			exit;
		}else{
			parent::onPaymentConfiguration($element);

			$lang = &JFactory::getLanguage();
			$locale=strtoupper(substr($lang->get('tag'),0,2));
			$element->payment_params->status_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=bluepaid&tmpl=component&lang='.strtolower($locale);
		}
	}

	function onPaymentConfigurationSave(&$element){
		if(!empty($element->payment_params->ips)){
			$element->payment_params->ips=explode(',',$element->payment_params->ips);
		}
		return true;
	}

	function _getIPList(&$ipList){
		$ipList = array_merge(gethostbynamel('securepayment.bluepaid.com'),gethostbynamel('securepayment1.bluepaid.com'),gethostbynamel('securepayment2.bluepaid.com'),gethostbynamel('securepayment3.bluepaid.com'),gethostbynamel('securepayment4.bluepaid.com'),gethostbynamel('securepayment5.bluepaid.com'),gethostbynamel('securepayment6.bluepaid.com'));
		if(!empty($ipList)){
			$newList = array('193.33.47.34','193.33.47.35');
			foreach($ipList as $k => $ip){
				$ipParts = explode('.',$ip);
				if(!in_array($ip,$newList)){
					$newList[]=$ip;
				}
			}
			$ipList = $newList;
		}
		return $ipList;
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='Bluepaid';
		$element->payment_description='Vous pouvez payer par carte bleue avec ce système de paiement';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';

		$element->payment_params->notification=true;
		$list = null;
		$element->payment_params->ips=$this->_getIPList($list);
		$element->payment_params->url='https://www.bluepaid.com/in.php';
		$element->payment_params->secure_key=md5(time().rand());
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
	}

}
