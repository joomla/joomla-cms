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
class plgHikashoppaymentWesternunion extends hikashopPaymentPlugin {
	var $multiple = true;
	var $name = 'westernunion';
	var $pluginConfig = array(
		'order_status' => array('ORDER_STATUS', 'orderstatus'),
		'information' => array('BANK_ACCOUNT_INFORMATION', 'textarea')

	);


	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		$this->loadOrderData($order);
		$this->loadPaymentParams($order);

		$this->removeCart = true;
		$this->information = $this->payment_params->information;
		if(preg_match('#^[a-z0-9_]*$#i',$this->information)){
			$this->information = JText::_($this->information);
		}
		$currencyClass = hikashop_get('class.currency');
		$this->amount = $currencyClass->format($order->order_full_price,$order->order_currency_id);
		$this->order_number = $order->order_number;

		$this->return_url =& $this->payment_params->return_url;

		return $this->showPage('end');
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='Western Union';
		$element->payment_description='You can pay by Western Union.';
		$element->payment_images='';

		$element->payment_params->information='';
		$element->payment_params->order_status='created';
	}
}
