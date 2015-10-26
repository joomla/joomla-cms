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
class plgHikashoppaymentVirtualmerchant extends hikashopPaymentPlugin
{
	var $multiple = true;
	var $name = 'virtualmerchant';
	var $pluginConfig = array(
		'merchant_id' => array('ATOS_MERCHANT_ID', 'input'),
		'user_id' => array('HKASHOP_USER_ID', 'input'),
		'pin' => array('PIN', 'input'),
		'currency' => array('CURRENCY', 'list',array(
			'AED','KZT','ANG','LBP','ARS','LKR','AUS','LTL','AWG','LVL',
			'AZN','LYD','BBD','MAD','BDT','MKD','BGN','MUR','BHD','MWK',
			'BMD','MXN','BRL','MYR','BSD','NAD','BWP','NGN','CAD','NOK',
			'CDF','NPR','CHF','NZD','CLP','OMR','CNY','PEN','COP','PHP',
			'CRC','PKR','CZK','PLN','DKK','QAR','DOP','RON','DZD','RSD',
			'EEK','RUB','EGP','SAR','ETB','SEK','EUR','SGD','FJD','SYP',
			'GBP','THB','GTQ','TND','HKD','TRY','HRK','TTD','HTG','TWD',
			'HUF','UAH','IDR','USD','ILS','VEF','INR','VND','IRR','XAF',
			'ISK','XCD','JMD','XOF','JOD','XPF','JPY','ZAR','KES','ZMK',
			'KRW','ZWL','KWD')
		),
		'ask_ccv' => array('CARD_VALIDATION_CODE', 'boolean','0'),
		'use_avs' => array('Add AVS information', 'boolean','0'),
		'debug' => array('DEBUG', 'boolean','0'),
		'sandbox' => array('SANDBOX', 'boolean','0'),
		'test_mode' => array('TEST_MODE', 'boolean','0'),
		'multi_currency' => array('Multi-currency support', 'boolean','0'),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function needCC(&$method) {
		$method->ask_cc = true;
		if( $method->payment_params->ask_ccv ) {
			$method->ask_ccv = true;
		}
	}

	function onBeforeOrderCreate(&$order, &$do) {
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;

		if(!function_exists('curl_init')){
			$this->app->enqueueMessage('The Virtual Merchant payment plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			return false;
		}

		$this->ccLoad();

		$amount = number_format($order->cart->full_total->prices[0]->price_value_with_tax,2,'.','');

		$vars = '<txn>'.
			'<ssl_merchant_ID>'.$this->payment_params->merchant_id.'</ssl_merchant_ID>'.
			'<ssl_user_id>'.$this->payment_params->user_id.'</ssl_user_id>'.
			'<ssl_pin>'.$this->payment_params->pin.'</ssl_pin>'.
			'<ssl_test_mode>'.((@$this->payment_params->test_mode)?'True':'False').'</ssl_test_mode>'.
			'<ssl_transaction_type>CCSALE</ssl_transaction_type>'.
			'<ssl_show_form >False</ssl_show_form >'.
			'<ssl_card_number>'.str_replace(array('<','>'),array('&lt;','&gt;'),$this->cc_number).'</ssl_card_number>'.
			'<ssl_exp_date>'.$this->cc_month.$this->cc_year.'</ssl_exp_date>'.
			'<ssl_amount>'.$amount.'</ssl_amount>'.
			'<ssl_salestax>0.00</ssl_salestax>'.
			'<ssl_cvv2cvc2_indicator>'.(($this->payment_params->ask_ccv)?'1':'0').'</ssl_cvv2cvc2_indicator>'.
			'<ssl_cvv2cvc2>'.str_replace(array('<','>'),array('&lt;','&gt;'),$this->cc_CCV).'</ssl_cvv2cvc2>'.
			'<ssl_customer_code>'.$this->user->user_id.'</ssl_customer_code>'.
			'<ssl_first_name>'.str_replace(array('<','>'),array('&lt;','&gt;'),$order->cart->billing_address->address_firstname).'</ssl_first_name>'.
			'<ssl_last_name>'.str_replace(array('<','>'),array('&lt;','&gt;'),$order->cart->billing_address->address_lastname).'</ssl_last_name>';

		if(@$this->payment_params->multi_currency)
			$vars .= '<ssl_transaction_currency>'.$this->currency->currency_code.'</ssl_transaction_currency>';

		if($this->payment_params->use_avs) {
			$addr1 = @$order->cart->billing_address->address_street;
			if(strlen(urlencode($addr1)) > 20) {
				$vars .= '<ssl_avs_address>'.urlencode(substr($addr1,0,20)).'</ssl_avs_address>'.
					'<ssl_address2>'.urlencode(substr($addr1,20,30)).'</ssl_address2>';
			} else {
				$vars .= '<ssl_avs_address>'.urlencode($addr1).'</ssl_avs_address>';
			}
			$vars .= '<ssl_city>'.urlencode(@$order->cart->billing_address->address_city).'</ssl_city>'.
				'<ssl_state>'.urlencode(@$order->cart->billing_address->address_state->zone_name).'</ssl_state>'.
				'<ssl_avs_zip>'.urlencode(@$order->cart->billing_address->address_post_code).'</ssl_avs_zip>'.
				'<ssl_country>'.urlencode(@$order->cart->billing_address->address_country->zone_name_english).'</ssl_country>';
		}

		$vars .= '<ssl_email>'.str_replace(array('<','>'),array('&lt;','&gt;'),$this->user->user_email).'</ssl_email>'.
		'</txn>';

		if( $this->payment_params->debug ) {
			echo htmlentities(str_replace(
					array($this->cc_number, $this->cc_CCV),
					array('**************', '***'),
				$vars)) . "\n\n\n";
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
		if($this->payment_params->sandbox) {
			$url = 'demo.myvirtualmerchant.com/VirtualMerchantDemo/processxml.do';
		} else {
			$url = 'www.myvirtualmerchant.com/VirtualMerchant/processxml.do';
		}

		curl_setopt($session, CURLOPT_URL, 'https://' . $url);
		curl_setopt($session, CURLOPT_REFERER, $httpsHikashop);
		curl_setopt($session, CURLOPT_POSTFIELDS, 'xmldata=' . urlencode($vars) );

		$ret = curl_exec($session);
		$error = curl_errno($session);

		curl_close($session);

		if( !$error ) {


			$p0 = strpos($ret,'<txn>');
			if($p0 !== false ) { $ret = substr($ret, $p0); }
			$data = str_replace(array('<txn>','</txn>'), '', trim($ret));
			$ret = array();
			while ($data) {
				$p0 = strpos($data, '<');
				$p1 = strpos($data, '>');
				if($p0 === false || $p1 === false) {
					break;
				}
				$key = substr($data, $p0+1, $p1-1);
				$data = substr($data, $p0+1);
				if(substr($key,-1) == '/') {
					$ret[$key] = '';
				} else {
					$l = strlen($key);
					$p1 = strpos($data, '</'.$key.'>');
					if($p1 !== false) {
						$ret[$key] = substr($data, $l+1, $p1-$l-1);
						$data = substr($data, $p1+$l+3);
					}
				}
			}

			if( $this->payment_params->debug ) {
				echo print_r($ret, true)."\n\n\n";
			}

			if( isset($ret['ssl_result']) ) {

				if( $ret['ssl_result'] == '0' ) {

					$dbg = ob_get_clean();
					if( !empty($dbg) ) $dbg .= "\r\n";
					ob_start();

					$history = new stdClass();
					$email = new stdClass();

					$history->notified = 0;
					$history->amount = $amount . $this->payment_params->currency;
					$history->data = $dbg . 'Authorization Code: ' . $ret['ssl_approval_code'] . "\r\n" . 'Transaction ID: ' . $ret['ssl_txn_id'];

					$order_status = $this->payment_params->verified_status;

					$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=listing';
					$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE','',HIKASHOP_LIVE);
					$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
					$email->subject = JText::sprintf('PAYMENT_NOTIFICATION','VirtualMerchant','Accepted');
					$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','VirtualMerchant','Accepted')).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order_status)."\r\n\r\n".$order_text;

					$this->modifyOrder($order,$order_status,$history,$email);

					$this->ccClear();

				} else {
					$this->app->enqueueMessage('Error Code #' . $ret['errorCode'] . ': ' . $ret['errorMessage']);
					$do = false;
				}
			} else {
				$this->app->enqueueMessage('An error occurred.');
				$do = false;
			}
		} else {
			$this->app->enqueueMessage('An error occurred. '. $error);
			$do = false;
		}
		return true;
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		$this->removeCart = true;

		return $this->showPage('thanks');
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='VirtualMerchant';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';

		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
	}
}
