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
class plgHikashoppaymentGOOGLECHECKOUT extends hikashopPaymentPlugin
{
	var $accepted_currencies = array( 'USD', 'GBP' );
	var $error_msg = array();
	var $multiple = true;
	var $name = 'googlecheckout';
	var $pluginConfig = array(
		'notif_url' => array('Notify url', 'html', ''),
		'merchant_id' => array('Merchant ID', 'input'),
		'merchant_key' => array('Merchant KEY', 'input'),
		'currency' => array('Currency', 'list',array(
			'USD' => 'USD',
			'GBP' => 'GBP'
		)),
		'sandbox' => array('Sandbox', 'boolean','0'),
		'server_to_server' => array('Server to Server', 'boolean','0'),
		'charge_and_ship' => array('Charge And Ship', 'boolean','0'),
		'debug' => array('DEBUG', 'boolean','0'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus')
	);

	function __construct(&$subject, $config){
		$httpsHikashop = str_replace('http://','https://', HIKASHOP_LIVE);
		$this->pluginConfig['notif_url'][2] = $httpsHikashop.'index.php?option=com_hikashop&amp;ctrl=checkout&amp;task=notify&amp;notif_payment=googlecheckout&amp;tmpl=component';

		return parent::__construct($subject, $config);
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		$httpsHikashop = str_replace('http://','https://', HIKASHOP_LIVE);
		$notify_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=googlecheckout&tmpl=component&lang='.$this->locale;
		$return_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=googlecheckout&tmpl=component&user_return=1&lang='.$this->locale;

		$price = round($order->cart->full_total->prices[0]->price_value_with_tax,(int)$this->currency->currency_locale['int_frac_digits']);

		$data = '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
		$data .= '<checkout-shopping-cart xmlns="http://checkout.google.com/schema/2"><shopping-cart><items>';
		$config =& hikashop_config();
		$group = $config->get('group_options',0);
		foreach($order->cart->products as $product){
			if($group && $product->order_product_option_parent_id) continue;
			$data .= '<item><item-name>Order #'.$order->order_id.' - '.$product->order_product_name.'</item-name><item-description></item-description><unit-price currency="'.$this->currency->currency_code.'">'.$product->order_product_price.'</unit-price><quantity>'.$product->order_product_quantity.'</quantity></item>';
		}
		if(isset($order->order_discount_code)) $data .= '<item><item-name>Order #'.$order->order_id.' - '.JText::_('HIKASHOP_COUPON').'</item-name><item-description></item-description><unit-price currency="'.$this->currency->currency_code.'">-'.$order->order_discount_price.'</unit-price><quantity>1</quantity></item>';

		$shipping_price = 0.0;
		if(!empty($order->order_shipping_price)) {
			$shipping_price = $order->order_shipping_price;
			if(!empty($order->order_shipping_tax))
				$shipping_price -= $order->order_shipping_tax;
		}
		if($shipping_price > 0 )
			$data .= '<item><item-name>Order #'.$order->order_id.' - '.JText::_('HIKASHOP_SHIPPING').' </item-name><item-description></item-description><unit-price currency="'.$this->currency->currency_code.'">'.$shipping_price.'</unit-price><quantity>1</quantity></item>';

		if($order->order_payment_price > 0 ) $data .= '<item><item-name>Order #'.$order->order_id.' - '.JText::_('HIKASHOP_PAYMENT').' </item-name><item-description></item-description><unit-price currency="'.$this->currency->currency_code.'">'.$order->order_payment_price.'</unit-price><quantity>1</quantity></item>';
		$tax = $order->cart->full_total->prices[0]->price_value_with_tax - $order->cart->full_total->prices[0]->price_value;
		if($tax>0) $data .= '<item><item-name>Order #'.$order->order_id.' - '.JText::_('TAXES').' </item-name><item-description></item-description><unit-price currency="'.$this->currency->currency_code.'">'.$tax.'</unit-price><quantity>1</quantity></item>';
		$data .= '</items></shopping-cart><checkout-flow-support><merchant-checkout-flow-support/></checkout-flow-support></checkout-shopping-cart>';

		if( $this->payment_params->debug ) { echo 'XML Sent to Google<pre>'.htmlentities($data).'</pre>'; }

		if( $this->payment_params->server_to_server == true ) {
			$ret =& $this->webCall('checkout', $data, $this->payment_params);
			if( $ret !== false ) {
				if( preg_match('#<redirect-url>(.*)</redirect-url>#iU', $ret, $redirect) ) {
					$redirect = html_entity_decode(trim($redirect[1]));
					$this->app->redirect($redirect);
				}
				if( $this->payment_params->debug ) { echo 'Google call return<pre>'.htmlentities($ret).'</pre>'; }
			}
			$url = '';
			$vars = '';
			$this->app->enqueueMessage('Google Checkout error. Please log-in to the backend of Google Checkout to see the log.');
		} else {
			$vars = array(
				'signature' => base64_encode($this->signature($data, $this->payment_params->merchant_key)),
				'cart' => base64_encode($data)
			);
			if( $this->payment_params->sandbox ) {
				$url = 'https://sandbox.google.com/checkout/api/checkout/v2/checkout/Merchant/';
			} else {
				$url = 'https://checkout.google.com/api/checkout/v2/checkout/Merchant/';
			}
			$url .= $this->payment_params->merchant_id;
		}
		$this->url = $url;
		$this->vars = $vars;

		return $this->showPage('end');
	}

	function onPaymentNotification(&$statuses){
		$response = isset($HTTP_RAW_POST_DATA)?$HTTP_RAW_POST_DATA:file_get_contents('php://input');
		if (get_magic_quotes_gpc()) { $response = stripslashes($response); }

		$vars =& $this->parseResponse($response);
		$order_id = (int)$vars['order-num'];

		$dbOrder = $this->getOrder($order_id);
		if(empty($dbOrder)){
			echo "Could not load any order for your notification ".$order_id;
			header('HTTP/1.0 400 Bad Request');
			exit;
		}
		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);
		if($this->payment_params->debug){
			echo print_r($vars,true)."\n\n\n";
			echo print_r($dbOrder,true)."\n\n\n";
		}

		$compare_mer_id = '';
		$compare_mer_key = '';
		if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
			$compare_mer_id = $_SERVER['PHP_AUTH_USER'];
			$compare_mer_key = $_SERVER['PHP_AUTH_PW'];
		}
		if( $compare_mer_id != $this->payment_params->merchant_id || $compare_mer_key != $this->payment_params->merchant_key ) {
			header('HTTP/1.1 401 Unauthorized');
			return false;
		}

