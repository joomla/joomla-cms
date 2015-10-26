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
class hikashopTaxType{
	function load($form){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '',JText::_('ALL_CUSTOMER_TYPES'));
		$this->values[] = JHTML::_('select.option', 'individual',JText::_('INDIVIDUAL'));
		$this->values[] = JHTML::_('select.option', 'company_without_vat_number',JText::_('COMPANY_WITHOUT_VAT_NUMBER'));
		$this->values[] = JHTML::_('select.option', 'company_with_vat_number',JText::_('COMPANY_WITH_VAT_NUMBER'));
	}
	function display($map,$value,$form=true,$options='class="inputbox" size="1"'){
		$this->load($form);
		if(!$form){
			$options .=' onchange="document.adminForm.submit();"';
		}
		return JHTML::_('select.genericlist',   $this->values, $map, $options, 'value', 'text', $value );
	}
}
