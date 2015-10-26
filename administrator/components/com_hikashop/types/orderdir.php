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
class hikashopOrderdirType{
	function load(){
		$this->values = array(
			'ASC' => JHTML::_('select.option', 'ASC',JText::_('ASCENDING')),
			'DESC' => JHTML::_('select.option', 'DESC',JText::_('DESCENDING'))
		);
		if(JRequest::getCmd('from_display',false) == false) {
			$config = hikashop_config();
			$defaultParams = $config->get('default_params');
			$default = '';
			if(isset($defaultParams['order_dir']))
				$default = ' ('.$this->values[$defaultParams['order_dir']]->text.')';
			$this->values['inherit'] = JHTML::_('select.option', 'inherit', JText::_('HIKA_INHERIT').$default);
		}
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', $value );
	}
}
