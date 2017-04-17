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
class hikashopPeriodType{
	function load($inside){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'today',JText::_('TODAY'));
		$this->values[] = JHTML::_('select.option', 'yesterday',JText::_('YESTERDAY'));
		$this->values[] = JHTML::_('select.option', 'last24h',JText::_('LAST_24H'));
		$this->values[] = JHTML::_('select.option', 'thisWeek',JText::_('THIS_WEEK'));
		$this->values[] = JHTML::_('select.option', 'last7d',JText::_('LAST_7D'));
		$this->values[] = JHTML::_('select.option', 'thisMonth',JText::_('THIS_MONTH'));
		$this->values[] = JHTML::_('select.option', 'last30d',JText::_('LAST_30D'));
		$this->values[] = JHTML::_('select.option', 'thisYear',JText::_('THIS_YEAR'));
		$this->values[] = JHTML::_('select.option', 'last365d',JText::_('LAST_365D'));
		$this->values[] = JHTML::_('select.option', 'previousWeek',JText::_('PREVIOUS_WEEK'));
		$this->values[] = JHTML::_('select.option', 'previousMonth',JText::_('PREVIOUS_MONTH'));
		$this->values[] = JHTML::_('select.option', 'previousYear',JText::_('PREVIOUS_YEAR'));
		$this->values[] = JHTML::_('select.option', 'all',JText::_('HIKA_ALL'));
	}
	function display($map,$value, $inside=true, $radio=false){
		$this->load($inside);
		$type='select.genericlist';
		if($radio){
			$type='select.radiolist';
		}
		return JHTML::_($type, $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', $value );
	}
}
