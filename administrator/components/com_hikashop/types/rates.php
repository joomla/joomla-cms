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
class hikashopRatesType{
	function load($form){
		$this->values = array();
		$query = 'SELECT * FROM '.hikashop_table('tax');
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$this->results = $db->loadObjectList();
		if(!$form){
			$this->values[] = JHTML::_('select.option', '',JText::_('ALL_RATES'));
		}
		foreach($this->results as $result){
			$this->values[] = JHTML::_('select.option', $result->tax_namekey,$result->tax_namekey.' ('.($result->tax_rate*100.0).'%)');
		}
	}
	function display($map,$value,$form=true){
		$this->load($form);
		$options = 'class="inputbox" size="1"';
		if(!$form){
			$options .=' onchange="document.adminForm.submit();"';
		}
		return JHTML::_('select.genericlist',   $this->values, $map, $options, 'value', 'text', $value );
	}
}
