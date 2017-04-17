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

class FieldViewField extends hikashopView{

	var $displayView = true;

	function display($tpl = null){
		$function = $this->getLayout();
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		if(method_exists($this,$function)) $this->$function();

		if($this->displayView) parent::display($tpl);
	}

	function form() {
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();

		$fieldid = hikashop_getCID('field_id');
		$fieldsClass = hikashop_get('class.field');
		if(!empty($fieldid)) {
			$field = $fieldsClass->getField($fieldid);
			$data = null;
			$allFields = $fieldsClass->getFields('', $data, $field->field_table);
		} else {
			$field = new stdClass();
			if(hikashop_level(1)) {
				$field->field_table = $app->getUserStateFromRequest($this->paramBase.".filter_table",'filter_table','product','string');
			} else {
				$field->field_table = 'address';
			}
			$field->field_published = 1;
			$field->field_type = 'text';
			$field->field_backend = 1;
			$allFields = null;
		}
		$this->assignRef('allFields',$allFields);

		$fieldTitle = '';
		if(!empty($field->field_id))
			$fieldTitle = ' : '.$field->field_namekey;
		hikashop_setTitle(JText::_('FIELD').$fieldTitle,'field','field&task=edit&field_id='.$fieldid);

		$jsDrop = '';
		if(HIKASHOP_BACK_RESPONSIVE && $app->isAdmin()) {
			$jsDrop = 'jQuery(input3).chosen();';
		}

		hikashop_loadJslib('jquery');

		$script = '
		function addLine() {
			window.hikashop.dupRow("hikashop_field_values_table_template", {
				"TITLE":"field_values[title][]",
				"VALUE":"field_values[value][]",
				"DISABLED":"field_values[disabled][]"
			})
		}

		function setVisible(value){
			if(value=="product" || value=="item" || value=="category"){
				document.getElementById(\'category_field\').style.display = "";
			}else{
				document.getElementById(\'category_field\').style.display = \'none\';
			}
		}';

		$doc->addScriptDeclaration($script);

		$this->toolbar = array(
			'save',
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => 'field-form')
		);

		$this->assignRef('field',$field);
		$this->assignRef('fieldsClass',$fieldsClass);

		$fieldType = hikashop_get('type.fields');
		$this->assignRef('fieldtype',$fieldType);

		$zoneType = hikashop_get('type.zone');
		$this->assignRef('zoneType',$zoneType);

		$allowType = hikashop_get('type.allow');
		$this->assignRef('allowType',$allowType);

		$displayOptions = array();
		$this->assignRef('displayOptions',$displayOptions);

		if(hikashop_level(1)){
			$tabletype = hikashop_get('type.table');
			$this->assignRef('tabletype',$tabletype);
		}

		if(hikashop_level(2)){
			$limitParent = hikashop_get('type.limitparent');
			$this->assignRef('limitParent',$limitParent);
			if(!empty($field->field_options['product_id'])) {
				$product = hikashop_get('class.product');
				$element = $product->get($field->field_options['product_id']);
				$this->assignRef('element',$element);
			}
		}

		$categories = array();
		if(isset($this->field->field_categories)){
			$this->field->field_categories=$this->field->field_categories;
			$this->categories= explode(",", $this->field->field_categories);
			unset($this->categories[0]);
			unset($this->categories[count($this->categories)]);
			if(!empty($this->categories)){
				foreach($this->categories as $k => $cat){
					if(!isset($categories[$k]))
						$categories[$k] = new stdClass();
					$categories[$k]->category_id=$cat;
				}
				$db = JFactory::getDBO();
				$db->setQuery('SELECT * FROM '.hikashop_table('category').' WHERE category_id IN ('.implode(',',$this->categories).')');
				$cats = $db->loadObjectList('category_id');
				foreach($this->categories as $k => $cat){
					if(!empty($cats[$cat])){
						$categories[$k]->category_name = $cats[$cat]->category_name;
					}else{
						$categories[$k]->category_name = JText::_('CATEGORY_NOT_FOUND');
					}
				}
			}
			$this->categories = $categories;
		}

		JHTML::_('behavior.modal');
		$popup = hikashop_get('helper.popup');
		$this->assignRef('popup', $popup);

		$nameboxType = hikashop_get('type.namebox');
		$this->assignRef('nameboxType', $nameboxType);

		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onCustomfieldEdit', array(&$field, &$this));
	}

	function listing(){
		$db = JFactory::getDBO();
		$filter = '';
		if(hikashop_level(1)){
			$app = JFactory::getApplication();
			$selectedType = $app->getUserStateFromRequest( $this->paramBase.".filter_table",'filter_table','','string');
			if(!empty($selectedType)){
				$filter = ' WHERE a.field_table='.$db->Quote($selectedType);
			}
			$table = hikashop_get('type.table');
			$this->assignRef('tabletype',$table);
		}else{
			$filter = ' WHERE a.field_table=\'address\' OR a.field_table LIKE \'plg.%\'';
		}
		$db->setQuery('SELECT a.* FROM '.hikashop_table('field').' AS a'.$filter.' ORDER BY a.`field_table` ASC, a.`field_ordering` ASC');
		$rows = $db->loadObjectList();

		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_field_manage','all'));
		$this->assignRef('manage',$manage);
		$this->toolbar = array(
			array('name'=>'addNew','display'=>$manage),
			array('name'=>'editList','display'=>$manage),
			array('name'=>'deleteList','display'=>hikashop_isAllowed($config->get('acl_field_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => 'field-listing'),
			'dashboard'
		);

		$total = count($rows);

		$pagination = hikashop_get('helper.pagination', $total, 0, $total);

		hikashop_setTitle(JText::_('FIELDS'),'field','field');

		$this->assignRef('rows',$rows);
		$toggle = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggle);
		$this->assignRef('pagination',$pagination);
		$this->assignRef('selectedType',$selectedType);
		$type = hikashop_get('type.fields');
		$type->load();
		$this->assignRef('fieldtype',$type);
		$fieldClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass',$fieldClass);
	}


	function state(){
		$namekey = JRequest::getCmd('namekey', '');
		if(!empty($namekey)) {
			$field_namekey = JRequest::getCmd('field_namekey', '');
			if(empty($field_namekey))
				$field_namekey = 'address_state';

			$field_id = JRequest::getCmd('field_id', '');
			if(empty($field_id))
				$field_id = 'address_state';

			$field_type = JRequest::getCmd('field_type', '');
			if(empty($field_type))
				$field_type = 'address';

			$class = hikashop_get('type.country');
			echo $class->displayStateDropDown($namekey, $field_id, $field_namekey, $field_type);
		}
		exit;
	}
}
