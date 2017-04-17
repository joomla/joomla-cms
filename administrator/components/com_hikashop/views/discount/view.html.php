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
class DiscountViewDiscount extends hikashopView {
	var $type = '';
	var $ctrl= 'discount';
	var $nameListing = 'DISCOUNTS';
	var $nameForm = 'DISCOUNTS';
	var $icon = 'discount';

	function display($tpl = null) {
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function))
			$this->$function();
		parent::display($tpl);
	}

	function listing($extendedData = true) {
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.discount_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));
		$pageInfo->filter->filter_type = $app->getUserStateFromRequest( $this->paramBase.".filter_type",'filter_type','','string');
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 500;
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	= JFactory::getDBO();
		$searchMap = array('a.discount_code','a.discount_id');
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}

		$query = ' FROM '.hikashop_table('discount').' AS a';
		if(!empty($pageInfo->filter->filter_type)){
			switch($pageInfo->filter->filter_type){
				case 'all':
					break;
				default:
					$filters[] = 'a.discount_type = '.$database->Quote($pageInfo->filter->filter_type);
					if($pageInfo->filter->filter_type=='coupon'){
						$this->nameListing = 'COUPONS';
					}
					break;
			}
		}
		if(!empty($filters)){
			$query.= ' WHERE ('.implode(') AND (',$filters).')';
		}
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		$database->setQuery('SELECT a.*'.$query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'discount_id');
		}
		$database->setQuery('SELECT count(*)'.$query );
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		if($pageInfo->elements->page && $extendedData){

			$types = array('product','category','zone');
			foreach($types as $type){
				$ids = array();
				$key = 'discount_'.$type.'_id';
				foreach($rows as $row){
					if(empty($row->$key)) continue;

					$row->$key = explode(',',$row->$key);
					foreach($row->$key as $v){
						if(is_numeric($v)){
							$ids[$v]=$v;
						}else{
							$ids[$v]=$database->Quote($v);
						}
					}
				}
				if(!count($ids)){
					continue;
				}
				if($type=='zone'){
					$primary = $type.'_namekey';
					$name = $type.'_name_english';
				}else{
					$primary = $type.'_id';
					$name = $type.'_name';
				}
				$query = 'SELECT * FROM '.hikashop_table($type).' WHERE '.$primary.' IN ('.implode(',',$ids).')';
				$database->setQuery($query);
				$elements = $database->loadObjectList();

				foreach($rows as $k => $row){
					if(empty($row->$key)){
						continue;
					}
					$display = array();
					foreach($row->$key as $el){
						foreach($elements as $element){
							if($element->$primary==$el){
								$display[] = $element->$name;
								$found = true;
								break;
							}
						}
					}
					if(!count($display)){
						$display = array(JText::_(strtoupper($type).'_NOT_FOUND'));
					}
					$rows[$k]->$key = implode(', ',$display);
				}
			}
		}

		if($pageInfo->limit->value == 500) $pageInfo->limit->value = 100;

		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);

		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_discount_manage','all'));
		$this->assignRef('manage',$manage);
		$exportIcon = 'archive';
		if(HIKASHOP_J30) {
			$exportIcon = 'export';
		}
		$this->toolbar = array(
			array('name' => 'custom', 'icon' => $exportIcon, 'alt' => JText::_('HIKA_EXPORT'), 'task' => 'export', 'check' => false),
			array('name' => 'copy','display'=>$manage),
			array('name' => 'addNew','display'=>$manage),
			array('name' => 'editList','display'=>$manage),
			array('name' => 'deleteList','display'=>hikashop_isAllowed($config->get('acl_discount_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);

		$discountType = hikashop_get('type.discount');
		$this->assignRef('filter_type',$discountType);
		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->getPagination();

		$currencyHelper = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyHelper);
	}

	public function export() {
		$this->listing(false);
	}

	public function selection($tpl = null) {
		$this->listing($tpl, true);

		$elemStruct = array(
			'discount_id',
			'discount_code'
		);
		$this->assignRef('elemStruct', $elemStruct);

		$singleSelection = JRequest::getVar('single', false);
		$this->assignRef('singleSelection', $singleSelection);
	}

	public function useselection() {
		$selection = JRequest::getVar('cid', array(), '', 'array');
		$rows = array();
		$data = '';

		$elemStruct = array(
			'discount_id',
			'discount_code'
		);

		if(!empty($selection)) {
			JArrayHelper::toInteger($selection);
			$db = JFactory::getDBO();
			$query = 'SELECT a.* FROM '.hikashop_table('discount').' AS a  WHERE a.discount_id IN ('.implode(',',$selection).')';
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			if(!empty($rows)) {
				$data = array();
				foreach($rows as $v) {
					$d = '{id:'.$v->user_id;
					foreach($elemStruct as $s) {
						if($s == 'id')
							continue;
						$d .= ','.$s.':"'. str_replace('"', '\"', $v->$s).'"';
					}
					$data[] = $d.'}';
				}
				$data = '['.implode(',', $data).']';
			}
		}
		$this->assignRef('rows', $rows);
		$this->assignRef('data', $data);

		$confirm = JRequest::getVar('confirm', true, '', 'boolean');
		$this->assignRef('confirm', $confirm);
		if($confirm) {
			$js = 'window.hikashop.ready( function(){window.top.hikashop.submitBox('.$data.');});';
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration($js);
		}
	}

	function form() {
		$discount_id = hikashop_getCID('discount_id',false);
		if(!empty($discount_id)){
			$class = hikashop_get('class.discount');
			$element = $class->get($discount_id);
			$task='edit';
		}else{
			$element = JRequest::getVar('fail');
			if(empty($element)){
				$element = new stdClass();
				$app = JFactory::getApplication();
				$type = $app->getUserState( $this->paramBase.".filter_type");
				if(!in_array($type,array('all','nochilds'))){
					$element->discount_type = $type;
					$this->nameForm = 'HIKASHOP_COUPON';
				}else{
					$element->discount_type = 'discount';
				}
				$element->discount_published=1;
			}
			$task='add';
		}

		hikashop_setTitle(JText::_($this->nameForm), $this->icon,$this->ctrl.'&task='.$task.'&discount_id='.$discount_id);

		$this->toolbar = array(
			'save',
			array('name' => 'save2new', 'display' => version_compare(JVERSION,'1.7','>=')),
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);

		$this->assignRef('element', $element);

		$discountType = hikashop_get('type.discount');
		$this->assignRef('type', $discountType);

		$currencyType = hikashop_get('type.currency');
		$this->assignRef('currency', $currencyType);

		$categoryType = hikashop_get('type.categorysub');
		$categoryType->type = 'tax';
		$categoryType->field = 'category_id';
		$this->assignRef('categoryType', $categoryType);

		$popup = hikashop_get('helper.popup');
		$this->assignRef('popup', $popup);

		$nameboxType = hikashop_get('type.namebox');
		$this->assignRef('nameboxType', $nameboxType);
	}

	function select_coupon(){
		$badge = JRequest::getVar('badge','false');
		$this->assignRef('badge',$badge);
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.discount_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));
		$pageInfo->filter->filter_type = $app->getUserStateFromRequest( $this->paramBase.".filter_type",'filter_type','','string');
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 500;
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	= JFactory::getDBO();
		$searchMap = array('a.discount_code','a.discount_id');
		$filters = array();
		if($badge!='false'){ $filters[]='a.discount_type="discount"'; }
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$query = ' FROM '.hikashop_table('discount').' AS a';
		if($badge=='false' && !empty($pageInfo->filter->filter_type)){
			switch($pageInfo->filter->filter_type){
				case 'all':
					break;
				default:
					$filters[] = 'a.discount_type = '.$database->Quote($pageInfo->filter->filter_type);
					break;
			}
		}
		if(!empty($filters)){
			$query.= ' WHERE ('.implode(') AND (',$filters).')';
		}
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		$database->setQuery('SELECT a.*'.$query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'discount_id');
		}
		$database->setQuery('SELECT count(*)'.$query );
		$pageInfo->elements=new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		if($pageInfo->limit->value == 500) $pageInfo->limit->value = 100;

		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);

		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_discount_manage','all'));
		$this->assignRef('manage',$manage);
		$this->toolbar = array(
			array('name' => 'custom', 'icon' => 'copy', 'task' => 'copy', 'alt' => JText::_('HIKA_COPY'),'display'=>$manage),
			array('name' => 'addNew','display'=>$manage),
			array('name' => 'editList','display'=>$manage),
			array('name' => 'deleteList','display'=>hikashop_isAllowed($config->get('acl_discount_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);
		$discountType = hikashop_get('type.discount');
		$this->assignRef('filter_type',$discountType);
		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->getPagination();
		$currencyHelper = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyHelper);
	}

	function add_coupon(){
		$discounts = JRequest::getVar( 'cid', array(), '', 'array' );
		$rows = array();
		$filter='';
		$badge = JRequest::getVar( 'badge');
		if(!isset($badge)){ $badge='false'; }
		$this->assignRef('badge',$badge);
		if(!empty($discounts)){
			JArrayHelper::toInteger($discounts);
			$database	= JFactory::getDBO();
			if($badge=='false'){ $filter='AND discount_type="coupon"';}
			$query = 'SELECT * FROM '.hikashop_table('discount').' WHERE discount_id IN ('.implode(',',$discounts).') '.$filter;
			$database->setQuery($query);
			$rows = $database->loadObjectList();
		}
		$this->assignRef('rows',$rows);
		$document= JFactory::getDocument();
		if($badge=='false'){
			$js = "window.hikashop.ready( function() {
					var dstTable = window.parent.document.getElementById('coupon_listing');
					var srcTable = document.getElementById('result');
					for (var c = 0,m=srcTable.rows.length;c<m;c++){
						var rowData = srcTable.rows[c].cloneNode(true);
						dstTable.appendChild(rowData);
					}
					window.parent.hikashop.closeBox();
			});";
		}else{
			$js = "window.hikashop.ready( function() {
						var field = window.parent.document.getElementById('changeDiscount');
						var result = document.getElementById('result').innerHTML;
						field.innerHTML=result;
						window.parent.hikashop.closeBox();
				});";
		}
		$document->addScriptDeclaration($js);
	}

}
