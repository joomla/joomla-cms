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
class hikashopMasstableType {
	var $externalValues = null;

	function load($form=false) {
		$this->values = array();
		if(!$form) {
			$this->values[] = JHTML::_('select.option', '', JText::_('HIKA_ALL') );
		}

		if($this->externalValues == null) {
			$this->externalValues = array();
			JPluginHelper::importPlugin('hikashop');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onMassactionTableLoad', array( &$this->externalValues ) );
			foreach($this->externalValues as $externalValue) {
				$this->values[] = JHTML::_('select.option', $externalValue->value, $externalValue->text);
			}
		}
	}
	function display($map, $value, $form=false, $optionsArg=''){
		$this->load($form);
		$options ='class="inputbox" size="1"';
		if(!$form){
			$options.=' onchange="document.adminForm.submit();"';
		}
		return JHTML::_('select.genericlist', $this->values, $map, $options.$optionsArg, 'value', 'text', $value);
	}
}
