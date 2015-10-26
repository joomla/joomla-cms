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
class plgHikashoppaymentIpaydna extends hikashopPaymentPlugin
{
	var $accepted_currencies = array( 'USD', 'EUR', 'GBP', 'JPY', 'MYR', 'AUD', 'CAD', 'SGD', 'DKK', 'SEK', 'NOK', 'HKD', 'KRW' );
	var $multiple = true;
	var $name = 'ipaydna';
	var $pluginConfig = array(
		'tid' => array('Store ID', 'input'),
		'url' => array('iPayDNA url', 'input'),
		'currency' => array('Account Currency', 'list',array(
			'' => 'ALL',
			'USD' => 'USD',
			'EUR' => 'EUR',
			'GBP' => 'GBP',
			'JPY' => 'JPY',
			'MYR' => 'MYR',
			'AUD' => 'AUD',
			'CAD' => 'CAD',
			'SGD' => 'SGD',
			'DKK' => 'DKK',
			'SEK' => 'SEK',
			'NOK' => 'NOK',
			'HKD' => 'HKD',
			'KRW' => 'KRW'
		)),
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
			$method->ask_cctype = array('VISA' => 'VISA', 'MASTERCARD' => 'MasterCard', 'AMEX' => 'American Express', 'DISCOVER' => 'Discover', 'JCB' => 'JCB', 'AQUARIUS' => 'Aquarius' );
		}

