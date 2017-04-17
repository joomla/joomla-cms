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
class WarehouseViewWarehouse extends hikashopView {
	var $type = 'warehouse';
	var $ctrl = 'warehouse';
	var $nameListing = 'WAREHOUSE';
	var $nameForm = 'WAREHOUSE';
	var $icon = 'warehouse';

	function display($tpl = null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}

	function listing() {
		$app = JFactory::getApplication();
		$database = JFactory::getDBO();
		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);

		$pageInfo = $this->getPageInfo('a.warehouse_id');

		$filters = array();
		$order = '';
		$searchMap = array('a.warehouse_id','a.warehouse_name','a.warehouse_description');
		$this->processFilters($filters, $order, $searchMap);

		$query = ' FROM '.hikashop_table('warehouse').' AS a'.$filters.$order;
		$this->getPageInfoTotal($query, '*');
		$database->setQuery('SELECT a.*'.$query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $database->loadObjectList();

		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'warehouse_id');
		}
		$database->setQuery('SELECT count(*)'.$query );
		$pageInfo->elements->page = count($rows);

		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->getPagination();
		$this->getOrdering('a.warehouse_ordering', true);
		$this->assignRef('order',$order);
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);

		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_warehouse_manage','all'));
		$this->assignRef('manage',$manage);

		$this->toolbar = array(
			array('name' => 'addNew', 'display' => $manage),
			array('name' => 'editList', 'display' => $manage),
			array('name' => 'deleteList', 'check' => JText::_('HIKA_VALIDDELETEITEMS'), 'display' => hikashop_isAllowed($config->get('acl_warehouse_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);
	}

	function form(){
		$warehouse_id = hikashop_getCID('warehouse_id');
		$class = hikashop_get('class.warehouse');
		if(!empty($warehouse_id)){
			$element = $class->get($warehouse_id,true);
			$task='edit';
		}else{
			$element = new stdClass();
			$element->warehouse_published = 1;
			$task='add';
		}
		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&warehouse='.$warehouse_id);

		$this->toolbar = array(
			'save',
			array('name' => 'save2new', 'display' => version_compare(JVERSION,'1.7','>=')),
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing')
		);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'data[warehouse][warehouse_description]';
		$editor->content = @$element->warehouse_description;
		$this->assignRef('editor',$editor);
		$this->assignRef('element',$element);
		$toggle=hikashop_get('helper.toggle');
		$this->assignRef('toggle',$toggle);
		$warehouse=hikashop_get('type.warehouse');
		$this->assignRef('warehouse',$warehouse);
		$popup = hikashop_get('helper.popup');
		$this->assignRef('popup', $popup);
	}

	public function selection($tpl = null) {
		$this->listing($tpl, true);

		$elemStruct = array(
			'warehouse_name'
		);
		$this->assignRef('elemStruct', $elemStruct);

		$singleSelection = JRequest::getVar('single', false);
		$this->assignRef('singleSelection', $singleSelection);
	}
}
