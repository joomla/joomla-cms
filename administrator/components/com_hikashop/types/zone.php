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
class hikashopZoneType{
	function load($form=false){
		$this->values = array();
		if(!$form){
			$this->values[] = JHTML::_('select.option', '', JText::_('ALL_ZONES') );
		}
		$this->values[] = JHTML::_('select.option', 'country',JText::_('COUNTRIES'));
		$this->values[] = JHTML::_('select.option', 'state',JText::_('STATES'));
		$this->values[] = JHTML::_('select.option', 'tax',JText::_('TAX_ZONES'));
		$this->values[] = JHTML::_('select.option', 'ship',JText::_('SHIP_ZONES'));
		$this->values[] = JHTML::_('select.option', 'discount',JText::_('DISCOUNT_ZONES'));
		$this->values[] = JHTML::_('select.option', 'payment',JText::_('PAYMENT_ZONES'));
	}
	function display($map,$value,$form=false){
		$this->load($form);
		$dynamic = ($form ? '' : 'onchange="document.adminForm.submit( );"');
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"'. $dynamic, 'value', 'text', $value );
	}
}