		if( $method->payment_params->ask_ccv ) {
			$method->ask_ccv = true;
		}
		return true;
	}

	function onBeforeOrderCreate(&$order, &$do) {
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;

		if( !in_array($this->currency->currency_code, $this->accepted_currencies) ) {
			$app->enqueueMessage('The iPayDNA payment plugin doest not support your currency: &quot;'.htmlentities($this->currency->currency_code).'&quot;','error');
			return false;
		}

		$this->ccLoad();

		$amount = number_format($order->cart->full_total->prices[0]->price_value_with_tax,2,'.','');
		if( !empty($this->payment_params->currency) ) {
			$db = JFactory::getDBO();
			$db->setQuery("SELECT currency_id as `id` FROM #__hikashop_currency WHERE currency_code='".$this->payment_params->currency."';");
			$dstCurrency = $db->loadObjectList();

			if( isset($dstCurrency) && @$dstCurrency[0]->id > 0 ) {
				if( $dstCurrency[0]->id != $order->order_currency_id ) {
					$currencyClass = hikashop_get('class.currency');
					$price = $currencyClass->convertUniquePrice($order->cart->full_total->prices[0]->price_value_with_tax, $order->order_currency_id, $dstCurrency[0]->id);
					$dstCurrencies = null;
					$dstCurrencies = $currencyClass->getCurrencies($dstCurrency[0]->id,$dstCurrencies);
					$tmpCurrency = $dstCurrencies[$dstCurrency[0]->id];
					$amount = number_format($price,2,'.','');
					$currency = $tmpCurrency;
				} else {
					$amount = number_format($order->cart->full_total->prices[0]->price_value_with_tax,2,'.','');
				}
			}
		}

		$vars = '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' . "\r\n" .
			'<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" '.
			'xmlns:ns1="http://acquirer.process.training.aquarius" xmlns:xsd="http://www.w3.org/2001/XMLSchema" '.
			'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" '.
			'SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">';

		if( isset($order->order_id) )
			$uuid = $order->order_id;
		else
			$uuid = uniqid('');

		$state = '';
		$state2 = '';
		$vars .='<SOAP-ENV:Body><ns1:payment>'.
			'<customerpaymentpagetext xsi:type="xsd:string">'. $this->payment_params->tid . '</customerpaymentpagetext>'.
			'<orderdescription xsi:type="xsd:string">'. $uuid . '</orderdescription>'.
			'<orderDetail xsi:type="xsd:string">HikaShop order ' . $this->user->user_id . '</orderDetail>'.
			'<currencytext xsi:type="xsd:string">'.$this->currency->currency_code.'</currencytext>'.
			'<purchaseamount xsi:type="xsd:string">'. $amount . '</purchaseamount>'.
			'<taxamount xsi:type="xsd:string">0.00</taxamount>'.
			'<shippingamount xsi:type="xsd:string">0.00</shippingamount>'.
			'<dutyamount xsi:type="xsd:string">0.00</dutyamount>'.
			'<cardholdername xsi:type="xsd:string">'. $this->cc_owner . '</cardholdername>'.
			'<cardno xsi:type="xsd:string">'. $this->cc_number . '</cardno>'.
			'<cardtypetext xsi:type="xsd:string">'. $this->cc_type . '</cardtypetext>'.
			'<securitycode xsi:type="xsd:string">'. $this->cc_CCV . '</securitycode>'.
			'<cardexpiremonth xsi:type="xsd:string">'. $this->cc_month . '</cardexpiremonth>'.
			'<cardexpireyear xsi:type="xsd:string">20'. $this->cc_year . '</cardexpireyear>'.
			'<cardissuemonth xsi:type="xsd:string">0</cardissuemonth>'.
			'<cardissueyear xsi:type="xsd:string">0</cardissueyear>'.
			'<issuername xsi:type="xsd:string"></issuername>'.
			'<firstname xsi:type="xsd:string">'. substr( @$order->cart->billing_address->address_firstname, 0, 100) . '</firstname>'.
			'<lastname xsi:type="xsd:string">'. substr( @$order->cart->billing_address->address_lastname, 0, 100) . '</lastname>'.
			'<company xsi:type="xsd:string"></company>'.
			'<address xsi:type="xsd:string">'. substr($order->cart->billing_address->address_street,0,250) . '</address>'.
			'<city xsi:type="xsd:string">'. substr(@$order->cart->billing_address->address_city, 0, 50) . '</city>'.
			'<state xsi:type="xsd:string">'. $state . '</state>'.
			'<zip xsi:type="xsd:string">'. substr(@$order->cart->billing_address->address_post_code, 0, 50) . '</zip>'.
			'<country xsi:type="xsd:string">'. @$order->cart->billing_address->address_country->zone_code_2 . '</country>'.
			'<email xsi:type="xsd:string">'. substr($this->user->user_email, 0, 250) . '</email>'.
			'<phone xsi:type="xsd:string">0</phone>'.
			'<shipfirstname xsi:type="xsd:string">'. substr( @$order->cart->shipping_address->address_firstname, 0, 100) . '</shipfirstname>'.
			'<shiplastname xsi:type="xsd:string">'. substr( @$order->cart->shipping_address->address_lastname, 0, 100) . '</shiplastname>'.
			'<shipaddress xsi:type="xsd:string">'. substr($order->cart->shipping_address->address_street,0,250) . '</shipaddress>'.
			'<shipcity xsi:type="xsd:string">'. substr(@$order->cart->shipping_address->address_city, 0, 50) . '</shipcity>'.
			'<shipstate xsi:type="xsd:string">'. $state2 . '</shipstate>'.
			'<shipzip xsi:type="xsd:string">'. substr(@$order->cart->shipping_address->address_post_code, 0, 50) . '</shipzip>'.
			'<shipcountry xsi:type="xsd:string">'. @$order->cart->shipping_address->address_country->zone_code_2 . '</shipcountry>'.
			'<cardHolderIP xsi:type="xsd:string">127.0.0.1</cardHolderIP>'.
			'</ns1:payment></SOAP-ENV:Body></SOAP-ENV:Envelope>';

		$url = $this->payment_params->url;

		$header = array(
			'Content-type: text/xml; charset=utf-8',
			'Accept: text/xml',
			'Cache-Control: no-cache',
			'Pragma: no-cache',
			'SOAPAction: ""',
			'Content-length: '.strlen($vars),
		);

		$session = curl_init('https://' . $url);
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($session, CURLOPT_VERBOSE, 1);
		curl_setopt($session, CURLOPT_POST, 1);
		curl_setopt($session, CURLOPT_HTTPHEADER, $header);
		curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($session, CURLOPT_POSTFIELDS, $vars);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);

		$ret = curl_exec($session);
		$error = curl_errno($session);
		$err_msg = curl_error($session);

		curl_close($session);

		if( !empty($ret) ) {

			if( $this->payment_params->debug ) {
				echo print_r($ret, true) . "\n\n\n";
			}

			$result = array();
			if( strpos($ret, 'TRANSACTIONSTATUSTEXT') !== false ) {
				if( preg_match_all('#&lt;var name=\'(.+)\'&gt;&lt;[a-zA-Z]+&gt;(.*)&lt;/[a-zA-Z]+&gt;&lt;/var&gt;#iU', $ret, $res, PREG_SET_ORDER) ) {
					foreach($res as $r) {
						$result[ $r[1] ] = $r[2];
					}
				}
			}

			if( isset($result['TRANSACTIONSTATUSTEXT']) && $result['TRANSACTIONSTATUSTEXT'] == 'SUCCESSFUL' ) {
				$do = true;

				$dbg .= ob_get_clean();
				if( !empty($dbg) ) $dbg .= "\r\n";
				ob_start();

				$history = new stdClass();
				$history->notified = 0;
				$history->amount = $amount . $this->accepted_currencies[0];
				$history->data = $dbg . 'Authorization Code: ' . @$result['AUTHORIZATIONCODE'] . "\r\n" . 'Order Reference: ' . @$result['ORDERREFERENCE'] . "\r\n" . 'Unique ID: ' . $uuid;

				$this->modifyOrder($order, $order->order_status, $history, true);

			} else {
				$errMsg = 'An error occurred.';
				if( !empty($result['ERRORMESSAGE']) ) {
					$errMsg = 'An error occurred: [' . @$result['ERRORCODE'] . '] ' . $result['ERRORMESSAGE'];
				}
				$this->app->enqueueMessage($errMsg);
				$do = false;
			}
		} else {
			$do = false;
		}

		if( $error != 0 ) {
			$this->app->enqueueMessage('There was an error during the connection with the iPayDNA payment gateway');
			if( $this->payment_params->debug ) {
				echo 'Curl Err [' . $error . '] : ' . $err_msg . "\n\n\n";
			}
		}

		$this->writeToLog(null);

		if( $error == 0 ) {
			$this->ccClear();
		}
		return true;
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		$this->showPage('thanks');
	}


	function getPaymentDefaultValues(&$element) {
		$element->payment_name='IPAYDNA';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';

		$element->payment_params->login='';
		$element->payment_params->password='';
		$element->payment_params->ask_ccv = true;
		$element->payment_params->cert = false;
		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
		$element->payment_params->ask_cctype = "VISA=VISA\nMASTERCARD=MasterCard\nAMEX=American Express\nDISCOVER=Discover\nJCB=JCB\nAQUARIUS=Aquarius";
	}
}
