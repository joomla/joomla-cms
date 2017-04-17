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
class hikashopCategoryType {

	function load() {
		$this->values = array(
			JHTML::_('select.option', 'product',JText::_('PRODUCT_CATEGORY')),
			JHTML::_('select.option', 'tax',JText::_('TAXATION_CATEGORY')),
			JHTML::_('select.option', 'status',JText::_('ORDER_STATUS')),
			JHTML::_('select.option', 'manufacturer',JText::_('MANUFACTURER')),
		);
	}

	function display($map, $value) {
		$this->load();
		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" size="1" '.(!empty($this->onchange)?'onchange="'.$this->onchange.'"':''), 'value', 'text', $value );
	}
}
