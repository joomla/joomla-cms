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
class plgHikashoppaymentAlipay extends hikashopPaymentPlugin
{
	var $accepted_currencies = array( 'CNY', 'USD', 'EUR', 'JPY', 'GBP', 'CAD', 'AUD', 'SGD', 'CHF', 'SEK', 'DKK', 'NOK', 'HKD' );
	var $multiple = true;
	var $name = 'alipay';
	var $pluginConfig = array(
		'email' => array('HIKA_EMAIL', 'input'),
		'partner_id' => array('Partner ID', 'input'),
		'security_code' => array('Security code', 'input'),
		'mode' => array('Payment mode', 'list',array(
			'Partner' => 'Partner',
			'Direct' => 'Direct'
		)),
		'transport' => array('Transport', 'list',array(
			'http' => 'http',
			'https' => 'https'
		)),
		'sign_type' => array('Signature type', 'list',array(
			'MD5' => 'MD5'
		)),
		'sandbox' => array('Sandbox', 'boolean','0'),
		'server_to_server' => array('Server to Server', 'boolean','0'),
		'charge_and_ship' => array('Charge And Ship', 'boolean','0'),
		'debug' => array('DEBUG', 'boolean','0'),
		'notification' => array('ALLOW_NOTIFICATIONS_FROM_X', 'boolean','0'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function onAfterOrderConfirm(&$order,&$methods,$method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		$address=$this->app->getUserState( HIKASHOP_COMPONENT.'.billing_address');
		if(!empty($address)){
			$firstname=@$order->cart->billing_address->address_firstname;
			$lastname=@$order->cart->billing_address->address_lastname;
			$address1 = '';
			if(!empty($order->cart->billing_address->address_street)){
				$address1 = substr($order->cart->billing_address->address_street,0,200);
			}
			$zip=@$order->cart->billing_address->address_post_code;
			$city=@$order->cart->billing_address->address_city;
			$state=@$order->cart->billing_address->address_state->zone_code_3;
			$country=@$order->cart->billing_address->address_country->zone_code_2;
			$email=$this->user->user_email;
			$phone=@$order->cart->billing_address->address_telephone;
		}
		$notify_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=alipay&tmpl=component&lang='.$this->locale.$this->url_itemid;
		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id.$this->url_itemid;
		$out_trade_no = $order->order_id;

		if ($this->payment_params->Mode == "Partner")
		{
			$order_params = array(
				"seller_email" => $this->payment_params->email,
				"service" => "create_partner_trade_by_buyer",
				"partner" => $this->payment_params->partner_id,
				"return_url" => $return_url,
				"notify_url" => $notify_url,
				"_input_charset" => "utf-8",
				"subject" => 'order number : '.$out_trade_no,
				"body" => '',
				"out_trade_no" => $out_trade_no,
				"payment_type"=> "1",
				"price" => round($order->order_full_price, (int)$this->currency->currency_locale['int_frac_digits'] ),
				"quantity" => "1",
				"logistics_type"=>"EXPRESS",
				"logistics_fee"=> "0.00",
				"logistics_payment"=>"BUYER_PAY",
				'receive_name' => $lastname.' '.$firstname,
				'receive_address' => $address1,
				'receive_zip' => $zip,
				'receive_phone' =>$phone
			);
		}
		else {
			$order_params = array(
				"seller_email" => $this->payment_params->email,
				"service" => "create_direct_pay_by_user",
				"partner" => $this->payment_params->partner_id,
				"return_url" => $return_url,
				"notify_url" => $notify_url,
				"_input_charset" => "utf-8",
				"subject" => 'order number : '.$out_trade_no,
				"body" => '',
				"out_trade_no" => $out_trade_no,
				"payment_type"=> "1",
				"total_fee" => round($order->order_full_price, (int)$this->currency->currency_locale['int_frac_digits'] )
			);
		}
		$alipay = new alipay();
		$alipay->set_order_params($order_params);
		$alipay->set_transport($this->payment_params->transport);
		$alipay->set_security_code($this->payment_params->security_code);
		$alipay->set_sign_type($this->payment_params->sign_type);
		$sign = $alipay->_sign($alipay->_order_params);
		$this->payment_params->url = $alipay->create_payment_link();

		return $this->showPage('end');
	}
	function onPaymentNotification(&$statuses){

		$vars = array();
		$data = array();
		$filter = JFilterInput::getInstance();
		foreach($_REQUEST as $key => $value){
			$key = $filter->clean($key);
			if(preg_match("#^[0-9a-z_-]{1,30}$#i",$key)&&!preg_match("#^cmd$#i",$key)){
				$value = JRequest::getString($key);
				$vars[$key]=$value;
				$data[]=$key.'='.urlencode($value);
			}
		}
		$data = implode('&',$data).'&cmd=_notify-validate';

		$order_id = (int)@$vars['out_trade_no'];
		$order_status = '';

		$dbOrder = $this->getOrder($order_id);
		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);
		if($this->payment_params->debug){
			$this->writeToLog(print_r($vars,true)."\n\n\n");
			$this->writeToLog(print_r($dbOrder,true)."\n\n\n");
		}
		if(empty($dbOrder)){
			echo "Could not load any order for your notification ".$order_id;
			return false;
		}
		$old_status=$dbOrder->order_status;
		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order_id;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
		if($this->payment_params->debug){
			$this->writeToLog(print_r($dbOrder,true)."\n\n\n");
		}

		$history = new stdClass();
		$email = new stdClass();

		$alipay = new alipay();
		$alipay->set_transport($this->payment_params->transport);
		$alipay->set_security_code($this->payment_params->security_code);
		$alipay->set_sign_type($this->payment_params->sign_type);
		$alipay->set_partner_id($this->payment_params->partner_id);
		if($alipay->_transport == "https") {
			$notify_url = $alipay->_notify_gateway . "service=notify_verify" ."&partner=" .$alipay->_partner_id . "&notify_id=".$_POST["notify_id"];
		} else {
			$notify_url = $alipay->_notify_gateway . "partner=" . $alipay->_partner_id . "&notify_id=".$_POST["notify_id"];
		}
		$url_array  = parse_url($notify_url);
		$errno='';
		$errstr='';
		$notify = array();
		$response = array();
		if($url_array['scheme'] == 'https') {
			$transport = 'ssl://';
			$url_array['port'] = '443';
		} else {
			$transport = 'tcp://';
			$url_array['port'] = '80';
		}
		if($this->payment_params->debug){
			$this->writeToLog(print_r($url_array,true)."\n\n\n");
		}
		$fp = @fsockopen($transport . $url_array['host'], $url_array['port'], $errno, $errstr, 60);
		if(!$fp) {
			$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Alipay').' '.JText::sprintf('PAYPAL_CONNECTION_FAILED',$dbOrder->order_number);
			$email->body = str_replace('<br/>',"\r\n",JText::sprintf('NOTIFICATION_REFUSED_NO_CONNECTION','Alipay'))."\r\n\r\n".$order_text;

			$this->modifyOrder($order_id,null,false,$email);

			JError::raiseError( 403, JText::_( 'Access Forbidden' ));
			return false;
		} else {
			fputs($fp, "POST " . $url_array['path'] . " HTTP/1.1\r\n");
			fputs($fp, "HOST: " . $url_array['host'] . "\r\n");
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($fp, "Content-length: " . strlen($url_array['query']) . "\r\n");
			fputs($fp, "Connection: close\r\n\r\n");
			fputs($fp, $url_array['query'] . "\r\n\r\n");
			while(!feof($fp)) {
				$notify[] = @fgets($fp, 1024);
			}
			fclose($fp);
			if($this->payment_params->debug){
				$this->writeToLog(print_r($notify,true)."\n\n\n");
			}
			$response=implode(',', $notify);
		}
		if(is_array($_POST)) {
			$tmp_array = array();
			foreach($_POST as $key=>$value) {
				if($value != '' && $key != 'sign' && $key != 'sign_type') {
					$tmp_array[$key] = $value;
				}
			}
			ksort($tmp_array);
			reset($tmp_array);
			$params = $tmp_array;
		} else {
			return false;
		}
		$sign = $alipay->_sign($params);
		if($this->payment_params->debug){
			$this->writeToLog("\n sign1 : \n".print_r($sign,true)."\n\n\n");
			$this->writeToLog("\n sign2 : \n".print_r($_POST['sign'],true)."\n\n\n");
		}
		if((preg_match('/true$/i', $response) && $sign == $_POST['sign']) && ($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS' || $_POST['trade_status'] == 'WAIT_SELLER_SEND_GOODS' || $_POST['trade_status']== 'WAIT_BUYER_PAY')) {

			$price_check = round($dbOrder->order_full_price, (int)$this->currency->currency_locale['int_frac_digits'] );

			$history->notified=1;
			$history->amount=$price_check;
			$history->data = ob_get_clean();

			$order_status = $this->payment_params->verified_status;


			if($dbOrder->order_status == $order_status) return true;
			$mail_status=$statuses[$order_status];
			$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Alipay',$_POST['trade_status'],$dbOrder->order_number);
			$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Alipay',$_POST['trade_status'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$mail_status)."\r\n\r\n".$order_text;

			$this->modifyOrder($order_id,$order_status,$history,$email);

			return true;
		} else {
			$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','Alipay').'invalid response';
			$email->body = JText::sprintf("Hello,\r\n An Alipay notification was refused because the response from the Alipay server was invalid")."\r\n\r\n".$order_text;

			$this->modifyOrder($order_id,null,false,$email);

			if($this->payment_params->debug){
				$this->writeToLog('invalid response'."\n\n\n");
			}
			return false;
		}
	}

	function onPaymentConfigurationSave(&$element){
		if( empty($element->payment_params->currency) )
			$element->payment_params->currency = $this->accepted_currencies[0];
		return true;
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='Alipay';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA';

		$element->payment_params->email='';
		$element->payment_params->partner_id='';
		$element->payment_params->mode='partner';
		$element->payment_params->transport='http';
		$element->payment_params->sign_type='MD5';
		$element->payment_params->sandbox=false;
		$element->payment_params->server_to_server=false;
		$element->payment_params->charge_and_ship=false;
		$element->payment_params->debug=false;
		$element->payment_params->notification=false;
		$element->payment_params->currency = $this->accepted_currencies[0];
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->verified_status='confirmed';
	}

}


class alipay {
	var $_order_params;
	var $_security_code;
	var $_sign_type;
	var $_partner_id;
	var $_transport;
	var $_gateway;
	var $_notify_gateway;

	function set_order_params($order_params) {
		if(is_array($order_params)) {
			$tmp_array = array();
			foreach($order_params as $key=>$value) {
				if($value != '' && $key != 'sign' && $key != 'sign_type') {
					$tmp_array[$key] = $value;
				}
			}
			ksort($tmp_array);
			reset($tmp_array);
			$this->_order_params = $tmp_array;
		} else {
			return false;
		}
	}

	function set_security_code($security_code) {
		$this->_security_code = $security_code;
	}

	function set_sign_type($sign_type) {
		$this->_sign_type = strtoupper($sign_type);
	}

	function set_partner_id($partner_id) {
		$this->_partner_id = $partner_id;
	}

	function set_transport($transport) {
		$this->_transport = strtolower($transport);
		if($this->_transport == 'https') {
			$this->_gateway = 'http://www.alipay.com/cooperate/gateway.do?';
			$this->_notify_gateway = $this->_gateway;
		} elseif($this->_transport == 'http') {
			$this->_gateway = 'http://www.alipay.com/cooperate/gateway.do?';
			$this->_notify_gateway = 'http://notify.alipay.com/trade/notify_query.do?';
		}
	}

	function _sign($params) {
		$params_str = '';
		foreach($params as $key => $value) {
			if($params_str == '') {
				$params_str = "$key=$value";
			} else {
				$params_str .= "&$key=$value";
			}
		}
		if($this->_sign_type == 'MD5') {
			return md5($params_str . $this->_security_code);
		}
	}

	function create_payment_link() {
		$params_str = '';
		foreach($this->_order_params as $key => $value) {
			$params_str .= "$key=" . urlencode($value) . "&";
		}
		return $this->_gateway . $params_str . 'sign=' . $this->_sign($this->_order_params) . '&sign_type=' . $this->_sign_type;
	}
}
