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
class plgHikashoppaymentPayJunction extends hikashopPaymentPlugin
{
	var $accepted_currencies = array( 'USD' );
	var $multiple = true;
	var $name = 'payjunction';

	var $error_msg = array(
		'00' => 'Transaction was approved.',
		'85' => 'Transaction was approved.',
		'FE' => 'There was a format error with your Trinity Gateway Service (API) request.',
		'AE' => 'Address verification failed because address did not match.',
		'ZE' => 'Address verification failed because zip did not match.',
		'XE' => 'Address verification failed because zip and address did not match.',
		'YE' => 'Address verification failed because zip and address did not match.',
		'OE' => 'Address verification failed because address or zip did not match..',
		'UE' => 'Address verification failed because cardholder address unavailable.',
		'RE' => 'Address verification failed because address verification system is not working',
		'SE' => 'Address verification failed because address verification system is unavailable',
		'EE' => 'Address verification failed because transaction is not a mail or phone order.',
		'GE' => 'Address verification failed because international support is unavailable.',
		'CE' => 'Declined because CVV2/CVC2 code did not match.',
		'NL' => 'Aborted because of a system error, please try again later.',
		'AB' => 'Aborted because of an upstream system error, please try again later.',
		'04' => 'Declined. Pick up card.',
		'07' => 'Declined. Pick up card (Special Condition).',
		'41' => 'Declined. Pick up card (Lost).',
		'43' => 'Declined. Pick up card (Stolen).',
		'13' => 'Declined because of the amount is invalid.',
		'14' => 'Declined because the card number is invalid.',
		'80' => 'Declined because of an invalid date.',
		'05' => 'Declined. Do not honor.',
		'51' => 'Declined because of insufficient funds.',
		'N4' => 'Declined because the amount exceeds issuer withdrawal limit.',
		'61' => 'Declined because the amount exceeds withdrawal limit.',
		'62' => 'Declined because of an invalid service code (restricted).',
		'65' => 'Declined because the card activity limit exceeded.',
		'93' => 'Declined because there a violation (the transaction could not be completed).',
		'06' => 'Declined because address verification failed.',
		'54' => 'Declined because the card has expired.',
		'15' => 'Declined because there is no such issuer.',
		'96' => 'Declined because of a system error.',
		'N7' => 'Declined because of a CVV2/CVC2 mismatch.',
		'M4' => 'Declined.',
		'DT' => 'Duplicate Transaction'
	);


	function needCC(&$method) {
		$method->ask_cc = true;
		$method->ask_owner = true;
		if( $method->payment_params->ask_ccv || ($method->payment_params->security && $method->payment_params->security_cvv) ) {
			$method->ask_ccv = true;
		}
		return true;
	}


	function onBeforeOrderCreate(&$order, &$do) {
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;

		$this->ccLoad();

		ob_start();
		$dbg = '';

		$uuid = uniqid('');

		$amount = number_format($order->cart->full_total->prices[0]->price_value_with_tax,2,'.','');

		$vars = array (
			"dc_logon" => $this->payment_params->login
			,"dc_password" => $this->payment_params->password
			,"dc_version" => "1.2"
			,"dc_transaction_type" => "AUTHORIZATION_CAPTURE"
			,"dc_transaction_amount" => $amount
			,"dc_address" => @$order->cart->billing_address->address_street
			,"dc_city" => @$order->cart->billing_address->address_city
			,"dc_state" => @$order->cart->billing_address->address_state->zone_name
			,"dc_zipcode" => @$order->cart->billing_address->address_post_code
			,"dc_name" => $this->cc_owner
			,"dc_number" => $this->cc_number
			,"dc_expiration_month" => $this->cc_month
			,"dc_expiration_year" => $this->cc_year
			,"dc_verification_number" => $this->cc_CCV

			,"dc_schedule_create" => ''
			,"dc_schedule_limit" => ''
			,"dc_schedule_periodic_number" => ''
			,"dc_schedule_periodic_type" => ''
			,"dc_schedule_start" => ''
			,"dc_transaction_id" => ''
		);

		if( $this->payment_params->security ) {
			$vars['dc_security'] = $this->payment_params->security_avs . '|' .
				($this->payment_params->security_cvv?'M':'I') . '|' .
				($this->payment_params->security_preauth?'true':'false') . '|' .
				($this->payment_params->security_avsforce?'true':'false') . '|' .
				($this->payment_params->security_cvvforce?'true':'false') ;
		}

		$tmp = array();
		foreach($vars as $k => $v) {
			$tmp[] = $k . '=' . urlencode(trim($v));
		}
		$vars = implode('&', $tmp);

		$session = curl_init();
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($session, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($session, CURLOPT_POST,           1);
		curl_setopt($session, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);

		$httpsHikashop = str_replace('http://','https://', HIKASHOP_LIVE);
		$domain = $this->payment_params->domain;
		$url = '/quick_link';

		curl_setopt($session, CURLOPT_URL, 'https://' . $domain . $url);
		curl_setopt($session, CURLOPT_POSTFIELDS, $vars);

		$ret = curl_exec($session);
		$error = curl_errno($session);
		$err_msg = curl_error($session);

		curl_close($session);

		if( !empty($ret) ) {

			$ret = explode(chr(28), $ret);
			$result = array();
			if( is_array($ret) ) {
				foreach ($ret as $kv) {
					list ($k, $v) = explode("=", $kv);
					$result[$k] = $v;
				}
			}

			if( $this->payment_params->debug ) {
				echo print_r($result, true) . "\n\n\n";
			}

			if( isset($result['dc_response_code']) ) {

				$rc = $result['dc_response_code'];

				if( $rc == '00' || $rc == '85' ) {

					$do = true;

					$dbg .= ob_get_clean();
					if( !empty($dbg) ) $dbg .= "\r\n";
					ob_start();

					$history = new stdClass();
					$history->notified = 0;
					$history->amount = $amount . $this->accepted_currencies[0];
					$history->data = $dbg . 'Authorization Code: ' . @$result['dc_approval_code'] . "\r\n" . 'Transaction ID: ' . @$result['dc_transaction_id'];

					$this->modifyOrder($order,$this->payment_params->verified_status,$history,true);

				} else {
					if( isset($this->error_msg[$rc]) ) {
						$this->app->enqueueMessage($this->error_msg[$rc]);
					} else {
						$this->app->enqueueMessage('Error');
					}
					if( isset($result['dc_response_message']) ) {
						$this->app->enqueueMessage( $result['dc_response_message'] );
					}
					$do = false;
				}
			} else {
				$this->app->enqueueMessage('An error occurred.');
				$do = false;
			}
		} else {
			$do = false;
		}

		if( $error != 0 ) {
			$this->app->enqueueMessage('There was an error during the connection with the PayJunction payment gateway');
			if( $this->payment_params->debug ) {
				echo 'Curl Err [' . $error . '] : ' . $err_msg . "\n\n\n";
			}
		}

		$dbg .= ob_get_clean();
		$this->writeToLog($dbg);

		if( $error != 0 ) {
			return true;
		}

		$this->ccClear();

		return true;
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id){

		return $this->showPage('thanks');

	}

	function onPaymentConfigurationSave(&$element){
		if( isset($element->payment_params->security) && $element->payment_params->security && isset($element->payment_params->security_cvv) && $element->payment_params->security_cvv ) {
			$element->payment_params->ask_ccv = true;
		}
		return true;
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='PayJunction';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';

		$element->payment_params->login='';
		$element->payment_params->password='';
		$element->payment_params->ask_ccv = true;
		$element->payment_params->security = false;
		$element->payment_params->domain='www.payjunction.com';
		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
	}
}
