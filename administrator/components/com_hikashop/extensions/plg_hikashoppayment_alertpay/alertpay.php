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
class plgHikashoppaymentAlertpay extends hikashopPaymentPlugin
{
	var $accepted_currencies = array(
		'AUD','BGN','CAD','CHF','CZK','DKK','EEK','EUR','GBP','HKD',
		'HUF','INR','LTL','MYR','MKD','NOK','NZD','PLN','RON','SEK',
		'SGD','USD','ZAR',
	);
	var $multiple = true;
	var $name = 'alertpay';
	var $debugData = array();
	var $pluginConfig = array(
		'email' => array('HIKA_EMAIL', 'input'),
		'security_code' => array('IPN_SECURITY_CODE', 'input'),
		'status_url' => array('STATUS_URL','html',''),
		'address_type' => array('PAYPAL_ADDRESS_TYPE', 'address'),
		'notification' => array('ALLOW_NOTIFICATIONS_FROM_X', 'boolean','0'),
		'debug' => array('DEBUG', 'boolean','0'),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function  __construct(&$subject, $config){
		$lang = JFactory::getLanguage();
		$locale=strtoupper(substr($lang->get('tag'),0,2));
		$this->pluginConfig['notification'][0] =  JText::sprintf('ALLOW_NOTIFICATIONS_FROM_X','Payza');
		$this->pluginConfig['status_url'][0] = JText::sprintf('STATUS_URL','Payza');
		$this->pluginConfig['status_url'][2] = htmlentities(HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=alertpay&tmpl=component&lang='.strtolower($locale));
		return parent::__construct($subject, $config);
	}


	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		parent::onAfterOrderConfirm($order,$methods,$method_id);

		$return_url =  HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$this->url_itemid;

		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id.$this->url_itemid;

		$vars = array(
		"ap_purchasetype" => "item",
		"ap_merchant" => $this->payment_params->email,
		"apc_1" => $order->order_id,
		"ap_itemname" => $order->order_number,
		"ap_currency" => $this->currency->currency_code,
		"ap_returnurl" => $return_url,
		"ap_cancelurl" => $cancel_url,
		"ap_amount" => round($order->cart->full_total->prices[0]->price_value_with_tax,(int)$this->currency->currency_locale['int_frac_digits']),
		);

		if(!empty($this->payment_params->address_type)){
			$address_type = $this->payment_params->address_type.'_address';
			$this->app =& JFactory::getApplication();
			$address=$this->app->getUserState( HIKASHOP_COMPONENT.'.'.$address_type);
			if(!empty($address)){
				$cart = hikashop_get('class.cart');
				$cart->loadAddress($order->cart,$address,'object',$this->payment_params->address_type);

				$vars["ap_fname"]=@$order->cart->$address_type->address_firstname;
				$vars["ap_lname"]=@$order->cart->$address_type->address_lastname;
				$address1 = '';
				$address2 = '';
				if(!empty($order->cart->$address_type->address_street)){
					if(strlen($order->cart->$address_type->address_street)>100){
						$address1 = substr($order->cart->$address_type->address_street,0,99);
						$address2 = substr($order->cart->$address_type->address_street,99,199);
					}else{
						$address1 = $order->cart->$address_type->address_street;
					}
				}

				$vars["ap_addressline1"]=$address1;
				$vars["ap_addressline2"]=$address2;
				$vars["ap_zippostalcode"]=@$order->cart->$address_type->address_post_code;
				$vars["ap_city"]=@$order->cart->$address_type->address_city;
				$vars["ap_stateprovince"]=@$order->cart->$address_type->address_state->zone_code_3;
				$vars["ap_country"]=@$order->cart->$address_type->address_country->zone_code_3;
				$vars["ap_contactemail"]=$this->user->user_email;
				$vars["ap_contactphone"]=@$order->cart->$address_type->address_telephone;
			}
		}
		$this->payment_params->url='https://secure.payza.com/checkout';



		$this->vars = $vars;
		return $this->showPage('end');
	}

	function onPaymentNotification(&$statuses){
		$vars = array();
		$filter = JFilterInput::getInstance();
		foreach($_POST as $key => $value){
			$key = $filter->clean($key);
			$value = JRequest::getString($key);
			$vars[$key]=$value;
		}
		$order_id = (int)@$vars['apc_1'];

		$dbOrder = $this->getOrder($order_id);
		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);

		if(!$this->payment_params->notification){
			return false;
		}

		if($this->payment_params->debug){
			echo print_r($vars,true)."\n\n\n";
		}

		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order_id;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));

		if($this->payment_params->debug){
			echo print_r($dbOrder,true)."\n\n\n";
		}

		if($vars['ap_merchant']!=$this->payment_params->email || $vars['ap_securitycode']!=$this->payment_params->security_code){
			if($this->payment_params->debug){
				echo 'invalid response'."\n\n\n";
			}

			$emailData = new stdClass();
			$emailData->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Payza').'invalid response';
			$emailData->body = JText::sprintf("Hello,\r\n An Payza notification was refused because the notification from the Payza server was invalid")."\r\n\r\n".$order_text;
			$this->modifyOrder($order_id, $this->payment_params->invalid_status, true, $emailData);
			return false;
		}

		if($vars['ap_status']!='Success'){
			if($this->payment_params->debug){
				echo 'payment '.$vars['payment_status']."\n\n\n";
			}
			$this->modifyOrder($order_id, $this->payment_params->invalid_status, true, true);
			return false;
		}

		$this->modifyOrder($order_id, $this->payment_params->verified_status, true, true);
		return true;
	}


	function getPaymentDefaultValues(&$element) {
		$element->payment_name='Payza';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';

		$element->payment_params->address_type="billing";
		$element->payment_params->notification=1;
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->verified_status='confirmed';
	}
}
