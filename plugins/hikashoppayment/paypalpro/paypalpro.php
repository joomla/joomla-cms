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
class plgHikashoppaymentPaypalpro extends hikashopPaymentPlugin
{
	var $accepted_currencies = array( 'USD', 'GBP', 'EUR', 'JPY', 'CAD', 'AUD' );
	var $multiple = true;
	var $name = 'paypalpro';
	var $pluginConfig = array(
		'login' => array('HIKA_USERNAME', 'input'),
		'password' => array('HIKA_PASSWORD', 'input'),
		'signature' => array('SIGNATURE', 'input'),
		'environnement' => array('ENVIRONNEMENT', 'list',array(
			'production' => 'HIKA_PRODUCTION',
			'sandbox' => 'HIKA_SANDBOX',
			'beta-sandbox' => 'Beta-Sandbox'
		)),
		'instant_capture' => array('Instant Capture', 'boolean','0'),
		'ask_ccv' => array('Ask CCV', 'boolean','1'),
		'details' => array('SEND_DETAILS_OF_ORDER', 'boolean','1'),
		'send_order_id' => array('Send order id', 'boolean','0'),
		'send_notification' => array('ORDER_NOTIFICATION', 'boolean','0'),
		'debug' => array('DEBUG', 'boolean','0'),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function needCC(&$method) {
		$method->ask_cc = true;
		if( $method->payment_params->ask_ccv ) {
			$method->ask_ccv = true;
		}
		return true;
	}

	function onBeforeOrderCreate(&$order, &$do) {
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;

		$this->ccLoad();

		$amount = round($order->cart->full_total->prices[0]->price_value_with_tax,(int)$this->currency->currency_locale['int_frac_digits']);

		$vars = array(
			'USER' => $this->payment_params->login,
			'PWD' => $this->payment_params->password,
			'SIGNATURE' => $this->payment_params->signature,
			'VERSION' => '51.0',
			'METHOD' => 'DoDirectPayment',
			'PAYMENTACTION' => $this->payment_params->instant_capture?'Sale':'Authorization',
			'AMT' => $amount,
			'ACCT' => $this->cc_number,
			'EXPDATE' => $this->cc_month.'20'.$this->cc_year,
			'FIRSTNAME' => $order->cart->billing_address->address_firstname,
			'LASTNAME' => $order->cart->billing_address->address_lastname,
			'CURRENCYCODE' => $this->currency->currency_code,
			'EMAIL' => $this->user->user_email,
			'STREET' => @$order->cart->billing_address->address_street,
			'STREET2' => @$order->cart->billing_address->address_street2,
			'CITY' => @$order->cart->billing_address->address_city,
			'STATE' => @$order->cart->billing_address->address_state->zone_name,
			'COUNTRYCODE' => @$order->cart->billing_address->address_country->zone_code_2,
			'ZIP' => @$order->cart->billing_address->address_post_code,
			'BUTTONSOURCE' => 'HikariSoftware_Cart_DP'
		);

		if(@$this->payment_params->send_order_id){
			$database = JFactory::getDBO();
			$database->setQuery('SELECT MAX(order_id) FROM #__hikashop_order;');
			$max = (int)$database->loadResult();
			$vars['INVNUM'] = $max+1;
		}

		if(!empty($order->cart->billing_address->address_street2)){
			$vars['STREET2'] = substr($order->cart->billing_address->address_street2,0,99);
		}

		if(!empty($order->cart->shipping_address)){
			$vars['SHIPTONAME'] = @$order->cart->shipping_address->address_firstname.' '.@$order->cart->shipping_address->address_lastname;
			$vars['SHIPTOSTREET'] = @$order->cart->shipping_address->address_street;
			$vars['SHIPTOSTREET2'] = @$order->cart->shipping_address->address_street2;
			$vars['SHIPTOCITY'] = @$order->cart->shipping_address->address_city;
			if(in_array(@$order->cart->shipping_address->address_country->zone_code_2, array('US'))){
				$vars['SHIPTOSTATE'] = @$order->cart->shipping_address->address_state->zone_code_3;
			}else{
				$vars['SHIPTOSTATE'] = @$order->cart->shipping_address->address_state->zone_name;
			}
			$vars['SHIPTOCOUNTRY'] = @$order->cart->shipping_address->address_country->zone_code_2;
			$vars['SHIPTOZIP'] = @$order->cart->shipping_address->address_post_code;
			$vars['SHIPTOPHONENUM'] = @$order->cart->shipping_address->address_phone;
		}

		if(!isset($this->payment_params->details)) $this->payment_params->details = 1;

		if(!empty($this->payment_params->details)){
			$i = 1;
			$tax = 0;
			$config =& hikashop_config();
			$group = $config->get('group_options',0);
			foreach($order->cart->products as $product){
				if($group && $product->order_product_option_parent_id) continue;
				if($product->order_product_quantity<1) continue;
				$vars["L_NAME".$i] = substr(strip_tags($product->order_product_name),0,127);
				$vars["L_NUMBER".$i] = $product->order_product_code;
				$vars["L_AMT".$i] = round($product->order_product_price,(int)$this->currency->currency_locale['int_frac_digits']);
				$vars["L_QTY".$i] = $product->order_product_quantity;
				$vars["L_TAXAMT".$i] = round($product->order_product_tax,(int)$this->currency->currency_locale['int_frac_digits']);
				$tax += round($product->order_product_tax,(int)$this->currency->currency_locale['int_frac_digits'])*$product->order_product_quantity;
				$i++;
			}
			if(bccomp($tax,0,5)){
				$vars['TAXAMT'] = round($tax+$order->order_shipping_tax+$order->order_payment_tax-$order->order_discount_tax,(int)$this->currency->currency_locale['int_frac_digits']);
			}
			if(!empty($order->cart->coupon)){
				$vars["SHIPDISCAMT"] = round($order->order_discount_price,(int)$this->currency->currency_locale['int_frac_digits']);
			}

			if(!empty($order->order_payment_price) && bccomp($order->order_payment_price,0,5)){
				$vars["L_NAME".$i] = JText::_('HIKASHOP_PAYMENT');
				$vars["L_NUMBER".$i] = 'payment';
				$vars["L_AMT".$i] = round($order->order_payment_price-$order->order_payment_tax,(int)$this->currency->currency_locale['int_frac_digits']);
				$vars["L_QTY".$i] = 1;
				$vars["L_TAXAMT".$i] = round($order->order_payment_tax,(int)$this->currency->currency_locale['int_frac_digits']);
				$i++;
			}

			if(!empty($order->order_shipping_price) && bccomp($order->order_shipping_price,0,5)){
				$vars['SHIPPINGAMT'] = round($order->order_shipping_price,(int)$this->currency->currency_locale['int_frac_digits']);
			}
			$vars['ITEMAMT']=$vars['AMT']-(@$vars['TAXAMT']+@$vars['SHIPPINGAMT']);
		}

		if( $this->payment_params->ask_ccv ) {
			$vars['CVV2'] = $this->cc_CCV;
		}

		if( $this->payment_params->debug ) {
			echo print_r($vars, true) . "\n\n\n";
		}

		$session = curl_init();
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($session, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($session, CURLOPT_POST,           1);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($session, CURLOPT_VERBOSE,        1);
		curl_setopt($session, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($session, CURLOPT_FAILONERROR,    true);

		$httpsHikashop = str_replace('http://','https://', HIKASHOP_LIVE);
		$url = 'api-3t.paypal.com/nvp';
		if( $this->payment_params->environnement != 'production' ) {
			$url = 'api-3t.'.$this->payment_params->environnement.'.paypal.com/nvp';
		}

		if( $this->payment_params->debug ) {
			echo print_r($url, true) . "\n\n\n";
		}

		$tmp = array();
		foreach($vars as $k => $v) {
			$tmp[] = $k . '=' . urlencode(trim($v));
		}
		$tmp = implode('&', $tmp);

		curl_setopt($session, CURLOPT_URL, 'https://' . $url);
		curl_setopt($session, CURLOPT_REFERER, $httpsHikashop);
		curl_setopt($session, CURLOPT_POSTFIELDS, $tmp);

		$ret = curl_exec($session);
		$error = curl_errno($session);

		if( !$error ) {

			$params = explode('&', $ret);
			$ret = array();
			foreach($params as $p) {
				$t = explode('=', $p);
				$ret[strtoupper($t[0])] = $t[1];
			}

			if( $this->payment_params->debug ) {
				echo print_r($ret, true) . "\n\n\n";
			}

			$responseCode = null;
			if( isset($ret['ACK']) ) {
				$responseCode = strtoupper($ret['ACK']);
			}

			if( isset($responseCode) ) {

				if( $responseCode == 'SUCCESS' || $responseCode == 'SUCCESSWITHWARNING' ) {
					$history = array(
						'notified' => (int)@$this->payment_params->send_notification,
						'data' => 'PayPal transaction id: ' .$ret['TRANSACTIONID'],
					);
					$this->modifyOrder($order, $this->payment_params->verified_status, $history, true);

				} else {
					$message = 'Error';
					if(!empty($ret['ERRORCODE'])){
						$message.=' '.$ret['ERRORCODE'];
					}elseif(!empty($ret['L_ERRORCODE0'])){
						$message.=' '.$ret['L_ERRORCODE0'];
					}
					if(!empty($ret['LONGMESSAGE'])){
						$message.=': '.urldecode($ret['LONGMESSAGE']);
					}elseif(!empty($ret['L_LONGMESSAGE0'])){
						$message.=': '.urldecode($ret['L_LONGMESSAGE0']);
					}

					$this->app->enqueueMessage($message);
					$do = false;
				}
			} else {
				$this->app->enqueueMessage('An error occurred. No response code in PayPal Pro server\'s response');
				$do = false;
			}
		} else {
			$this->app->enqueueMessage('An error occurred. The connection to the PayPal Pro server could not be established: '.curl_error($session));
			$do = false;
		}
		curl_close($session);

		$this->ccClear();

		return true;
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id){

		return $this->showPage('thanks');

	}

	function onPaymentConfiguration(&$element){
		parent::onPaymentConfiguration($element);
		$obj = $element;
		$field = '';
		if(empty($obj->payment_params->login)){
			$field = JText::_( 'USERNAME' );
		}elseif(empty($obj->payment_params->password)){
			$field = JText::_( 'PASSWORD' );
		}elseif(empty($obj->payment_params->signature)){
			$field = JText::_( 'SIGNATURE' );
		}
		if(!empty($field)){
			$app = JFactory::getApplication();
			$lang = JFactory::getLanguage();
			$locale=strtolower(substr($lang->get('tag'),0,2));
			$app->enqueueMessage(JText::sprintf('ENTER_INFO_REGISTER_IF_NEEDED','PayPal Pro',$field,'PayPal Pro','https://www.paypal.com/'.$locale.'/mrb/pal=SXL9FKNKGAEM8'));
		}
	}


	function getPaymentDefaultValues(&$element) {
		$element->payment_name='PayPal Pro';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card';

		$element->payment_params->login='';
		$element->payment_params->password='';
		$element->payment_params->ask_ccv = true;
		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
	}
}
