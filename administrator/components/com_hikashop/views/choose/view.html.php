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
class chooseViewchoose extends hikashopView
{
	function display($tpl = null)
	{
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}

	function searchfields(){
		$db = JFactory::getDBO();
		if(!HIKASHOP_J30){
			$columnTable = $db->getTableFields(hikashop_table('product'));
			$columns = reset($columnTable);
		} else {
			$columns = $db->getTableColumns(hikashop_table('product'));
		}

		$rows = array_keys($columns);

		$selected = JRequest::getVar('values','','','string');
		$selectedvalues = explode(',',$selected);
		$newRows = array();
		foreach($rows as $id => $oneRow){
			$obj = new stdClass();
			$obj->namekey = $oneRow;
			if(in_array($oneRow,$selectedvalues)){
				$obj->selected = true;
			}
			$newRows[]=$obj;
		}
		$this->assignRef('rows',$newRows);
		$controlName = JRequest::getString('control','params');
		$this->assignRef('controlName',$controlName);
	}
	function filters(){
		$db = JFactory::getDBO();
		$db->setQuery('SELECT * FROM '.hikashop_table('filter').' ORDER BY filter_ordering');
		$rows = $db->loadObjectList('filter_namekey');

		$selected = JRequest::getVar('values','','','string');
		$selectedvalues = explode(',',$selected);
		$newRows = array();
		foreach($rows as $namkey => $row){
			if(in_array($namkey,$selectedvalues)){
				$rows[$namkey]->selected = true;
			}
		}
		$this->assignRef('rows',$rows);
		$controlName = JRequest::getString('control','params');
		$this->assignRef('controlName',$controlName);
	}
}
