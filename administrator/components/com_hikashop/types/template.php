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
class hikashopTemplateType{
	function load($templates){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '',JText::_('ALL_TEMPLATES'));
		foreach($templates as $template){
			$this->values[] = JHTML::_('select.option', $template,$template);
		}
	}
	function display($map,$value,$templates){
		$this->load($templates);
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" onchange="document.adminForm.submit();return false;"', 'value', 'text', $value );
	}
}
