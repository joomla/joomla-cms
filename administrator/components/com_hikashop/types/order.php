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
class hikashopOrderType {
	function load($type, $value ='', $inherit = true) {
		$filter = false;
		if($type == 'product_filter') {
			$type = 'product';
			$filter = true;
		}

		if(substr($type, 0, 1) != '#')
			$query = 'SELECT * FROM '.hikashop_table($type);
		else
			$query = 'SELECT * FROM '.hikashop_table(substr($type, 2), false);

		$database = JFactory::getDBO();
		$database->setQuery($query, 0, 1);
		$arr = $database->loadAssoc();

		$object = new stdClass();
		if(!empty($arr)) {
			if(!is_array($value) && !isset($arr[$value]) && !in_array($value,array('ordering','inherit'))) {
				$arr[$value]=$value;
			}
			ksort($arr);
			foreach($arr as $key => $value) {
				if(!empty($key))
					$object->$key = $value;
			}
		}

		$this->values = array();
		if($type == 'product') {
			if(!$filter) {
				$this->values['ordering'] = JHTML::_('select.option', 'ordering', JText::_('ORDERING'));
			} else {
				$this->values['all'] = JHTML::_('select.option', 'all','all');
			}
		}
		if(!empty($object)) {
			foreach(get_object_vars($object) as $key => $val) {
				$this->values[$key] = JHTML::_('select.option', $key,$key);
			}
			if(JRequest::getCmd('from_display',false) == false && $inherit) {
				$config = hikashop_config();
				$defaultParams = $config->get('default_params');
				$default = '';
				if(isset($defaultParams['product_order']) && isset($this->values[$defaultParams['product_order']]))
					$default = ' ('.$this->values[$defaultParams['product_order']]->text.')';
				$this->values[] = JHTML::_('select.option', 'inherit', JText::_('HIKA_INHERIT').$default);
			}
		}

	}

	function display($map, $value, $type, $options = 'class="inputbox" size="1"', $inherit = true) {
		$this->load($type, $value, $inherit);
		return JHTML::_('select.genericlist', $this->values, $map, $options, 'value', 'text', $value);
	}
}
