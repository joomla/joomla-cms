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
class hikashopAddressType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '',JText::_('NO_ADDRESS') );
		$this->values[] = JHTML::_('select.option', 'billing',JText::_('HIKASHOP_BILLING_ADDRESS'));
		$this->values[] = JHTML::_('select.option', 'shipping',JText::_('HIKASHOP_SHIPPING_ADDRESS'));
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', $value );
	}
}
