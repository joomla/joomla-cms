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

class hikashopOperatorsType{
	var $extra = '';
	function hikashopOperatorsType(){

		$this->values = array();

		$this->values[] = JHTML::_('select.option', '<OPTGROUP>',JText::_('HIKA_NUMERIC'));
		$this->values[] = JHTML::_('select.option', '=','=');
		$this->values[] = JHTML::_('select.option', '!=','!=');
		$this->values[] = JHTML::_('select.option', '>','>');
		$this->values[] = JHTML::_('select.option', '<','<');
		$this->values[] = JHTML::_('select.option', '>=','>=');
		$this->values[] = JHTML::_('select.option', '<=','<=');
		$this->values[] = JHTML::_('select.option', '</OPTGROUP>');
		$this->values[] = JHTML::_('select.option', '<OPTGROUP>',JText::_('HIKA_STRING'));
		$this->values[] = JHTML::_('select.option', 'BEGINS',JText::_('HIKA_BEGINS_WITH'));
		$this->values[] = JHTML::_('select.option', 'END',JText::_('HIKA_ENDS_WITH'));
		$this->values[] = JHTML::_('select.option', 'CONTAINS',JText::_('HIKA_CONTAINS'));
		$this->values[] = JHTML::_('select.option', 'NOTCONTAINS',JText::_('HIKA_NOT_CONTAINS'));
		$this->values[] = JHTML::_('select.option', 'LIKE','LIKE');
		$this->values[] = JHTML::_('select.option', 'NOT LIKE','NOT LIKE');
		$this->values[] = JHTML::_('select.option', 'REGEXP','REGEXP');
		$this->values[] = JHTML::_('select.option', 'NOT REGEXP','NOT REGEXP');
		$this->values[] = JHTML::_('select.option', '</OPTGROUP>');
		$this->values[] = JHTML::_('select.option', '<OPTGROUP>',JText::_('OTHER'));
		$this->values[] = JHTML::_('select.option', 'IS NULL','IS NULL');
		$this->values[] = JHTML::_('select.option', 'IS NOT NULL','IS NOT NULL');
		$this->values[] = JHTML::_('select.option', '</OPTGROUP>');

	}

	function display($map, $default ='', $additionalClass=""){
		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox '.$additionalClass.'" size="1" style="width:120px;" '.$this->extra, 'value', 'text',$default);
	}

}
