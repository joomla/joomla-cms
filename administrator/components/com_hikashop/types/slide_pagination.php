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
class hikashopSlide_paginationType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'no_pagination',JText::_('HIKASHOP_NO'));
		$this->values[] = JHTML::_('select.option', 'numbers',JText::_('NUMBERS'));
		$this->values[] = JHTML::_('select.option', 'thumbnails',JText::_('THUMBNAILS'));
		$this->values[] = JHTML::_('select.option', 'names',JText::_('HIKA_NAME'));
		$this->values[] = JHTML::_('select.option', 'rounds',JText::_('DOTS'));
	}
	function display($map,$value, $options=''){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" '.$options, 'value', 'text', $value );
	}
}
