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
class plgHikashoppaymentFirstdata extends hikashopPaymentPlugin
{
	var $accepted_currencies = array( 'USD' );
	var $multiple = true;
	var $name = 'firstdata';
	var $pluginConfig = array(
		'login' => array('Store ID', 'input'),
		'password' => array('API Password', 'input'),
		'domain' => array('Payment Server', 'list',array(
			'ws.firstdataglobalgateway.com' => 'Production Server',
			'ws.merchanttest.firstdataglobalgateway.com' => 'Test Server'
		)),
		'pem_file' => array('PEM file', 'input'),
		'key_file' => array('KEY file', 'input'),
		'key_passwd' => array('KEY password', 'input'),
		'ask_ccv' => array('Ask CCV', 'boolean','1'),
		'debug' => array('DEBUG', 'boolean','0'),
		'return_url' => array('RETURN_URL', 'input'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
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

		if(!function_exists('curl_init')){
			$this->app->enqueueMessage('The First Data payment plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			return false;
		}

		$this->ccLoad();

		ob_start();
		$dbg = '';

		$amount = number_format($order->cart->full_total->prices[0]->price_value_with_tax,2,'.','');

		$vars = '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' . "\r\n" . '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"><SOAP-ENV:Header /><SOAP-ENV:Body>';

		$vars .= '<fdggwsapi:FDGGWSApiOrderRequest xmlns:v1="http://secure.linkpt.net/fdggwsapi/schemas_us/v1"  xmlns:fdggwsapi="http://secure.linkpt.net/fdggwsapi/schemas_us/fdggwsapi">';
		$vars .= '<v1:Transaction><v1:CreditCardTxType><v1:Type>sale</v1:Type></v1:CreditCardTxType><v1:CreditCardData><v1:CardNumber>';
		$vars .= $this->cc_number;
		$vars .= '</v1:CardNumber><v1:ExpMonth>'. $this->cc_month .'</v1:ExpMonth>';
		$vars .= '<v1:ExpYear>' . substr($this->cc_year, -2) . '</v1:ExpYear>';
		if( $this->payment_params->ask_ccv ) {
			$vars .= '<v1:CardCodeValue>' . $this->cc_CCV . '</v1:CardCodeValue>';
		}
		$vars .= '</v1:CreditCardData><v1:Payment><v1:ChargeTotal>' . $amount . '</v1:ChargeTotal></v1:Payment>';
		$vars .= '<v1:TransactionDetails><v1:UserID>'. $this->user->user_id .'</v1:UserID></v1:TransactionDetails>';
		$vars .= '<v1:Billing><v1:Name>'. $this->cc_owner .'</v1:Name><v1:Address1>'.
			@$order->cart->billing_address->address_street .'</v1:Address1><v1:City>'.
			@$order->cart->billing_address->address_city.'</v1:City><v1:State>'.
			@$order->cart->billing_address->address_state->zone_name.'</v1:State><v1:Zip>'.
			@$order->cart->billing_address->address_post_code.'</v1:Zip><v1:Country>'.
			@$order->cart->billing_address->address_country->zone_name.'</v1:Country></v1:Billing>';
		$vars .= '</v1:Transaction></fdggwsapi:FDGGWSApiOrderRequest>';

		$vars .= '</SOAP-ENV:Body></SOAP-ENV:Envelope>';

		$credentials = 'WS'.$this->payment_params->login . '._.1:' . $this->payment_params->password;
		$httpsHikashop = str_replace('http://','https://', HIKASHOP_LIVE);
		$domain = rtrim($this->payment_params->domain, '/'); // ws.firstdataglobalgateway.com
		$url = '/fdggwsapi/services/order.wsdl';

		$session = curl_init('https://' . $domain . $url);
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($session, CURLOPT_VERBOSE, 1);
		curl_setopt($session, CURLOPT_POST, 1);
		curl_setopt($session, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
		curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($session, CURLOPT_USERPWD, $credentials);
		curl_setopt($session, CURLOPT_POSTFIELDS, $vars);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($session, CURLOPT_SSLCERT, $this->payment_params->pem_file);
		curl_setopt($session, CURLOPT_SSLKEY, $this->payment_params->key_file);
		curl_setopt($session, CURLOPT_SSLKEYPASSWD, $this->payment_params->key_passwd);

		$ret = curl_exec($session);
		$error = curl_errno($session);
		$err_msg = curl_error($session);

		curl_close($session);

		if( !empty($ret) ) {

			if( $this->payment_params->debug ) {
				echo print_r($ret, true) . "\n\n\n";
			}

			$result = 0;
			if( strpos($ret, '<fdggwsapi:FDGGWSApiOrderResponse') !== false ) {
				$result = 1;

				if( preg_match('#<fdggwsapi:TransactionResult>(.*)</fdggwsapi:TransactionResult>#iU', $ret, $res) ) {
					$resultMsg = strtoupper(trim($res[1]));
					if($resultMsg == 'APPROVED') {
						$result = 2;
					}
				}
				if( $result ) {
					if( preg_match('#<fdggwsapi:TransactionID>(.*)</fdggwsapi:TransactionID>#iU', $ret, $res) ) {
						$transactionId = trim($res[1]);
					}
					if( preg_match('#<fdggwsapi:ApprovalCode>(.*)</fdggwsapi:ApprovalCode>#iU', $ret, $res) ) {
						$approvalCode = trim($res[1]);
					}
				}
				if( preg_match('#<fdggwsapi:ErrorMessage>(.*)</fdggwsapi:ErrorMessage>#iU', $ret, $res) ) {
					$errorMsg = trim($res[1]);
				}
				if( preg_match('#<fdggwsapi:AuthenticationResponseCode>(.*)</fdggwsapi:AuthenticationResponseCode>#iU', $ret, $res) ) {
					$responseMsg = trim($res[1]);
				}
			}

			if( $result > 0 ) {

				if( $result == 2 ) {

					$do = true;

					$dbg .= ob_get_clean();
					if( !empty($dbg) ) $dbg .= "\r\n";
					ob_start();

					$history = new stdClass();
					$email = new stdClass();

					$history->notified = 0;
					$history->amount = $amount . $this->accepted_currencies[0];
					$history->data = $dbg . 'Authorization Code: ' . @$approvalCode . "\r\n" . 'Transaction ID: ' . @$transactionId;

					$order_status = $this->payment_params->verified_status;

					$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE','',HIKASHOP_LIVE);
					$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
					$email->subject = JText::sprintf('PAYMENT_NOTIFICATION','First Data','Accepted');
					$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','First Data','Accepted')).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order_status)."\r\n\r\n".$order_text;

					$this->modifyOrder($order,$order_status,$history,$email);

				} else {
					if( isset($responseMsg) ) {
						$this->app->enqueueMessage($responseMsg);
					} else {
						$this->app->enqueueMessage('Error');
					}
					if( isset($errorMsg) ) {
						$this->app->enqueueMessage($errorMsg);
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
			$this->app->enqueueMessage('There was an error during the connection with the First Data payment gateway');
			if( $this->payment_params->debug ) {
				$this->app->enqueueMessage('Curl Err [' . $error . '] : ' . $err_msg );
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
		$this->removeCart = true;
		return $this->showPage('thanks');
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='FirstData';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';

		$element->payment_params->login='';
		$element->payment_params->password='';
		$element->payment_params->ask_ccv = true;
		$element->payment_params->cert = false;
		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
	}
}
