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
class hikashopMenusType{
	function hikashopMenusType(){
		if(!HIKASHOP_J16){
			$query = 'SELECT a.name, a.id as itemid, b.title  FROM `#__menu` as a LEFT JOIN `#__menu_types` as b on a.menutype = b.menutype WHERE a.access = 0 ORDER BY b.title ASC,a.ordering ASC';
		}else if(!HIKASHOP_J30){
			$query = 'SELECT a.alias as name, a.id as itemid, b.title  FROM `#__menu` as a LEFT JOIN `#__menu_types` as b on a.menutype = b.menutype WHERE a.client_id=0 AND a.parent_id!=0 ORDER BY b.title ASC,a.ordering ASC';
		} else {
			$query = 'SELECT a.alias as name, a.id as itemid, b.title  FROM `#__menu` as a LEFT JOIN `#__menu_types` as b on a.menutype = b.menutype WHERE a.client_id=0 AND a.parent_id!=0 ORDER BY b.title ASC';
		}
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$joomMenus = $db->loadObjectList();
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0',JText::_('HIKA_NONE'));
		$lastGroup = '';
		foreach($joomMenus as $oneMenu){
			if($oneMenu->title != $lastGroup){
				if(!empty($lastGroup))
					$this->values[] = JHTML::_('select.option', '</OPTGROUP>');
				$this->values[] = JHTML::_('select.option', '<OPTGROUP>',$oneMenu->title);
				$lastGroup = $oneMenu->title;
			}
			$this->values[] = JHTML::_('select.option', $oneMenu->itemid,$oneMenu->name);
		}
		if(!empty($lastGroup))
			$this->values[] = JHTML::_('select.option', '</OPTGROUP>');
	}
	function display($map,$value){
		return JHTML::_('select.genericlist', $this->values, $map , 'size="1"', 'value', 'text', $value);
	}
}
