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
class hikashopLayoutType{
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'div',JText::_('DIV') );
		$this->values[] = JHTML::_('select.option', 'table',JText::_('TABLE'));
		$this->values[] = JHTML::_('select.option', 'list',JText::_('LIST'));
		if(JRequest::getCmd('from_display',false) == false)
			$this->values[] = JHTML::_('select.option', 'inherit',JText::_('HIKA_INHERIT'));

		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onHkLayoutTypeLoad', array(&$this->values));

	}
	function display($map,$value,&$js,$update=true,$id='',$control='',$module=false){
		$this->load();
		$options = '';

		$optValues = array();
		foreach($this->values as $k => $optValue){
			$optValues[] = $optValue->value;
		}
		$optValues = '\''.implode('\',\'',$optValues).'\'';

		if($update){
			$options = 'var options = ['.$optValues.'];';
			if ($module)
			{
				if(!HIKASHOP_J30) {
					$js .= $options.'switchPanelMod(\''.$value.'\',options,\'layout\',\''.$control.'\');';
					$options = 'onchange="'.$options.'return switchPanelMod(this.value,options,\'layout\',\''.$control.'\');"';
				}
			}
			else
			{
				$js .=$options.'switchPanel(\''.$value.'\',options,\'layout\');';
				$options = 'onchange="'.$options.'return switchPanel(this.value,options,\'layout\');"';
			}
		}
		if(!empty($id)){
			return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" '.$options, 'value', 'text', $value,'layout_select'.$control ,$id);
		}else{
			return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" '.$options, 'value', 'text', $value,'layout_select'.$control );
		}
	}
}
