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
class plgHikashoppaymentPaymentexpress extends hikashopPaymentPlugin
{
	var $accepted_currencies = array(
		'CAD','CHF','DKK','EUR','FRF','GBP','HKD','JPY','NZD','SGD','THB',
		'USD','ZAR','AUD','WST','VUV','TOP','SBD','PGK','MYR','KWD','FJD'
	);
	var $multiple = true;
	var $name = 'paymentexpress';
	var $pluginConfig = array(
		'username' => array('DPS Username', 'input'),
		'password' => array('DPS Password', 'input'),
		'ask_ccv' => array('Ask CCV', 'boolean','1'),
		'ask_owner' => array('Ask owner', 'boolean','0'),
		'debug' => array('DEBUG', 'boolean','0'),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function needCC(&$method) {
		$method->ask_cc = true;
		if( $method->payment_params->ask_owner ) {
			$method->ask_owner = true;
		}
		if( $method->payment_params->ask_ccv ) {
			$method->ask_ccv = true;
		}
		return true;
	}

	function onBeforeOrderCreate(&$order, &$do) {
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;

		if(!function_exists('curl_init')){
			$this->app->enqueueMessage('The Payment Express payment plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			return false;
		}

		$this->ccLoad();

		ob_start();
		$dbg = '';

		$amount = number_format($order->cart->full_total->prices[0]->price_value_with_tax,2,'.','');

		$vars = '<Txn>';
		$vars .= '<PostUsername>'.$this->payment_params->username.'</PostUsername>';
		$vars .= '<PostPassword>'.$this->payment_params->password.'</PostPassword>';
		$vars .= '<Amount>'.$amount.'</Amount>';
		$vars .= '<InputCurrency>'.$this->currency->currency_code.'</InputCurrency>';
		if(!empty($this->cc_CCV)){
			$vars .= '<Cvc2>'.$this->cc_CCV.'</Cvc2>';
		}
		if(!empty($this->cc_owner)){
			$vars .= '<CardHolderName>'.$this->cc_owner.'</CardHolderName>';
		}
		$vars .= '<CardNumber>'.$this->cc_number.'</CardNumber>';
		$vars .= '<DateExpiry>'.$this->cc_month.$this->cc_year.'</DateExpiry>';

		$vars .= '<TxnType>Purchase</TxnType>';
		if(empty($order->order_number) && !empty($order->order_id)) $order->order_number = hikashop_encode($order);
		if(!empty($order->order_number)){
			$vars .= '<MerchantReference>'.$order->order_number.'</MerchantReference>';
		}
		$vars .= '</Txn>';

		$domain = 'https://sec.paymentexpress.com/pxpost.aspx';

		$session = curl_init($domain);
		curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($session, CURLOPT_VERBOSE, 1);
		curl_setopt($session,CURLOPT_SSLVERSION,defined('CURL_SSLVERSION_TLSv1') ? CURL_SSLVERSION_TLSv1 : 1);
		curl_setopt($session, CURLOPT_POST, 1);
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

			$error_msg = '';
			if( preg_match('#<Success>([0-9])</Success>#', $ret, $res) !== false && $res[1]) {
				$approvalCode='';
				if( preg_match('#<AuthCode>([0-9]+)</AuthCode>#', $ret, $res) !== false){
					$approvalCode = $res[1];
				}
				$transactionId='';
				if( preg_match('#<TransactionId>([0-9]+)</TransactionId>#', $ret, $res) !== false){
					$transactionId = $res[1];
				}

				$do = true;

				$dbg .= ob_get_clean();
				if( !empty($dbg) ) $dbg .= "\r\n";
				ob_start();

				$history = new stdClass();
				$email = new stdClass();

				$history->notified = 0;
				$history->amount = $amount . $this->currency->currency_code;
				$history->data = $dbg . 'Authorization Code: ' . $approvalCode . "\r\n" . 'Transaction ID: ' . $transactionId;

				$order_status = $this->payment_params->verified_status;

				$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=listing';
				$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE','',HIKASHOP_LIVE);
				$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
				$email->subject = JText::sprintf('PAYMENT_NOTIFICATION','Payment express','Accepted');
				$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Payment express','Accepted')).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order_status)."\r\n\r\n".$order_text;

				$this->modifyorder($order,$order_status,$history,$email);
			}else{
				if(preg_match('#<ReCo>([0-9]+)</ReCo>#', $ret, $res)){
					$error = $res[1].' ';
				}
				if(preg_match('#<ResponseText>(.*)</ResponseText>#', $ret, $res)){
					$error_msg = $res[1].' ';
				}
				if(preg_match('#<HelpText>(.*)</HelpText>#', $ret, $res)){
					$error_msg .= $res[1];
				}
				$responseMsg = $error.$error_msg;
				if( !empty($responseMsg) ) {
					$this->app->enqueueMessage('Error : '.$responseMsg);
				} else {
					$this->app->enqueueMessage('Error');
				}
				$do = false;
			}
		}else{
			$this->app->enqueueMessage('There was an error during the connection with the Payment Express gateway');
			if( $this->payment_params->debug ) {
				echo 'Curl Err [' . $error . '] : ' . $err_msg . "\n\n\n";
			}
			$do = false;
		}

		$this->writeToLog($dbg);

		if( !$do ) {
			return true;
		}

		$this->ccClear();

		return true;
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		parent::onAfterOrderConfirm($order, $methods, $method_id);
		$this->removeCart = true;

		return $this->showPage('thanks');
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='Payment Express';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';

		$element->payment_params->username='';
		$element->payment_params->password='';
		$element->payment_params->ask_ccv = true;
		$element->payment_params->ask_owner = false;
		$element->payment_params->verified_status='confirmed';
	}
}
