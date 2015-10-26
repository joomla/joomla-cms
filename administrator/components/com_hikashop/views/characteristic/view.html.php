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
class CharacteristicViewCharacteristic extends hikashopView {
	var $ctrl = 'characteristic';
	var $nameListing = 'CHARACTERISTICS';
	var $nameForm = 'CHARACTERISTICS';
	var $icon = 'characteristic';
	var $triggerView = true;

	function display($tpl = null) {
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}

	function listing() {
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.characteristic_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'asc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	= JFactory::getDBO();
		$searchMap = array('a.characteristic_value','a.characteristic_alias','a.characteristic_id');
		$filters = array('a.characteristic_parent_id=0');
		if(!empty($pageInfo->search)) {
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(' LIKE '.$searchVal.' OR ', $searchMap).' LIKE '.$searchVal;
		}

		$extrafilters = array();
		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onBeforeCharacteristicListing', array($this->paramBase, &$extrafilters, &$pageInfo, &$filters));
		$this->assignRef('extrafilters', $extrafilters);

		$query = ' FROM '.hikashop_table('characteristic').' AS a';
		if(!empty($filters))
			$query.= ' WHERE ('.implode(') AND (',$filters).')';

		if(!empty($pageInfo->filter->order->value))
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;

		$database->setQuery('SELECT a.*'.$query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search))
			$rows = hikashop_search($pageInfo->search,$rows,'characteristic_id');

		$database->setQuery('SELECT count(*)'.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);

		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_characteristic_manage','all'));
		$this->assignRef('manage',$manage);

