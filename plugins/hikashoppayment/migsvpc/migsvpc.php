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
class plgHikashoppaymentMigsvpc extends hikashopPaymentPlugin
{
	var $accepted_currencies = array('AUD');
	var $multiple = true;
	var $name = 'migsvpc';
	var $pluginConfig = array(
		'url' => array('URL', 'input'),
		'vpc_mode' => array('VPC Mode', 'list',array(
			'dps' => 'HIKA_HOSTED',
			'pay' => 'HIKA_REDIRECT'
		)),
		'merchant_id' => array('MERCHANT_ID', 'input'),
		'locale' => array('Locale', 'input'),
		'access_code' => array('ACCESS_CODE', 'input'),
		'secure_secret' => array('SECURE_SECRET', 'input'),
		'ticket_info' => array('Display payment information in Redirect mode', 'boolean','0'),
		'currency' => array('CURRENCY', 'input'),
		'ask_ccv' => array('Ask CCV', 'boolean','0'),
		'debug' => array('DEBUG', 'boolean','0'),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function needCC(&$method) {
		if(empty($method->payment_params->vpc_mode) || $method->payment_params->vpc_mode == 'dps') {
			$method->ask_cc = true;
			$method->ask_owner = false;
			if( $method->payment_params->ask_ccv || (!empty($method->payment_params->security) && !empty($method->payment_params->security_cvv)) ) {
				$method->ask_ccv = true;
			}
		}
	}

	function onBeforeOrderCreate(&$order, &$do) {
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;

		if(!empty($this->payment_params->vpc_mode) && $this->payment_params->vpc_mode != 'dps')
			return true;

		if(!function_exists('curl_init')){
			$this->app->enqueueMessage('The MIGS payment plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			return false;
		}

		$this->ccLoad();

		if(!empty($this->payment_params->currency))
			$this->accepted_currencies = array( strtoupper($this->payment_params->currency) );

		ob_start();
		$dbg = '';

		$amount = round($order->cart->full_total->prices[0]->price_value_with_tax * 100);
		$order_id = uniqid('');
		$uuid = $order_id.'-1';

		$vars = array(
			'vpc_Version' => '1',
			'vpc_Command' => 'pay',
			'vpc_AccessCode' => $this->payment_params->access_code,
			'vpc_MerchTxnRef' => $uuid,
			'vpc_Merchant' => $this->payment_params->merchant_id,
			'vpc_OrderInfo' => $order_id,
			'vpc_Amount' => $amount,
			'vpc_CardNum' => $this->cc_number,
			'vpc_CardExp' => $this->cc_year.$this->cc_month
		);
		if($this->payment_params->ask_ccv) {
			$vars['vpc_CardSecurityCode'] = $this->cc_CCV;
		}

		$postdata = array();
		foreach($vars as $k => $v) {
			$postdata[] = urlencode($k).'='.urlencode($v);
		}
		$postdata = implode('&', $postdata);

		$httpsHikashop = str_replace('http://','https://', HIKASHOP_LIVE);

		$url = 'https://migs.mastercard.com.au/vpcdps';

		if(!empty($this->payment_params->url)){
			$url = rtrim($this->payment_params->url, '/');
			if(strpos($url,'http')===false)
				$url='https://'.$url;
		}

		$session = curl_init($url);
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($session, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($session, CURLOPT_VERBOSE, 1);
		curl_setopt($session, CURLOPT_POST, 1);
		curl_setopt($session, CURLOPT_POSTFIELDS, $postdata);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);

		$ret = curl_exec($session);
		$error = curl_errno($session);
		$err_msg = curl_error($session);

		curl_close($session);

		if( !empty($ret) ) {

			if( $this->payment_params->debug ) {
				echo print_r($ret, true) . "\n\n\n";
			}

			$result = 0;
			if( strpos($ret, '&') !== false ) {
				$res = explode('&', $ret);
				$ret = array();
				foreach($res as $r) {
					list($k,$v) = explode('=',$r,2);
					$ret[urldecode($k)] = urldecode($v);
				}

				$result = 1;
				$errorMsg = '';
				if( $ret['vpc_TxnResponseCode'] == 0 || $ret['vpc_TxnResponseCode'] == '0' ) {
					$result = 2;
				} else {
					$errorMsg = $this->getResponseMessage($ret['vpc_TxnResponseCode']);
				}
				$transactionId = @$ret['vpc_TransactionNo'];
				$approvalCode = @$ret['vpc_AuthorizeId'];
				$responseMsg = $ret['vpc_Message'];
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
					$history->amount = number_format($order->cart->full_total->prices[0]->price_value_with_tax,2,'.','') . $this->accepted_currencies[0];
					$history->data = $dbg . 'Authorization Code: ' . @$approvalCode . "\r\n" . 'Transaction ID: ' . @$transactionId;

					$order_status = $this->payment_params->verified_status;

					$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=listing';
					$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE','',HIKASHOP_LIVE);
					$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
					$email->subject = JText::sprintf('PAYMENT_NOTIFICATION','MIGS','Accepted');
					$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','MIGS','Accepted')).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order_status)."\r\n\r\n".$order_text;

					$this->modifyOrder($order,$order_status,$history,$email);

				} else {
					if( !empty($responseMsg) ) {
						$this->app->enqueueMessage($responseMsg);
					} else {
						$this->app->enqueueMessage('Error');
					}
					if( !empty($errorMsg) ) {
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
			$this->app->enqueueMessage('There was an error during the connection with the MIGS payment gateway');
			if( $this->payment_params->debug ) {
				echo 'Curl Err [' . $error . '] : ' . $err_msg . "\n\n\n";
			}
		}


		$this->writeToLog($dbg);

		if( $error != 0 ) {
			return true;
		}

		$this->ccClear();

		return true;
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		if(!empty($this->payment_params->vpc_mode) && $this->payment_params->vpc_mode == 'pay') {
			return $this->onAfterOrderConfirm_VPCPAY($order, $methods, $method_id);
		}

		$this->removeCart = true;
		return $this->showPage('thankyou');
	}

	function onAfterOrderConfirm_VPCPAY(&$order,&$methods,$method_id){

		$amount = round($order->cart->full_total->prices[0]->price_value_with_tax * 100);
		$uuid = $order->order_id.'-'.uniqid('');

		$return_url = HIKASHOP_LIVE.'migsvpc_return.php';

		if(empty($this->payment_params->locale))
			$this->payment_params->locale = 'en';

		$this->vars = array(
			'vpc_Version' => '1',
			'vpc_Command' => 'pay',
			'vpc_MerchTxnRef' => $uuid,
			'vpc_AccessCode' => $this->payment_params->access_code,
			'vpc_Merchant' => $this->payment_params->merchant_id,
			'vpc_OrderInfo' => $order->order_id,
			'vpc_Locale' => $this->payment_params->locale,
			'vpc_Amount' => $amount,
			'vpc_ReturnURL' => $return_url,
		);

		ksort($this->vars);
		$this->vars['vpc_SecureHash'] = md5($this->payment_params->secure_secret . implode('', $this->vars));

		foreach($this->vars as $key => &$var) {
			$var = $key . '=' . urlencode($var);
		}
		unset($var);

		if(empty($this->payment_params->url))
			$this->payment_params->url = 'https://migs.mastercard.com.au/vpcpay';

		$this->app->redirect($this->payment_params->url . '?' . implode('&', $this->vars));
	}

	function onPaymentNotification(&$statuses){
		$vars = array();
		$filter = JFilterInput::getInstance();
		foreach($_REQUEST as $key => $value) {
			$key = $filter->clean($key);
			if(substr($key, 0, 4) == 'vpc_') {
				$value = JRequest::getString($key);
				$vars[$key] = $value;
			}
		}

		$return_hash = $vars['vpc_SecureHash'];
		unset($vars['vpc_SecureHash']);

		$order_id = (int)$vars['vpc_OrderInfo'];
		$dbOrder = $this->getOrder($order_id);

		if(empty($dbOrder)) {
			echo 'Could not load any order for your notification '.$order_id;
			return false;
		}

		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);
		if($this->payment_params->debug){
			echo print_r($vars,true)."\n\n\n";
			echo print_r($dbOrder,true)."\n\n\n";
		}

		if(empty($this->payment_params->vpc_mode) || $this->payment_params->vpc_mode != 'pay') {
			return false;
		}

		ksort($vars);
		$hash = $this->payment_params->secure_secret;
		foreach($vars as $var) {
			$hash .= $var;
		}

		if(strtolower($return_hash) != strtolower(md5($hash))) {
			echo 'Invalid hash';
			return false;
		}

		$return_url = hikashop_completeLink('checkout&task=after_end&order_id='.$order_id.$this->url_itemid);
		$cancel_url = hikashop_completeLink('order&task=cancel_order&order_id='.$order_id.$this->url_itemid);

		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order_id.$this->url_itemid;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));

		$history = new stdClass();
		$email = new stdClass();
		$history->notified = 0;
		$history->amount = $vars['vpc_Amount'];
		$history->data = $vars['vpc_TransactionNo'] . "\r\n\r\n" . ob_get_clean();

		$orderPrice = round($dbOrder->order_full_price * 100);
		$orderstatus = '';
		if($orderPrice != $vars['vpc_Amount']) {
			$order_status = $this->payment_params->invalid_status;

			$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','MIGS').JText::_('INVALID_AMOUNT');
			$email->body = str_replace('<br/>',"\r\n",JText::sprintf('AMOUNT_RECEIVED_DIFFERENT_FROM_ORDER','MIGS', $history->amount, $orderPrice . $this->currency->currency_code))."\r\n\r\n".$order_text;

			$this->modifyOrder($order_id,$order_status,$history,$email);

			$this->app->enqueueMessage(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','MIGS').JText::_('INVALID_AMOUNT'));
			$this->app->redirect($cancel_url);
		}

		$completed = ($vars['vpc_TxnResponseCode'] == '0');
		$payment_status = '';
		$redirect_to = $return_url;
		if($completed) {
			$order_status = $this->payment_params->verified_status;
			$history->notified = 1;
			$payment_status = 'confirmed';
		} else {
			$order_status = $this->payment_params->invalid_status;
			$payment_status = 'cancelled';
			$this->app->enqueueMessage(JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','MIGS'));
			$redirect_to = $cancel_url;
		}
		$mail_status = $statuses[$order->order_status];

		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER', 'MIGS', $payment_status, $dbOrder->order_number);
		$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','MIGS',$payment_status)).' '.JText::sprintf('ORDER_STATUS_CHANGED',$mail_status)."\r\n\r\n".$order_text;

		$this->modifyOrder($order_id,$order_status,$history,$email);

		if(@$this->element->payment_params->ticket_info){
			$key = 'TICKET_INFO';
			$currencyClass = hikashop_get('class.currency');
			if(JText::_($key) != $key) {
				$text = JText::sprintf($key, $vars['vpc_AuthorizeId'], $currencyClass->format($dbOrder->order_full_price,$dbOrder->order_currency_id));
			}else{
				$text = sprintf('Your authorization number is %s for the payment of %s.', $vars['vpc_AuthorizeId'], $currencyClass->format($dbOrder->order_full_price,$dbOrder->order_currency_id));
			}
			$this->app->enqueueMessage($text);
		}

		$this->app->redirect($redirect_to);
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='MIGS';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';

		$element->payment_params->merchant_id = '';
		$element->payment_params->access_code = '';
		$element->payment_params->ask_ccv = true;
		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
	}

	function onPaymentConfigurationSave(&$element){
		$app = JFactory::getApplication();
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.path');
		$lang = JFactory::getLanguage();
		$locale = strtolower(substr($lang->get('tag'),0,2));

		$migsvpc_file='<?php
	$_GET[\'option\']=\'com_hikashop\';
	$_GET[\'tmpl\']=\'component\';
	$_GET[\'ctrl\']=\'checkout\';
	$_GET[\'task\']=\'notify\';
	$_GET[\'notif_payment\']=\'migsvpc\';
	$_GET[\'format\']=\'html\';
	$_GET[\'lang\']=\''.$locale.'\';
	$_REQUEST[\'option\']=\'com_hikashop\';
	$_REQUEST[\'tmpl\']=\'component\';
	$_REQUEST[\'ctrl\']=\'checkout\';
	$_REQUEST[\'task\']=\'notify\';
	$_REQUEST[\'notif_payment\']=\'migsvpc\';
	$_REQUEST[\'format\']=\'html\';
	$_REQUEST[\'lang\']=\''.$locale.'\';
	include(\'index.php\');
';
		JFile::write(JPATH_ROOT.DS.'migsvpc_return.php', $migsvpc_file);

		return true;
	}

	function getResponseMessage($code) {
		switch ($code) {
			case '0': return 'Transaction Successful';
			case '?': return 'Transaction status is unknown';
			case '1': return 'Unknown Error';
			case '2': return 'Bank Declined Transaction';
			case '3': return 'No Reply from Bank';
			case '4': return 'Expired Card';
			case '5': return 'Insufficient funds';
			case '6': return 'Error Communicating with Bank';
			case '7': return 'Payment Server System Error';
			case '8': return 'Transaction Type Not Supported';
			case '9': return 'Bank declined transaction (Do not contact Bank)';
			case 'A': return 'Transaction Aborted';
			case 'C': return 'Transaction Cancelled';
			case 'D': return 'Deferred transaction has been received and is awaiting processing';
			case 'F': return '3D Secure Authentication failed';
			case 'I': return 'Card Security Code verification failed';
			case 'L': return 'Shopping Transaction Locked (Please try the transaction again later)';
			case 'N': return 'Cardholder is not enrolled in Authentication scheme';
			case 'P': return 'Transaction has been received by the Payment Adaptor and is being processed';
			case 'R': return 'Transaction was not processed - Reached limit of retry attempts allowed';
			case 'S': return 'Duplicate SessionID (OrderInfo)';
			case 'T': return 'Address Verification Failed';
			case 'U': return 'Card Security Code Failed';
			case 'V': return 'Address Verification and Card Security Code Failed';
		}
		return 'Unable to be determined';
	}
}
