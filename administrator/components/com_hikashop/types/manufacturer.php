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
class hikashopManufacturerType{
	function load($value){
		$this->values = array();
		$query = 'SELECT category_id,category_name FROM '.hikashop_table('category').' WHERE category_type = "manufacturer" AND category_depth != 1 ORDER BY category_name ASC';
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$manufacturers = $db->loadObjectList('category_id');
		if(!empty($manufacturers)){
			$this->values[] = JHTML::_('select.option', '',JText::_('MANUFACTURER'));
			$this->values[] = JHTML::_('select.option', 'none',JText::_('NO_MANUFACTURER'));
			foreach($manufacturers as $manufacturer){
				$this->values[] = JHTML::_('select.option', (int)$manufacturer->category_id, $manufacturer->category_name );
			}
		}
	}
	function display($map,$value,$options=''){
		if(empty($this->values)){
			$this->load($value);
		}
		if ($this->values) return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" onchange="document.adminForm.submit();" '.$options, 'value', 'text', $value );
	}
}
