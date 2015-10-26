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
class hikashopCharacteristicdisplayType{
	function load($type){
		$this->values = array();
		if($type!='config'){
			$this->values[] = JHTML::_('select.option', '',JText::_('HIKA_INHERIT'));
		}
		$this->values[] = JHTML::_('select.option', 'dropdown',JText::_('DROPDOWN'));
		$this->values[] = JHTML::_('select.option', 'radio',JText::_('FIELD_RADIO'));
		if($type=='config'){
			$this->values[] = JHTML::_('select.option', 'table',JText::_('TABLE'));//table only works for 2 characteristics, it will default to dropdown if less or more
			$this->values[] = JHTML::_('select.option', 'list',JText::_('LIST'));
		}
	}
	function display($map,$value,$type='config'){
		$this->load($type);
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', $value );
	}
}
