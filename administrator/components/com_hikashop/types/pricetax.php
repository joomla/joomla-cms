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
class hikashopPricetaxType{
	function load($inherit=false){
		$this->values = array(
			0 => JHTML::_('select.option', 0, JText::_('NO_TAX')),
			1 => JHTML::_('select.option', 1, JText::_('WITH_TAX')),
			2 => JHTML::_('select.option', 2, JText::_('DISPLAY_BOTH_TAXES'))
		);
		if($inherit){
			$config = hikashop_config();
			$defaultValue = $config->get('price_with_tax','');
			$default = '';
			if(!empty($defaultValue))
				$default = ' ('.$this->values[$defaultValue]->text.')';
			$this->values[3] = JHTML::_('select.option', 3, JText::_('HIKA_INHERIT').$default );
		}
	}
	function display($map,$value,$inherit=false){
		$this->load($inherit);
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', (int)$value );
	}
}
