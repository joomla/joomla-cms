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
class plgHikashoppaymentInnovativegateway extends hikashopPaymentPlugin
{
	var $accepted_currencies = array( 'USD' );
	var $name = 'innovativegateway';
	var $multiple = true;

	var $pluginConfig = array(
		 'login' => array('HIKA_LOGIN', 'input'),
		 'password' => array('API HIKA_PASSWORD', 'input'),
		 'ask_ccv' => array('CARD_VALIDATION_CODE', 'boolean','0'),
		 'ask_cctype' => array('CARD_TYPE', 'big-textarea'),
		 'debug' => array('DEBUG', 'boolean','0'),
		 'cancel_url' => array('CANCEL_URL', 'input'),
		 'return_url' => array('RETURN_URL', 'input'),
		 'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function needCC(&$method) {
		$method->ask_cc = true;
		$method->ask_owner = true;
		if(!empty($method->payment_params->ask_cctype)){
			$cctypes = explode("\n",str_replace(array("\r\n","\r"),array("\n","\n"),$method->payment_params->ask_cctype));
			$method->ask_cctype = array();
			foreach($cctypes as $cctype){
				$row = explode('=',$cctype,2);
				$method->ask_cctype[trim($row[0])] = trim($row[1]);
			}
		}else{
			$method->ask_cctype = array('visa' => 'VISA', 'mc' => 'MasterCard', 'amex' => 'American Express', 'diners' => 'Diners', 'discover' => 'Discover', 'jcb' => 'JCB' );
		}

		if( $method->payment_params->ask_ccv || ($method->payment_params->security && $method->payment_params->security_cvv) ) {
			$method->ask_ccv = true;
		}
		return true;
	}

	function onBeforeOrderCreate(&$order, &$do) {
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;

		if(!function_exists('curl_init')){
			$this->app->enqueueMessage('The Innovative Gateway payment plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			$do = false;
			return false;
		}

		$this->ccLoad();

		ob_start();
		$dbg = '';

		$vars = array();
		$vars['target_app'] = 'WebCharge_v5.06';
		$vars['response_mode'] = 'simple';
		$vars['response_fmt'] = 'delimited';
		$vars['upg_auth'] = 'zxcvlkjh';
		$vars['delimited_fmt_field_delimiter'] = '=';
		$vars['delimited_fmt_include_fields'] = 'true';
		$vars['delimited_fmt_value_delimiter'] = '|';

		$vars['username'] = $this->payment_params->login;
		$vars['pw'] = $this->payment_params->password;

		if( $vars['username'] == 'gatewaytest' )
			$vars['test_override_errors'] = 'yes';

		$vars['trantype'] = 'sale'; // Options:  preauth, postauth, sale, credit, void
		$vars['reference'] = ''; // Blank for new sales..
		$vars['trans_id'] = ''; // Blank for new sales...
		$vars['authamount'] = ''; // Only valid for POSTAUTH and is equal to the original preauth amount.
		$vars['cardtype'] = !empty($this->cc_type)?$this->cc_type:'visa';

		$vars['ccnumber'] = $this->cc_number; // Credit Card information

		if( $this->payment_params->ask_ccv ) {
			$vars['ccidentifier1'] = $this->cc_CCV;
		}

		$vars['month'] = $this->cc_month; // Must be TWO DIGIT month.
		$vars['year'] =  $this->cc_year; // Must be TWO or FOUR DIGIT year.
		$vars['ccname'] = $this->cc_owner;

		$vars['fulltotal'] = number_format($order->cart->full_total->prices[0]->price_value_with_tax,2,'.',''); // Total amount WITHOUT dollar sign.

		$vars['baddress'] = @$order->cart->billing_address->address_street;
		$vars['baddress1'] = '';
		$vars['bcity'] = @$order->cart->billing_address->address_city;
		$vars['bstate'] = @$order->cart->billing_address->address_state->zone_code_3;
		$vars['bzip'] = @$order->cart->billing_address->address_post_code;
		$vars['bcountry'] = @$order->cart->billing_address->address_country->zone_code_2; // TWO DIGIT COUNTRY (United States = 'US')
		$vars['email'] = $this->user->user_email;

		$domain = 'transactions.innovativegateway.com';
		$url = '/servlet/com.gateway.aai.Aai';

		if( $this->payment_params->debug ) {
			echo print_r($vars, true) . "\n\n\n";
		}

		$data = '';
		foreach ($vars as $k => $v) {
			if( $data != '' )
				$data .= '&';
			$data .= $k . "=" . urlencode($v);
		}

		$session = curl_init('https://' . $domain . $url);
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($session, CURLOPT_VERBOSE, 1);
		curl_setopt($session, CURLOPT_POST, 1);
		curl_setopt($session, CURLOPT_POSTFIELDS, $data);
		curl_setopt($session, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($session, CURLOPT_TIMEOUT, 120);

		$ret = curl_exec($session);
		$error = curl_errno($session);
		$err_msg = curl_error($session);

		curl_close($session);

		if( !empty($ret) ) {

			if( $this->payment_params->debug ) {
				echo print_r($ret, true) . "\n\n\n";
			}

			$result = explode('|',$ret);
			$ret = array();
			foreach($result as $v) {
				if( !empty($v) ) {
					$t = explode('=', $v, 2);
					if( isset($t[1]) )
						$ret[strtolower($t[0])] = strip_tags($t[1]);
					else
						$ret[strtolower($t[0])] = '';
				}
			}

			if( !empty($ret['approval']) ) {

				$do = true;

				$dbg .= ob_get_clean();
				if( !empty($dbg) ) $dbg .= "\r\n";
				ob_start();

				$this->modifyOrder($order, $this->payment_params->verified_status, true, true);


			} else {
				if( isset($ret['error']) ) {
					$this->app->enqueueMessage($ret['error']);
				} else {
					$this->app->enqueueMessage('Error');
				}
				$do = false;
			}
		} else {
			$do = false;
		}

		if( $error != 0 ) {
			$this->app->enqueueMessage('There was an error during the connection with the Innovative Gateway payment gateway');
			if( $this->payment_params->debug ) {
				echo 'Curl Err [' . $error . '] : ' . $err_msg . "\n\n\n";
			}
		}

		$this->writeToLog($data);


		if( $error != 0 ) {
			return true;
		}

		$this->ccClear();

		return true;
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		return $this->showPage('thanks');
	}


	function getPaymentDefaultValues(&$element) {
		$element->payment_name='Innovative Gateway';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';

		$element->payment_params->login='';
		$element->payment_params->password='';
		$element->payment_params->ask_ccv = true;
		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
		$element->payment_params->ask_cctype = "visa=VISA\nmc=MasterCard\namex=American Express\ndiners=Diners\ndiscover=Discover\njcb=JCB";
	}
}
