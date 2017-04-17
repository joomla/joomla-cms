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
class hikashopChilddisplayType {
	function load($show_inherit = true, $groupby = false) {
		$this->values = array(
			0 => JHTML::_('select.option', 0, JText::_('DIRECT_SUB_ELEMENTS')),
			1 => JHTML::_('select.option', 1, JText::_('ALL_SUB_ELEMENTS'))
		);
		if($groupby)
			$this->values[3] = JHTML::_('select.option', 3, JText::_('ALL_SUB_ELEMENTS_GROUP_BY_CATEGORY'));
		if($show_inherit && JRequest::getCmd('from_display', false) == false) {
			$config = hikashop_config();
			$defaultParams = $config->get('default_params');
			$default = '';
			if(isset($defaultParams['filter_type']))
				$default = ' ('.$this->values[$defaultParams['filter_type']]->text.')';
			$this->values[2] = JHTML::_('select.option', 2, JText::_('HIKA_INHERIT').$default);
		}
	}
	function display($map, $value, $form = true, $show_inherit = true, $groupby = false) {
		$this->load($show_inherit, $groupby);
		$options = 'class="inputbox" size="1" ';
		if(!$form) {
			$options .= 'onchange="this.form.submit();"';
		}
		return JHTML::_('select.genericlist', $this->values, $map, $options, 'value', 'text', (int)$value );
	}
}
