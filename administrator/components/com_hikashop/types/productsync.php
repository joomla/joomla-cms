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
class hikashopProductsyncType{
	function load(){
		$this->values = array(
			2 => JHTML::_('select.option', 2, JText::_('RELATED_PRODUCTS')),
			1 => JHTML::_('select.option', 1, JText::_('IN_SAME_CATEGORIES')),
			3 => JHTML::_('select.option', 3, JText::_('FROM_SAME_MANUFACTURER')),
			0 => JHTML::_('select.option', 0, JText::_('IN_MODULE_PARENT_CATEGORY'))
		);

		if(JRequest::getCmd('from_display',false) == false){
			$config = hikashop_config();
			$defaultParams = $config->get('default_params');
			$default = '';
			if(isset($defaultParams['product_synchronize']))
				$default = ' ('.$this->values[$defaultParams['product_synchronize']]->text.')';
			$this->values[4] = JHTML::_('select.option', 4, JText::_('HIKA_INHERIT').$default);
		}
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', (int)$value );
	}
}
