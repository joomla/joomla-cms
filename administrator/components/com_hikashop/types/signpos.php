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
class hikashopSignposType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 0,'0 '.JText::_('PARENTHESIS_AROUND') );
		$this->values[] = JHTML::_('select.option', 1,'1 '.JText::_('SIGN_BEFORE'));
		$this->values[] = JHTML::_('select.option', 2,'2 '.JText::_('SIGN_AFTER'));
		$this->values[] = JHTML::_('select.option', 3,'3 '.JText::_('SIGN_BEFORE_SYMBOL'));
		$this->values[] = JHTML::_('select.option', 4,'4 '.JText::_('SIGN_AFTER_SYMBOL'));
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', (int)$value );
	}
}