		if( in_array($vars['state'], array('REVIEWING','CHARGING')) || empty($vars['state']) || in_array($vars['type'], array('risk-information-notification','charge-amount-notification')) ) {
			$this->sendAck($vars);
			exit;
		}

		if($vars['state'] == 'CHARGEABLE') {
			if( $vars['type'] != 'authorization-amount-notification' ) {
				$this->sendAck($vars);
				exit;
			}

			$data = '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
			if( $this->payment_params->charge_and_ship ) {
				$data .= '<charge-and-ship-order xmlns="http://checkout.google.com/schema/2" google-order-number="'.$vars['google-order'].'">';
			} else {
				$data .= '<charge-order xmlns="http://checkout.google.com/schema/2" google-order-number="'.$vars['google-order'].'">';
			}

			if( $vars['currency'] != '' ) {
				$data .= '<amount currency="'.$vars['currency'].'">'.$vars['amount'].'</amount>';
			}
			if( $this->payment_params->charge_and_ship ) {
				$data .= '</charge-and-ship-order>';
			} else {
				$data .= '</charge-order>';
			}

			$serial = $vars['serial'];
			$ret =& $this->webCall('request', $data, $this->payment_params);
			$vars =& $this->parseResponse($ret);
			$vars['serial'] = $serial;

			if( $vars['type'] == 'request-received' ) {
				$this->sendAck($vars);
				exit;
			}
		}

