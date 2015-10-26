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
class plgHikashoppaymentMoneybookers extends hikashopPaymentPlugin
{
	var $accepted_currencies = array(
		'EUR','USD','GBP','HKD','SGD','JPY','CAD','AUD','CHF','DKK',
		'SEK','NOK','ILS','MYR','NZD','TRY','AED','MAD','QAR','SAR',
		'TWD','THB','CZK','HUF','SKK','EEK','BGN','PLN','ISK','INR',
		'LVL','KRW','ZAR','RON','HRK','LTL','JOD','OMR','RSD','TND',
	);
	var $multiple = true;
	var $name = 'moneybookers';
	var $debugData = array();
	var $pluginConfig = array(
		'url' => array('URL', 'input'),
		'email' => array('HIKA_EMAIL', 'input'),
		'merchant_id' => array('MONEYBOOKERS_MERCHANT_ID', 'input'),
		'secret_word' => array('MONEYBOOKERS_SECRET_WORD', 'input'),
		'notification' => array('ALLOW_NOTIFICATIONS_FROM_X', 'boolean','0'),
		'debug' => array('DEBUG', 'boolean','0'),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'logo_url' => array('LOGO', 'input'),
		'hide_login' => array('Hide login', 'boolean','0'),
		'ips' => array('IPS', 'textarea'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'pending_status' => array('PENDING_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		parent::onAfterOrderConfirm($order,$methods,$method_id);

		$lang = &JFactory::getLanguage();
		$locale=strtoupper(substr($lang->get('tag'),0,2));


		$price = round($order->cart->full_total->prices[0]->price_value_with_tax,(int)$this->currency->currency_locale['int_frac_digits']);
		if(strpos($price,'.')){
			$price =rtrim(rtrim($price, '0'), '.');
		}
		$vars = array(
		"currency" => $this->currency->currency_code,
		"amount" => $price,
		);

		$vars["status_url"] = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=moneybookers&tmpl=component&lang='.strtolower($locale).$this->url_itemid;
		$vars["transaction_id"] = $order->order_id;
		$vars["pay_from_email"]=$this->user->user_email;
		$vars["pay_to_email"]=$this->payment_params->email;
		$vars["recipient_description"] = $this->app->getCfg( 'sitename' );

		if(!in_array($locale,array('EN', 'DE', 'ES', 'FR', 'IT', 'PL', 'GR', 'RO', 'RU', 'TR', 'CN', 'CZ', 'NL', 'DA', 'SV', 'FI'))){
			$locale = 'EN';
		}
		$vars["language"]=$locale;

		$vars["return_url"]=HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$this->url_itemid;
		$vars["return_url_text"]=JText::_('RETURN_TO_THE_STORE');
		$cancel_url =  HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id.$this->url_itemid;
		$vars["cancel_url"]=$cancel_url;

		$this->app =& JFactory::getApplication();
		$cart = hikashop_get('class.cart');
		$address=$this->app->getUserState( HIKASHOP_COMPONENT.'.billing_address');
		$type = 'billing';
		if(empty($address)){
			$address=$this->app->getUserState( HIKASHOP_COMPONENT.'.shipping_address');
			if(!empty($address)){
				$type='shipping';
			}
		}
		if(!empty($address)){
			$cart->loadAddress($order->cart,$address,'object',$type);
			$address_type = $type.'_address';
			$vars["title"]=substr(@$order->cart->$address_type->address_title,0,3);
			$vars["firstname"]=substr(@$order->cart->$address_type->address_firstname,0,20);
			$vars["lastname"]=substr(@$order->cart->$address_type->address_lastname,0,50);
			$address1 = '';
			$address2 = '';
			if(!empty($order->cart->$address_type->address_street)){
				if(strlen($order->cart->$address_type->address_street)>100){
					$address1 = substr($order->cart->$address_type->address_street,0,100);
					$address2 = substr($order->cart->$address_type->address_street,100,200);
				}else{
					$address1 = $order->cart->$address_type->address_street;
				}
			}
			$vars["address"]=$address1;
			$vars["address2"]=$address2;
			$vars["country"]=@$order->cart->$address_type->address_country->zone_code_3;
			$vars["postal_code"]=substr(@$order->cart->$address_type->address_post_code,0,9);
			$vars["city"]=substr(@$order->cart->$address_type->address_city,0,50);
			$vars["state"]=substr(@$order->cart->$address_type->address_state->zone_name_english,0,50);
			$vars["phone_number"]=substr(@$order->cart->$address_type->address_telephone,0,20);
		}
		if(!empty($method->payment_params->logo_url)){
			$vars['logo_url']=$method->payment_params->logo_url;
		}
		if(!isset($method->payment_params->hide_login)){
			$method->payment_params->hide_login=1;
		}
		if($method->payment_params->hide_login){
			$vars["hide_login"]='1';
		}
		$vars["platform"]='30071142';
		$vars["detail1_description"]=JText::_('ORDER_NUMBER').' :';
		$vars["detail1_text"]=$order->order_number;

		$this->vars = $vars;

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

		$order_id = (int)@$vars['transaction_id'];
		$dbOrder = $this->getOrder($order_id);

		if(!empty($dbOrder)){
			$order->old_status->order_status=$dbOrder->order_status;
			$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order_id;
			$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
			$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
		}else{
			echo "Could not load any order for your notification ".$order_id;
			return false;
		}

		$this->loadPaymentParams($dbOrder);
		if($this->payment_params->debug){
			echo print_r($dbOrder,true)."\n\n\n";
		}
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);

		if(!$this->payment_params->notification){
			return false;
		}

		$vars['calculated_md5sig']=strtoupper(md5(@$this->payment_params->merchant_id.@$vars['transaction_id'].strtoupper(md5($this->payment_params->secret_word)).@$vars['mb_amount'].@$vars['mb_currency'].@$vars['status']));

		if($this->payment_params->debug){
			echo print_r($vars,true)."\n\n\n";
		}

		$mailer = JFactory::getMailer();
		$config =& hikashop_config();
		$sender = array(
				$config->get('from_email'),
				$config->get('from_name')
		);
		$mailer->setSender($sender);
		$mailer->addRecipient(explode(',',$config->get('payment_notification_email')));

		if(!empty($this->payment_params->ips)){
			$ip = hikashop_getIP();
			$ips = str_replace(array('.','*',','),array('\.','[0-9]+','|'),$this->payment_params->ips);
			if(!preg_match('#('.implode('|',$ips).')#',$ip)){
				$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Moneybookers').' '.JText::sprintf('IP_NOT_VALID',$dbOrder->order_number));
				$body = str_replace('<br/>',"\r\n",JText::sprintf('NOTIFICATION_REFUSED_FROM_IP','Moneybookers',$ip,implode("\r\n",$this->payment_params->ips)))."\r\n\r\n".$order_text;
				$mailer->setBody($body);
				$mailer->Send();
				JError::raiseError( 403, JText::_( 'Access Forbidden' ));
				return false;
			}
		}
		if (@$vars['md5sig']!=$vars['calculated_md5sig']) {
			$mailer->setSubject(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Moneybookers').'invalid response');
			$body = JText::sprintf("Hello,\r\n A Moneybookers notification was refused because the response from the Moneybookers server was invalid")."\r\n\r\n".$order_text;
			$mailer->setBody($body);
			$mailer->Send();
			if($this->payment_params->debug){
				echo 'invalid response'."\n\n\n";
			}
			return false;
		}
		$vars['status']=(int)@$vars['status'];

		if(!in_array($vars['status'],array(0,2))) {

			if($vars['status']==-1){
				$vars['payment_status']='Cancelled';
			}elseif($vars['status']==-2){
				$vars['payment_status']='Failed';
			}elseif($vars['status']==-3){
				$vars['payment_status']='Chargeback';
			}else{
				$vars['payment_status']='Unknown';
			}
			$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Moneybookers',$vars['payment_status'])).' '.JText::_('STATUS_NOT_CHANGED')."\r\n\r\n".$order_text;
		 	$mailer->setSubject(JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Moneybookers',$vars['payment_status'],$dbOrder->order_number));
			$mailer->setBody($body);
			$mailer->Send();
			if($element->payment_params->debug){
				echo 'payment with code '.@$vars['status'].(!empty($vars['failed_reason_code'])?' : '.@$vars['failed_reason_code']:'')."\n\n\n";
			}
			return false;
		 }

		$this->modifyOrder($order_id, $vars['status'],true,true);

		return true;
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='Moneybookers';
		$element->payment_description='You can pay by credit card, bank transfer, check, etc using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';

		$element->payment_params->ips=array('91.208.28.*','72.52.0.65','83.220.158.*','91.208.28.*','213.129.65.223','213.129.65.21','91.208.28.*');
		$element->payment_params->url='https://www.moneybookers.com/app/payment.pl';
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
	}

}
