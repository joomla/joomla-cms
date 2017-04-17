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
class hikashopPricedisplayType{
	function load(){
		$this->values = array(
			'cheapest' => JHTML::_('select.option', 'cheapest',JText::_('CHEAPEST_PRICE')),
			'unit' => JHTML::_('select.option', 'unit', JText::_('UNIT_PRICE_ONLY')),
			'range' => JHTML::_('select.option', 'range', JText::_('PRICE_RANGE')),
			'all' => JHTML::_('select.option', 'all', JText::_('HIKA_ALL'))
		);
		if(JRequest::getCmd('from_display',false) == false){
			$config = hikashop_config();
			$defaultParams = $config->get('default_params');
			$default = '';
			if(isset($defaultParams['price_display_type']))
				$default = ' ('.$this->values[$defaultParams['price_display_type']]->text.')';
			$this->values['inherit'] = JHTML::_('select.option', 'inherit', JText::_('HIKA_INHERIT').$default);
		}
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', $value );
	}
}
