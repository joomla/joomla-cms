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
class hikashopDiscount_displayType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 2,JText::_('DISPLAY_PRICE_BEFORE_DISCOUNT'));
		$this->values[] = JHTML::_('select.option', 1,JText::_('DISPLAY_DISCOUNT_AMOUNT'));
		$this->values[] = JHTML::_('select.option', 0,JText::_('HIKASHOP_NO'));
		if(JRequest::getCmd('from_display',false) == false){
			$config = hikashop_config();
			$defaultParams = $config->get('default_params');
			$default = '';
			if(isset($defaultParams['show_discount']))
				$default = ' ('.$this->values[$defaultParams['show_discount']]->text.')';
			$this->values[] = JHTML::_('select.option', 3,JText::_('HIKA_INHERIT').$default);
		}
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', (int)$value );
	}
}
