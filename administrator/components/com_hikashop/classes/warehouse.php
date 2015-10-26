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
class hikashopWarehouseClass extends hikashopClass {
	var $tables = array('warehouse');
	var $pkeys = array('warehouse_id');
	var $toggle = array('warehouse_published'=>'warehouse_id');

	function saveForm() {
		$element = new stdClass();
		$element->warehouse_id = hikashop_getCID('warehouse_id');
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = & JFilterInput::getInstance(null, null, 1, 1);
		foreach($formData['warehouse'] as $column => $value) {
			hikashop_secureField($column);
			$element->$column = $safeHtmlFilter->clean(strip_tags($value), 'string');
		}
		$class = hikashop_get('helper.translation');
		$class->getTranslations($element);
		$status = $this->save($element);

		return $status;
	}

	function save(&$element) {
		$isNew = empty($element->warehouse_id);
		$element->warehouse_modified=time();
		if($isNew) {
			$element->warehouse_created=$element->warehouse_modified;
			$orderClass = hikashop_get('helper.order');
			$orderClass->pkey = 'warehouse_id';
			$orderClass->table = 'warehouse';
			$orderClass->orderingMap = 'warehouse_ordering';
			$orderClass->reOrder();
		}
		$status = parent::save($element);
		if(!$status) {
			return false;
		}
		return $status;
	}

	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {

		$ret = array(
			0 => array(),
			1 => array()
		);

		$db = JFactory::getDBO();

		$limit = (int)@$typeConfig['limit'];
		if(!empty($options['limit']))
			$limit = (int)$options['limit'];
		if(empty($limit))
			$limit = 30;

		if(!empty($search)) {
			$searchStr = "'%" . ((HIKASHOP_J30) ? $db->escape($search, true) : $db->getEscaped($search, true) ) . "%'";
			$query = 'SELECT warehouse_id, warehouse_name '.
				' FROM ' . hikashop_table('warehouse') .
				' WHERE warehouse_published = 1 AND warehouse_name LIKE ' . $searchStr .
				' ORDER BY warehouse_name';
		} else {
			$query = 'SELECT warehouse_id, warehouse_name '.
				' FROM ' . hikashop_table('warehouse') .
				' WHERE warehouse_published = 1 '.
				' ORDER BY warehouse_name';
		}

		$db->setQuery($query, 0, $limit);
		$warehouses = $db->loadObjectList('warehouse_id');
		foreach($warehouses as $warehouse) {
			$ret[0][$warehouse->warehouse_id] = $warehouse;
		}

		if(count($warehouses) == $limit)
			$fullLoad = false;

		if(!empty($value)) {
			if(!is_array($value))
				$value = array($value);

			if($fullLoad) {
				foreach($value as $v) {
					if(isset($ret[0][(int)$v]))
						$ret[1][(int)$v] = $ret[0][(int)$v];
				}
			} else {
				$values = array_merge($values);
				JArrayHelper::toInteger($values);

				$query = 'SELECT warehouse_id, warehouse_name '.
					' FROM ' . hikashop_table('warehouse') .
					' WHERE warehouse_id IN ('.implode(',', $values).') '.
					' ORDER BY warehouse_name';
				$db->setQuery($query);
				$ret[1] = $db->loadObjectList('warehouse_id');
			}
		}

		return $ret;
	}
}
