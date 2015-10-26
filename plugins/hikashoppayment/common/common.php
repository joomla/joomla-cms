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
jimport( 'joomla.plugin.helper' );
class plgHikashoppaymentCommon extends hikashopPaymentPlugin
{

	var $multiple = true;
	var $name = 'common';

	function loadCurrentPlugin($id,$name){
		static $plugins = array();
		if(!isset($plugins[$id])){
			$plugins[$id] = hikashop_import('payment',$name);
			$plugins[$id]->params=&$this;
			JPluginHelper::importPlugin('payment');
			$plugin = JPluginHelper::getPlugin('payment', $name);
			if(is_object($plugin))$plugin->params = json_encode($this->payment_params);
		}
		return $plugins[$id];
	}

	function get($property, $default = NULL){
		if(isset($this->payment_params->$property)) return $this->payment_params->$property;
		return $default;
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);
		$plugin = $this->loadCurrentPlugin($order->order_payment_id,$this->payment_params->common_payment_plugin);
		if(!is_object($plugin) || !method_exists($plugin,'onTP_GetHTML')) return '';

		echo $plugin->onTP_GetHTML($this->_loadVars($order));
	}

	function _loadVars(&$order){
		$notify_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=notify&notif_payment='.$this->name.'&name='.$this->payment_params->common_payment_plugin.'&order_id='.$order->order_id .'&tmpl=component&lang='.$this->locale . $this->url_itemid;
		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order->order_id . $this->url_itemid;
		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order->order_id . $this->url_itemid;
		if($this->currency->currency_locale['int_frac_digits'] > 2)
			$this->currency->currency_locale['int_frac_digits'] = 2;
		if(isset($order->cart->full_total->prices[0]->price_value_with_tax)){
			$price = $order->cart->full_total->prices[0]->price_value_with_tax;
		}else{
			$price = $dbOrder->order_full_price;
		}
		$vars = new stdClass();
		$vars->order_id = $order->order_id;
		$vars->amount = round($order->cart->full_total->prices[0]->price_value_with_tax, (int)$this->currency->currency_locale['int_frac_digits']);
		$vars->currency_code = $this->currency->currency_code;
		$vars->user_id = $this->user->user_cms_id;
		$vars->email = $this->user->user_email;
		$vars->user_email = $this->user->user_email;
		$vars->country_code = @$order->cart->billing_address->address_country->zone_code_2;
		$vars->item_name = JText::_('CART_PRODUCT_TOTAL_PRICE');
		$vars->return = $notify_url;
		$vars->cancel_return = $cancel_url;
		$vars->notify_url = $notify_url;
		$vars->url = $notify_url;
		$vars->submiturl = $notify_url.'&payment_submit_mode=1';
		$vars->is_recurring = 0;
		return $vars;
	}

	function onPaymentNotification(&$statuses){
		$plugin = $this->loadCurrentPlugin(0,@$_REQUEST['name']);
		$function = 'onTP_Processpayment';
		if(!empty($_REQUEST['payment_submit_mode'])) $function = 'onTP_ProcessSubmit';
		if(!is_object($plugin) || !method_exists($plugin,$function)) return true;

		$dbOrder = $this->getOrder((int)@$_REQUEST['order_id']);
		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);
		foreach($this->payment_params as $k => $v){
			$plugin->params->set($k,$v);
		}


		ob_start();
		$result = $plugin->$function($_POST,$this->_loadVars($dbOrder));
		$data = ob_get_clean();
		$statuses = array('C'=>$this->payment_params->verified_status,'P'=>$this->payment_params->pending_status);
		$url = HIKASHOP_LIVE . 'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id=' . $result['order_id'];
		$order_text = "\r\n" . JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE', $dbOrder->order_number, HIKASHOP_LIVE);
		$order_text .= "\r\n" . str_replace('<br/>', "\r\n", JText::sprintf('ACCESS_ORDER_WITH_LINK', $url));
		$email = new stdClass();
		$status = @$statuses[$result['status']];
		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER',$_REQUEST['name'],$status,$dbOrder->order_number);
		if(!empty($statuses[$result['status']])){
			$status = $statuses[$result['status']];
			$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS',$_REQUEST['name'],$status)).' '.JText::sprintf('ORDER_STATUS_CHANGED',$status)."\r\n\r\n".$order_text;

			$history = new stdClass();
			$history->notified = 0;
			if($status=='confirmed') $history->notified = 1;
			$history->data = $data;
			if(!empty($result['transaction_id'])) $history->data = 'Transaction id: '.$result['transaction_id'] . "\r\n\r\n".$history->data;

			$this->modifyOrder($result['order_id'], $status, $history, $email);
		}else{
			$email->body = str_replace('<br/>',"\r\n",@$result['error']['desc']."\r\n\r\n".$order_text);
			$o = false;
			$this->modifyOrder($o, null, null, $email);
		}
		if(!empty($result['return'])){
			$this->app->redirect($result['return']);
		}
	}

	function onAfterHikaPluginConfigurationSelectionListing($type, &$plugins, &$parent){
		if($type!='payment') return true;
		if(version_compare(JVERSION,'1.6','<')){
			$query='SELECT * FROM '.hikashop_table('plugins',false).' WHERE folder=\'payment\' ORDER BY ordering ASC';
		}else{
			$query='SELECT extension_id as id, enabled as published,name,element FROM '.hikashop_table('extensions',false).' WHERE folder=\'payment\' AND type=\'plugin\' ORDER BY ordering ASC';
		}
		$database = JFactory::getDBO();
		$database->setQuery($query);
		$payments = $database->loadObjectList();
		foreach($payments as $k => $payment){
			$payments[$k]->element = 'common&element='.$payments[$k]->element;
		}

		foreach($plugins as $k => $plugin){
			if($plugin->element == 'common'){
				unset($plugins[$k]);
			}
		}

		$plugins = array_merge($plugins,$payments);
		return true;
	}
}
