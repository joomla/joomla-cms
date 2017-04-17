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
class hikashopContentType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'product',JText::_('PRODUCT') );
		$this->values[] = JHTML::_('select.option', 'category',JText::_('CATEGORY'));
		$this->values[] = JHTML::_('select.option', 'manufacturer',JText::_('MANUFACTURER'));
	}
	function display($map,$value,&$js,$update=true,$control='',$module=false){
		$this->load();
		$options='';
		if($update){
			if(empty($value)){
				$value = 'product';
			}
			$options = 'var options = [\'product\', \'category\'];';
			if ($module)
			{
				if(!HIKASHOP_J30) {
					$js .= $options.'switchPanelMod(\''.$value.'\',options,\'content\',\''.$control.'\');';
					$options = 'onchange="'.$options.'return switchPanelMod(this.value,options,\'content\',\''.$control.'\');"';
				}
			}
			else
			{
				$js .=$options.'switchPanel(\''.$value.'\',options,\'content\');';
				$options='onchange="'.$options.'return switchPanel(this.value,options,\'content\');"';
			}
		}
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" '.$options, 'value', 'text', $value, 'content_select'.$control );
	}
}
