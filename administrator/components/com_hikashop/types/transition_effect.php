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
class hikashopTransition_effectType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'linear',JText::_('LINEAR'));
		$this->values[] = JHTML::_('select.option', 'bounce',JText::_('BOUNCE'));
		$this->values[] = JHTML::_('select.option', 'elastic',JText::_('ELASTIC'));
		$this->values[] = JHTML::_('select.option', 'sin',JText::_('SINUSOIDAL'));
		$this->values[] = JHTML::_('select.option', 'quad',JText::_('QUADRATIC'));
		$this->values[] = JHTML::_('select.option', 'expo',JText::_('EXPONENTIAL'));
		$this->values[] = JHTML::_('select.option', 'back',JText::_('HIKA_BACK'));
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', $value );
	}
}