		$this->toolbar = array(
			array('name' => 'addNew', 'display' => $manage),
			array('name' => 'editList', 'display' => $manage),
			array('name' => 'deleteList', 'display' => hikashop_isAllowed($config->get('acl_characteristic_view','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);

		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->getPagination();
	}

	function form(){
		$characteristic_id = $this->editpopup();
		if(!empty($characteristic_id)){
			$task='edit';
		}else{
			$task='add';
		}
		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&characteristic_id='.$characteristic_id);

		$this->toolbar = array(
			'save',
			array('name' => 'save2new', 'display' => version_compare(JVERSION,'1.7','>=')),
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);

		$popup = hikashop_get('helper.popup');
		$this->assignRef('popup', $popup);

	}

	function editpopup(){
		$characteristic_id = hikashop_getCID('characteristic_id');
		$class = hikashop_get('class.characteristic');
		if(!empty($characteristic_id)){
			$element = $class->get($characteristic_id,true);
			if($element && empty($element->characteristic_parent_id)){
				$database	= JFactory::getDBO();
				$config =& hikashop_config();
				$sort = $config->get('characteristics_values_sorting');
				if($sort=='old'){
					$order = 'characteristic_id ASC';
				}elseif($sort=='alias'){
					$order = 'characteristic_alias ASC';
				}elseif($sort=='ordering'){
					$order = 'characteristic_ordering ASC';
				}else{
					$order = 'characteristic_value ASC';
				}
				$query = 'SELECT * FROM '.hikashop_table('characteristic').' WHERE characteristic_parent_id = '.$characteristic_id.' ORDER BY '.$order;
				$database->setQuery($query);
				$element->values = $database->loadObjectList();
			}
		}else{
			$element = JRequest::getVar('fail');
			if(empty($element)){
				$element = new stdClass();
			}
		}

		$this->assignRef('element',$element);
		jimport('joomla.html.pane');
		$config =& hikashop_config();
		$multilang_display=$config->get('multilang_display','tabs');
		if($multilang_display=='popups') $multilang_display = 'tabs';
		$tabs = hikashop_get('helper.tabs');
		$this->assignRef('tabs',$tabs);
		$translation = false;
		$transHelper = hikashop_get('helper.translation');
		if($transHelper && $transHelper->isMulti()){
			$translation = true;
			$transHelper->load('hikashop_characteristic',@$element->characteristic_id,$element);
			$this->assignRef('transHelper',$transHelper);
		}
		$js = '
		function deleteRow(divName,inputName,rowName){
			var d = document.getElementById(divName);
			var olddiv = document.getElementById(inputName);
			if(d && olddiv){
				d.removeChild(olddiv);
				document.getElementById(rowName).style.display=\'none\';
			}
			return false;
		}
		';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
		$this->assignRef('cid',$characteristic_id);
		$this->assignRef('translation',$translation);
		$toggle=hikashop_get('helper.toggle');
		$this->assignRef('toggle',$toggle);
		return $characteristic_id;
	}

	function addcharacteristic(){
		$element = JRequest::getInt( 'cid');
		$rows = array();
		if(!empty($element)){
			$database	= JFactory::getDBO();
			$query = 'SELECT * FROM '.hikashop_table('characteristic').' WHERE characteristic_id ='.$element;
			$database->setQuery($query);
			$rows = $database->loadObjectList();
			$document= JFactory::getDocument();
			$id = JRequest::getInt('id');
			$js = "window.hikashop.ready( function() {
					window.top.deleteRow('characteristic_div_".$rows[0]->characteristic_id.'_'.$id."','characteristic[".$rows[0]->characteristic_id."][".$id."]','characteristic_".$rows[0]->characteristic_id.'_'.$id."');
					var dstTable = window.top.document.getElementById('characteristic_listing');
					var srcTable = document.getElementById('result');
					for (var c = 0,m=srcTable.rows.length;c<m;c++){
						var rowData = srcTable.rows[c].cloneNode(true);
						dstTable.appendChild(rowData);
					}
					window.parent.hikashop.closeBox();
			});";
			$document->addScriptDeclaration($js);
		}
		$this->assignRef('rows',$rows);
		$image=hikashop_get('helper.image');
		$this->assignRef('image',$image);
		$popup = hikashop_get('helper.popup');
		$this->assignRef('popup', $popup);
	}

	function selectcharacteristic(){
		$this->listing();
	}

	function usecharacteristic(){
		$characteristics = JRequest::getVar( 'cid', array(), '', 'array' );
		$rows = array();
		$js="window.top.hikashop.closeBox();";
		if(!empty($characteristics) && count($characteristics)){
			JArrayHelper::toInteger($characteristics);
			$database	= JFactory::getDBO();
			$query = 'SELECT * FROM '.hikashop_table('characteristic').' WHERE characteristic_id IN ('.implode(',',$characteristics).') OR characteristic_parent_id IN ('.implode(',',$characteristics).') ORDER BY characteristic_ordering ASC, characteristic_value ASC';
			$database->setQuery($query);
			$rows = $database->loadObjectList();
			if(!empty($rows)){
				$unsetList = array();
				foreach($rows as $key => $characteristic){
					if(!empty($characteristic->characteristic_parent_id)){
						$unsetList[]=$key;
						foreach($rows as $key2 => $characteristic2){
							if($characteristic->characteristic_parent_id==$characteristic2->characteristic_id){
								$rows[$key2]->values[$characteristic->characteristic_id]=$characteristic->characteristic_value;
								break;
							}
						}
					}
				}
				if(!empty($unsetList)){
					foreach($unsetList as $item){
						unset($rows[$item]);
					}
					$rows = array_values($rows);
				}
			}

			$totalVariants = 1;
			foreach($rows as $row){
				$totalVariants = count($row->values) * $totalVariants;
			}
			if($totalVariants > 200){
				$optionsLink = 'http://www.hikashop.com/support/documentation/integrated-documentation/19-hikashop-product-form.html#options';
				$customLink = 'http://www.hikashop.com/support/documentation/42-hikashop-field-form.html';
				$js="
					var alertMessage = window.top.document.getElementById('hikashop_product_characteristics_message');
					alertMessage.style.display = 'inherit';
					alertMessage.innerHTML = '".str_replace("'","\'",str_replace("\'","'",JText::sprintf('TOO_MANY_VARIANTS',$optionsLink,$customLink)))."';
					".$js;
			}

			$js="
				var dstTable = window.top.document.getElementById('characteristic_listing');
				var srcTable = document.getElementById('result');
				for (var c = 0,m=srcTable.rows.length;c<m;c++){
					var rowData = srcTable.rows[c].cloneNode(true);
					dstTable.appendChild(rowData);
				}
				".$js;
		}

		$this->assignRef('rows',$rows);
		$document= JFactory::getDocument();
		$js = "window.hikashop.ready( function() {".$js."});";
		$document->addScriptDeclaration($js);
		$characteristicHelper = hikashop_get('type.characteristic');
		$this->assignRef('characteristicHelper',$characteristicHelper);
	}
}
