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
class hikashopEditorType{
	function hikashopEditorType(){
		if(version_compare(JVERSION,'1.6','<')){
			$query = 'SELECT element,name FROM '.hikashop_table('plugins',false).' WHERE folder=\'editors\' AND published=1 ORDER BY ordering ASC, name ASC';
		}else{
			$query = 'SELECT element,name FROM '.hikashop_table('extensions',false).' WHERE folder=\'editors\' AND enabled=1 AND type=\'plugin\' ORDER BY ordering ASC, name ASC';
		}
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$joomEditors = $db->loadObjectList();
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0',JText::_('HIKA_DEFAULT'));
		if(!empty($joomEditors)){
			foreach($joomEditors as $myEditor){
				$this->values[] = JHTML::_('select.option', $myEditor->element,$myEditor->name);
			}
		}
	}
	function display($map,$value){
		return JHTML::_('select.genericlist', $this->values, $map , 'size="1"', 'value', 'text', $value);
	}
}