		if( $dbOrder->order_status == $this->payment_params->verified_status || $dbOrder->order_status == $this->payment_params->invalid_status ) {
			$this->sendAck($vars);
			exit;
		}

		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order_id;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));

		$history = new stdClass();
		$history->notified = 0;
		$history->amount = $vars['amount']. $vars['currency'];
		$history->data = var_export($vars, true) . "\r\n" . ob_get_clean();

		if($vars['state'] == 'CHARGED') {
			$order_status = $this->payment_params->verified_status;
			$history->notified = 1;
			$payment_status = 'confirmed';
		} else {
			$order_status = $this->payment_params->invalid_status;
			$payment_status = 'cancelled';
			$order_text = 'Google Checkout State: ' . $vars['state'] ."\r\n". $order_text;
		}
		$mail_status = $statuses[order_status];

		$email = new stdClass();
		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','GOOGLECHECKOUT',$payment_status,$dbOrder->order_number);
		$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','GOOGLECHECKOUT',$payment_status)).' '.JText::sprintf('ORDER_STATUS_CHANGED',$mail_status)."\r\n\r\n".$order_text;

		$this->modifyOrder($order_id, $order_status, $history, $email);

		$this->sendAck($vars);
		exit;
	}

	function onPaymentConfigurationSave(&$element){
		if( empty($element->payment_params->currency) ) {
			$element->payment_params->currency = $this->accepted_currencies[0];
		}
		return true;
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='Google Checkout';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';

		$element->payment_params->login='';
		$element->payment_params->password='';
		$element->payment_params->currency = $this->accepted_currencies[0];
		$element->payment_params->security = false;
		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
	}

	function webCall($type, &$data, $params) {

		if( $type == 'request' ) {
			$called_action = 'request';
		} else if( $type == 'checkout' ) {
			if( $params->server_to_server ) {
				$called_action = 'merchantCheckout';
			} else {
				$called_action = 'checkout';
			}
		}

		if( $params->sandbox ) {
			$url = 'https://sandbox.google.com/checkout/api/checkout/v2/'.$called_action.'/Merchant/';
		} else {
			$url = 'https://checkout.google.com/api/checkout/v2/'.$called_action.'/Merchant/';
		}
		$url .= $params->merchant_id;

		$headers = array(
			'Authorization: Basic '.base64_encode($params->merchant_id.':'.$params->merchant_key),
			'Content-Type: application/xml; charset=UTF-8',
			'Accept: application/xml; charset=UTF-8',
			'User-Agent: HikaShop Google Checkout Plugin'
		);

		$session = curl_init($url);
		curl_setopt($session, CURLOPT_POST, true);
		curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($session, CURLOPT_POSTFIELDS, $data);
		curl_setopt($session, CURLOPT_HEADER, true);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

		$ret = curl_exec($session);
		curl_close($session);

		return $ret;
	}

	function parseResponse(&$xml) {
		$vars = array(
			'currency' => '',
			'amount' => 0,
			'serial' => '',
			'order-num' => '',
			'state' => ''
		);

		if( preg_match('#<(.*) xmlns="http://checkout.google.com/schema/2" serial-number=#iU', $xml, $ggreg) ) {
			$vars['type'] = trim($ggreg[1]);
		}

		if( preg_match('#serial-number="(.*)"#iU', $xml, $ggreg) ) {
			$vars['serial'] = $ggreg[1];
		}
		if( preg_match('/<item-name>.* #(.*)<\/item-name>/iU', $xml, $ggreg) ) {
			$vars['order-num'] = trim($ggreg[1]);
		}
		if( preg_match('#<google-order-number>(.*)</google-order-number>#iU', $xml, $ggreg) ) {
			$vars['google-order'] = trim($ggreg[1]);
		}
		if( preg_match('#<order-total currency="(.*)">(.*)</order-total>#iU', $xml, $ggreg) ) {
			$vars['currency'] = $ggreg[1];
			$vars['amount'] = (int)$ggreg[2];
		}
		if( preg_match('#<new-financial-order-state>(.*)</new-financial-order-state>#iU', $xml, $ggreg) ) {
			$vars['state'] = trim($ggreg[1]);
		} else if( preg_match('#<financial-order-state>(.*)</financial-order-state>#iU', $xml, $ggreg) ) {
			$vars['state'] = trim($ggreg[1]);
		}

		return $vars;
	}

	function sendAck(&$vars) {
		$acknowledgment = '<notification-acknowledgment xmlns="http://checkout.google.com/schema/2"';
		if(!empty($vars['serial'])) {
			$acknowledgment .= ' serial-number="'.$vars['serial'].'"';
		}
		$acknowledgment .= ' />';

		$msg = ob_get_clean();
		echo $acknowledgment;
		ob_start();
		echo $msg;
	}

	function signature($data, $key) {
		$blocksize = 64;
		if (strlen($key) > $blocksize) {
			$key = pack('H*', sha1($key));
		}
		$key = str_pad($key, $blocksize, chr(0x00));
		$ipad = str_repeat(chr(0x36), $blocksize);
		$opad = str_repeat(chr(0x5c), $blocksize);
		$hmac = pack(
			'H*', sha1(
				($key^$opad).pack(
					'H*', sha1(
						($key^$ipad).$data
					)
				)
			)
		);
		return $hmac;
	}
}
