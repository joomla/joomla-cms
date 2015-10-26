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
class HikashopProductType{
	var $onchange = 'document.adminForm.submit( );';
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'all',JText::_('HIKA_ALL') );
		$this->values[] = JHTML::_('select.option', 'main',JText::_('PRODUCTS'));
		$this->values[] = JHTML::_('select.option', 'variant',JText::_('VARIANTS'));
	}
	function display($map,$value, $additionalClass = ''){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox '.$additionalClass.'" size="1" '. (!empty($this->onchange)?'onchange="'.$this->onchange.'"':''), 'value', 'text', $value );
	}
}
