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
class plgHikashoppaymentEselect extends hikashopPaymentPlugin {
	var $accepted_currencies = array( 'CAD','USD' );
	var $name = 'eselect';
	var $multiple = true;
	var $ask_cc = true;
	var $ask_owner = true;
	var $ask_ccv = true;

	var $pluginConfig = array(
		 'store_id' => array('STORE_ID', 'input'),
		 'api_token' => array('API Token', 'input'),
		 'ask_ccv' => array('CARD_VALIDATION_CODE', 'boolean','0'),
		 'debug' => array('SANDBOX', 'boolean','0'),
		 'return_url' => array('RETURN_URL', 'input'),
		 'cancel_url' => array('CANCEL_URL', 'input'),
		 'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function onBeforeOrderCreate(&$order, &$do) {
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;

		if(!function_exists('curl_init')) {
			$this->app->enqueueMessage('The eSelect payment plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.', 'error');
			$do = false;
			return false;
		}

		$this->ccLoad();

		ob_start();
		$dbg = '';
		$amount = number_format($order->cart->full_total->prices[0]->price_value_with_tax,2,'.','');

		require_once dirname(__FILE__) . DS . 'eselect_lib.php';

		$txnArray = array(
			'type' => 'purchase',
			'order_id' => uniqid(),
			'cust_id' => $this->user->user_id,
			'amount' => $amount,
			'pan' => $this->cc_number,
			'expdate' => $this->cc_month . $this->cc_year,
			'crypt_type' => '7', // SSL enabled merchant
			'dynamic_descriptor' => ''
		);

		$mpgTxn = new mpgTransaction($txnArray);

		if($this->payment_params->ask_ccv) {
			$cvdTemplate = array(
				'cvd_indicator' => 1,
				'cvd_value' => $this->cc_CCV
			);
			$mpgCvdInfo = new mpgCvdInfo($cvdTemplate);

			$mpgTxn->setCvdInfo($mpgCvdInfo);
		}

		$mpgRequest = new mpgRequest($mpgTxn);
		$mpgHttpPost = new mpgHttpsPost($this->payment_params->store_id, $this->payment_params->api_token, $mpgRequest, (int)$this->payment_params->debug != 0);
		$mpgResponse = $mpgHttpPost->getMpgResponse();

		$ret = $mpgResponse->getResponseCode();

		if($ret !== null && $ret != 'null') {
			$ret = (int)$ret;
			if( $ret < 50 && $mpgResponse->getComplete() == 'true') {

				ob_get_clean();
				ob_start();

				$this->modifyOrder($order, $this->payment_params->verified_status, true, true);
			} else {
				$responseMsg = $mpgResponse->getMessage();
				if( !empty($responseMsg) ) {
					$this->app->enqueueMessage($responseMsg);
				} else {
					$this->app->enqueueMessage('Eselect/Moneris Response Error');
				}
				$do = false;
			}
		} else {
			if(!empty($mpgHttpPost->curl_err)) {
				$this->app->enqueueMessage($mpgHttpPost->curl_err_msg);
			} else {
				$msg = $mpgResponse->getMessage();
				if(empty($msg))
					$this->app->enqueueMessage('Eselect/Moneris Generic Error');
				else
					$this->app->enqueueMessage('Eselect/Moneris: '.$msg);
			}
			$do = false;
		}

		if( $do == false ) {
			return true;
		}

		$this->ccClear();

		return true;
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		$method =& $methods[$method_id];

		return $this->showPage('thanks');
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='ESELECT';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express';
		$element->payment_params->store_id='';
		$element->payment_params->api_token='';
		$element->payment_params->ask_ccv = true;

		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
	}
}
