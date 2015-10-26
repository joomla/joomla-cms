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
class hikashopButtonType{
	function load($value){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'normal',JText::_('NORMAL'));
		if($value=='rounded'){
			$this->values[] = JHTML::_('select.option', 'rounded',JText::_('ROUNDED'));
		}
		$this->values[] = JHTML::_('select.option', 'css',JText::_('CSS'));
	}
	function display($map,$value){
		$this->load($value);
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', $value );
	}
}
