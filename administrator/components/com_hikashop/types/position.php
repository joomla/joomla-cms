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
class hikashopPositionType{
	function load($inside){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'top',JText::_('HIKA_TOP'));
		$this->values[] = JHTML::_('select.option', 'bottom',JText::_('HIKA_BOTTOM'));
		$this->values[] = JHTML::_('select.option', 'left',JText::_('HIKA_LEFT'));
		$this->values[] = JHTML::_('select.option', 'right',JText::_('HIKA_RIGHT'));
		if($inside){
			$this->values[] = JHTML::_('select.option', 'inside',JText::_('HIKA_INSIDE'));
		}
	}
	function display($map,$value, $inside=true, $radio=false){
		$this->load($inside);
		$type='hikaselect.genericlist';
		if($radio){
			$type='hikaselect.radiolist';
		}
		return JHTML::_($type, $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', $value );
	}
}
