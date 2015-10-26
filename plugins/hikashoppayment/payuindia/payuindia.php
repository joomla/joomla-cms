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
class plgHikashoppaymentPayuindia extends hikashopPaymentPlugin {
	var $accepted_currencies = array('INR');

	var $multiple = true;
	var $name = 'payuindia';
	var $doc_form = 'payuindia';
	var $pluginConfig = array(
		'key' => array('FEDEX_API_KEY', 'input'),
		'salt' => array('HASH_SALT', 'input'),
		'environnement' => array('ENVIRONNEMENT', 'list', array(
			'test' => 'HIKA_TEST',
			'production' => 'HIKA_PRODUCTION',
		)),
		'paisa' =>  array('PayUMoney', 'boolean','0'),
		'debug' => array('DEBUG', 'boolean','0'),
		'cancel_url' => array('CANCEL_URL', 'input'),
		'return_url' => array('RETURN_URL', 'input'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'pending_status' => array('PENDING_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);

	function onBeforeOrderCreate(&$order, &$do) {
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;
		if ($this->payment_params->debug){
			if (empty ($this->payment_params->key)) {
				$this->app->enqueueMessage('Please fill out the field API Key on your plugin configuration');
				$do = false;
			}

			if (empty ($this->payment_params->salt)) {
				$this->app->enqueueMessage('Please fill out the field Hash salt on your plugin configuration');
				$do = false;
			}
		}
	}

	function onAfterOrderConfirm(&$order, &$methods, $method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);

		$surl = HIKASHOP_LIVE . 'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment=' . $this->name . '&tmpl=component&lang=' . $this->locale . $this->url_itemid;
		$curl = HIKASHOP_LIVE . 'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id=' . $order->order_id . $this->url_itemid;

		$debug = @$this->payment_params->debug;


		$vars = array (
			'key' => $this->payment_params->key,
			'txnid' => $order->order_id,
			'surl' => $surl,
			'furl' => $curl,
			'curl' => $curl,
			'productinfo' => JText::_('ORDER_NUMBER') . ' ' . $order->order_number,
			'charset' => 'utf-8',
			'firstname' => $order->cart->shipping_address->address_firstname,
			'lastname' => $order->cart->shipping_address->address_lastname,
			'email' => $this->user->user_email,
			'phone' => $order->cart-> shipping_address->address_telephone,
			'amount' => round($order->cart->full_total->prices[0]->price_value_with_tax, (int)$this->currency->currency_locale['int_frac_digits'])
		);

		$vars['hash'] = $this->get_hash($vars, $this->payment_params->salt);

		if(@$this->payment_params->paisa)
			$vars['service_provider'] = 'payu_paisa';

		if($this->payment_params->environnement == 'test')
			$this->payment_params->url = 'https://test.payu.in/_payment';
		else
			$this->payment_params->url = 'https://secure.payu.in/_payment';

		$this->vars = $vars;
		return $this->showPage('end');

	}

	function onPaymentNotification(&$statuses) {

		$vars = array ();
		$filter = JFilterInput::getInstance();
		foreach ($_REQUEST as $key => $value) {
			$key = $filter->clean($key);
			if (preg_match('#^[0-9a-z_-]{1,30}$#i', $key) && !preg_match('#^cmd$#i', $key)) {
				$value = JRequest :: getString($key);
				$vars[$key] = $value;
			}
		}

		$order_id = (int)@$vars['txnid'];


		$dbOrder = $this->getOrder($order_id);
		$this->loadPaymentParams($dbOrder);
		if (empty ($this->payment_params))
			return false;

		$this->loadOrderData($dbOrder);

		if ($this->payment_params->debug)
			$this->writeToLog (print_r($vars, true) . "\r\n\r\n");

		if (empty ($dbOrder)) {
			echo 'Could not load any order for your notification ' . @$vars['txnid'];
			return false;
		}

		if ($this->payment_params->debug) {
			$this->writeToLog (print_r($dbOrder, true) . "\r\n\r\n");
		}

		$order_id = $dbOrder->order_id;

		$url = HIKASHOP_LIVE . 'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id=' . $order_id;
		$order_text = "\r\n" . JText :: sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE', $dbOrder->order_number, HIKASHOP_LIVE);
		$order_text .= "\r\n" . str_replace('<br/>', "\r\n", JText :: sprintf('ACCESS_ORDER_WITH_LINK', $url));

		if($this->payment_params->environnement == 'test')
			$this->payment_params->url = 'https://test.payu.in/_payment';
		else
			$this->payment_params->url = 'https://secure.payu.in/_payment';

		$HASHreverse = $this->reverse_hash ( $vars, $this->payment_params->salt, $vars['status'] );

		$surl = HIKASHOP_LIVE . 'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id=' . $order_id;
		$furl = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order_id;

		if($vars['hash'] != $HASHreverse) {
			$email = new stdClass();
			$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER', $this->name).'invalid response';
			$email->body = JText::sprintf("Hello,\r\n A Payu India notification was refused because the response from the Payu India server was invalid")."\r\n\r\n".$order_text;
			$Orderclass = hikashop_get('class.order');
			$order = $Orderclass->get($order_id);
			if($order->order_status != $this->payment_params->invalid_status)
				$this->modifyOrder($order_id, $this->payment_params->invalid_status, false, $email);

			if($this->payment_params->debug){
				$this->writeToLog ('invalid response: hash values are not the same'."\n\n\n");
			}
			$app = JFactory::getApplication();
			$app->enqueueMessage('Transaction Failed : wrong hash');
			$app->redirect($furl);
			return false;
		}

		if($vars['status'] == 'success'){
			$history = new stdClass();
			$email = new stdClass();
			$history->notified = 1;
			$history->amount = $vars['amount'];
			$history->data = ob_get_clean();

			$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Payu India',$vars['status'],$dbOrder->order_number);
			$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Payu India',$vars['status'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$this->payment_params->verified_status)."\r\n\r\n".$order_text;
			$email->body = $body;

			$Orderclass = hikashop_get('class.order');
			$order = $Orderclass->get($order_id);
			if($order->order_status != $this->payment_params->verified_status)
				$this->modifyOrder($order_id, $this->payment_params->verified_status, $history, $email);

			$app = JFactory::getApplication();
			$app->redirect($surl);
			return true;
		}

		if($vars['status'] == 'pending'){
			$email = new stdClass();
			$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','Payu India',$vars['status'],$dbOrder->order_number);
			$body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','Payu India',$vars['status'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$this->payment_params->pending_status)."\r\n\r\n".$order_text;
			$email->body = $body;
			$Orderclass = hikashop_get('class.order');
			$order = $Orderclass->get($order_id);
			if($order->order_status != $this->payment_params->pending_status)
				$this->modifyOrder($order_id, $this->payment_params->pending_status, false, $email);

			if($element->payment_params->debug){
				$this->writeToLog ('pending response'."\n\n\n");
			}
			$app = JFactory::getApplication();
			$app->redirect($surl);
			return true;
		}

		$email = new stdClass();
		$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER', $this->name).'invalid response';
		$email->body = JText::sprintf("Hello,\r\n A Payu India notification was refused because the response from the Payu India server was invalid")."\r\n\r\n".$order_text;
		$Orderclass = hikashop_get('class.order');
		$order = $Orderclass->get($order_id);
		if($order->order_status != $this->payment_params->invalid_status)
			$this->modifyOrder($order_id, $this->payment_params->invalid_status, false, $email);

		if($element->payment_params->debug){
			$this->writeToLog ('invalid response'."\n\n\n");
		}
		$app->enqueueMessage('Transaction Failed with the status : '.$vars['status']);
		$app = JFactory::getApplication();
		$app->redirect($furl);
		return false;
	}

	function getPaymentDefaultValues(& $element) {
		$element->payment_name = 'PayU India';
		$element->payment_description = 'You can pay by credit card or PayU money using this payment method';
		$element->payment_params->invalid_status = 'cancelled';
		$element->payment_params->pending_status = 'created';
		$element->payment_params->verified_status = 'confirmed';
	}

	function get_hash($params, $salt) {
		$posted = array ();

		if(!empty($params)) {
			foreach($params as $key => $value) {
				$posted[$key] = htmlentities($value, ENT_QUOTES);
			}
		}

		$hash_sequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";

		$hash_vars_seq = explode( '|', $hash_sequence );
		$hash_string = null;

		foreach($hash_vars_seq as $hash_var) {
			$hash_string .= isset( $posted[$hash_var] ) ? $posted[$hash_var] : '';
			$hash_string .= '|';
		}

		$hash_string .= $salt;
		return strtolower( hash( 'sha512', $hash_string ) );
	}

	function reverse_hash($params, $salt, $status) {
		$posted = array ();
		$hash_string = null;

		if( !empty($params) ) {
			foreach($params as $key => $value) {
				$posted[$key] = htmlentities( $value, ENT_QUOTES );
			}
		}

		$additional_hash_sequence = 'base_merchantid|base_payuid|miles|additional_charges';
		$hash_vars_seq = explode( '|', $additional_hash_sequence );

		foreach($hash_vars_seq as $hash_var) {
			$hash_string .= isset( $posted[$hash_var] ) ? $posted[$hash_var] . '|' : '';
		}

		$hash_sequence = "udf10|udf9|udf8|udf7|udf6|udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|key";
		$hash_vars_seq = explode( '|', $hash_sequence );
		$hash_string .= $salt . '|' . $status;

		foreach($hash_vars_seq as $hash_var) {
			$hash_string .= '|';
			$hash_string .= isset( $posted[$hash_var] ) ? $posted[$hash_var] : '';
		}

		return strtolower( hash( 'sha512', $hash_string ) );
	}
}
