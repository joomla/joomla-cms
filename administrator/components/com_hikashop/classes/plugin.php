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
class hikashopPluginClass extends hikashopClass {
	var $tables = array('plugin');
	var $pkeys = array('plugin_id');
	var $toggle = array('plugin_published' => 'plugin_id');
	var $deleteToggle = array('plugin' => array('plugin_type', 'plugin_id'));

	function get($id, $default = '') {
		$result = parent::get($id);
		if(!empty($result->plugin_params))
			$result->plugin_params = unserialize($result->plugin_params);
		return $result;
	}

	function save(&$element, $reorder = true) {
		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$do = true;
		if(empty($element->payment_id))
			$dispatcher->trigger('onBeforeHikaPluginCreate', array('plugin', &$element, &$do));
		else
			$dispatcher->trigger('onBeforeHikaPluginUpdate', array('plugin', &$element, &$do));

		if(!$do)
			return false;

		if(isset($element->plugin_params) && !is_string($element->plugin_params))
			$element->plugin_params = serialize($element->plugin_params);

		if(empty($element->plugin_id))
			unset($element->plugin_id);

		$status = parent::save($element);
		if($status && empty($element->plugin_id)) {
			$element->plugin_id = $status;
			if($reorder) {
				$orderClass = hikashop_get('helper.order');
				$orderClass->pkey = 'plugin_id';
				$orderClass->table = 'plugin';
				$orderClass->groupVal = $element->plugin_type;
				$orderClass->orderingMap = 'plugin_ordering';
				$orderClass->reOrder();
			}
		}

		if($status && !empty($element->plugin_published) && !empty($element->plugin_id)) {
			$db = JFactory::getDBO();
			$query = 'SELECT plugin_type FROM ' . hikashop_table('plugin') . ' WHERE plugin_id = ' . (int)$element->plugin_id;
			$db->setQuery($query);
			$name = $db->loadResult();
			if(!HIKASHOP_J16) {
				$query = 'UPDATE '.hikashop_table('plugins',false).' SET published = 1 WHERE published = 0 AND element = ' . $db->Quote($name) . ' AND folder = ' . $db->Quote('hikashop');
			} else {
				$query = 'UPDATE '.hikashop_table('extensions',false).' SET enabled = 1 WHERE enabled = 0 AND type = ' . $db->Quote('plugin') . ' AND element = ' . $db->Quote($name) . ' AND folder = ' . $db->Quote('hikashop');
			}
			$db->setQuery($query);
			$db->query();
		}
		return $status;
	}
}
