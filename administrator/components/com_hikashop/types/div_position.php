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
class hikashopDiv_positionType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'top_left',JText::_('HIKA_TOP_LEFT'));
		$this->values[] = JHTML::_('select.option', 'top_right',JText::_('HIKA_TOP_RIGHT'));
		$this->values[] = JHTML::_('select.option', 'top_center',JText::_('HIKA_TOP_CENTER'));
		$this->values[] = JHTML::_('select.option', 'bottom_left',JText::_('HIKA_BOTTOM_LEFT'));
		$this->values[] = JHTML::_('select.option', 'bottom_right',JText::_('HIKA_BOTTOM_RIGHT'));
		$this->values[] = JHTML::_('select.option', 'bottom_center',JText::_('HIKA_BOTTOM_CENTER'));

	}
	function display($map,$value, $radio=false){
		$this->load();
		$type='select.genericlist';
		if($radio){
			$type='select.radiolist';
		}
		return JHTML::_($type, $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', $value );
	}
}
