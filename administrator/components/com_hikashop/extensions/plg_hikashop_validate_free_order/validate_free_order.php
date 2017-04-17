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
class plgHikashopValidate_free_order extends JPlugin
{
	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
	}

	protected function init() {
		if(isset($this->params))
			return;
		$plugin = JPluginHelper::getPlugin('hikashop', 'validate_free_order');
		if(!HIKASHOP_J25) {
			jimport('joomla.html.parameter');
			$this->params = new JParameter($plugin->params);
		} else {
			$this->params = new JRegistry($plugin->params);
		}
	}

	public function onBeforeOrderCreate(&$order, &$send_email) {
		if(empty($order) || empty($order->order_type) || $order->order_type != 'sale' || !isset($order->order_full_price))
			return;

		$this->init();
		if(!$this->params->get('send_confirmation', 1) && bccomp($order->order_full_price, 0, 5) == 0) {
			$config = hikashop_config();
			$order->order_status = $config->get('order_confirmed_status', 'confirmed');
		}
	}

	public function onAfterOrderCreate(&$order) {
		if(empty($order) || empty($order->order_type) || $order->order_type != 'sale' || !isset($order->order_full_price))
			return;

		$this->init();
		$send_confirmation = $this->params->get('send_confirmation', 1);

		if(!$send_confirmation && $order->order_status == 'confirmed') {
			$class = hikashop_get('class.cart');
			$class->cleanCartFromSession();
			return;
		}

		if($send_confirmation && bccomp($order->order_full_price, 0, 5) == 0) {
			$config = hikashop_config();
			$orderObj = new stdClass();
			$orderObj->order_id = (int)$order->order_id;
			$orderObj->order_status = $config->get('order_confirmed_status', 'confirmed');
			$orderObj->history = new stdClass();
			$orderObj->history->history_notified = 1;
			$orderClass = hikashop_get('class.order');
			$orderClass->save($orderObj);

			$class = hikashop_get('class.cart');
			$class->cleanCartFromSession();
		}
	}
}
