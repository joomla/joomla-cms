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
class plgHikashopHistory extends JPlugin
{
	function plgHikashopHistory(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('hikashop', 'history');
			if(version_compare(JVERSION,'2.5','<')){
				jimport('joomla.html.parameter');
				$this->params = new JParameter($plugin->params);
			} else {
				$this->params = new JRegistry($plugin->params);
			}
		}
	}

	function onAfterOrderCreate(&$order,&$send_email){
		return $this->onAfterOrderUpdate($order,$send_email);
	}

	function onAfterOrderUpdate(&$order,&$send_email){
		if(!empty($order->order_id)){
			$history = new stdClass();
			$history->history_order_id = $order->order_id;
			$history->history_created = time();
			$history->history_ip = hikashop_getIP();
			$history->history_user_id = hikashop_loadUser();
			if(empty($order->order_status)){
				$class = hikashop_get('class.order');
				$old = $class->get($order->order_id);
				$order->order_status = $old->order_status;
			}
			$history->history_new_status = $order->order_status;
			if(!empty($order->history)){
				foreach(get_object_vars($order->history) as $k => $v){
					$history->$k = $v;
				}
			}
			$historyClass = hikashop_get('class.history');
			$historyClass->save($history);
		}
		return true;
	}

	function onAfterOrderDelete($elements){
		if(!is_array($elements)){
			$elements = array($elements);
		}
		$database = JFactory::getDBO();

		foreach($elements as $key => $val){
			$elements[$key] = $database->Quote($val);
		}

		$query='DELETE FROM '.hikashop_table('history').' WHERE history_order_id IN ('.implode(',',$elements).')';
		$database->setQuery($query);
		$database->query();
		return true;
	}
}
