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
class ModulesViewModules extends hikashopView{
	var $include_module = false;
	var $ctrl= 'modules';
	var $nameListing = 'MODULES';
	var $nameForm = 'MODULE';
	var $icon = 'module';

	function display($tpl = null,$params=null)
	{
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function($params);
		parent::display($tpl);
	}

	function form($cid=null)
	{
		$this->noForm = false;

		if(empty($cid))
			$cid = hikashop_getCID('id');
		else
			$this->noForm = true;

		if(empty($cid)){
			$element = new stdClass();
			$config = hikashop_config();
			$element->hikashop_params = $config->get('default_params');
			$element->position 	= 'left';
			$element->showtitle = true;
			$element->published = 1;
			$element->module = 'mod_hikashop';
			$element->hikashop_params['transition_effect'] = 'quad';
			$element->hikashop_params['carousel_effect_duration'] = 800;
			$element->hikashop_params['one_by_one'] = true;
			$element->hikashop_params['auto_slide'] = true;
			$element->hikashop_params['auto_slide_duration'] = 1800;
			$element->hikashop_params['pagination_type'] = 'dot';
			$element->hikashop_params['pagination_position'] = 'bottom';

			$element->hikashop_params['layout_type'] = 'inherit';
			$element->hikashop_params['columns'] = '';
			$element->hikashop_params['limit'] = '';
			$element->hikashop_params['random'] = '-1';
			$element->hikashop_params['order_dir'] = 'inherit';
			$element->hikashop_params['filter_type'] = 2;
			$element->hikashop_params['product_order'] = 'inherit';
			$element->hikashop_params['product_synchronize'] = 4;
			$element->hikashop_params['recently_viewed'] = '-1';
			$element->hikashop_params['add_to_cart'] = '-1';
			$element->hikashop_params['add_to_wishlist'] = '-1';
			$element->hikashop_params['link_to_product_page'] = '-1';
			$element->hikashop_params['show_vote_product'] = '-1';
			$element->hikashop_params['show_price'] = '-1';
			$element->hikashop_params['price_with_tax'] = 3;
			$element->hikashop_params['show_original_price'] = '-1';
			$element->hikashop_params['show_discount'] = 3;
			$element->hikashop_params['price_display_type'] = 'inherit';
			$element->hikashop_params['display_custom_item_fields'] = '-1';
			$element->hikashop_params['display_badges'] = '-1';
			$element->hikashop_params['category_order'] = 'inherit';
			$element->hikashop_params['child_display_type'] = 'inherit';
			$element->hikashop_params['child_limit'] = '';
			$element->hikashop_params['links_on_main_categories'] = '-1';
			$element->hikashop_params['number_of_products'] = '-1';
			$element->hikashop_params['only_if_products'] = '-1';
			$element->hikashop_params['div_item_layout_type'] = 'inherit';
			$element->hikashop_params['background_color'] = '';
			$element->hikashop_params['margin'] = '';
			$element->hikashop_params['border_visible'] = '-1';
			$element->hikashop_params['rounded_corners'] = '-1';
			$element->hikashop_params['text_center'] = '-1';
			$element->hikashop_params['ul_class_name'] = '';
			$element->hikashop_params['no_form']=$this->noForm;

			$control = 'config[params_0]';
			$task='add';
		}else{
			$modulesClass = hikashop_get('class.modules');
			$element = $modulesClass->get($cid);
			$control = 'config[params_'.$cid.']';
			$task='edit';
			if(!isset($element->hikashop_params['link_to_product_page'])){
				$element->hikashop_params['link_to_product_page'] = '1';
			}
			$element->hikashop_params['no_form']=$this->noForm;
			$element->hikashop_params['cid']=$cid;
		}

		$this->_loadCategory($element);
		if(!$this->noForm) hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&cid[]='.$cid);

		if(!empty($cid)){
			if(!HIKASHOP_J16){
				$url = JRoute::_('index.php?option=com_modules&client=0&task=edit&cid[]='.$element->id);
			}else{
				$url = JRoute::_('index.php?option=com_modules&task=module.edit&id='.$element->id);
			}
			if (!$this->noForm)
				$this->toolbarJoomlaModule = array('name'=>'link','icon'=>'upload','alt'=> JText::_('JOOMLA_MODULE_OPTIONS'),'url'=>$url);
		}

		$this->assignRef('element',$element);
		$this->assignRef('control',$control);

		$extra_blocks = array(
			'products' => array(),
			'layouts' => array()
		);
		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onHkContentParamsDisplay', array('module', $control, &$element, &$extra_blocks));
		$this->assignRef('extra_blocks', $extra_blocks);

		$this->type = 'cart';
		if($element->module == 'mod_hikashop_wishlist')
			$this->type = 'wishlist';

		$js = null;
		$jsHide="
		function setVisible(value,option){
			value=parseInt(value);
			if(value==1){
				document.getElementById('carousel_type_'+option).style.display = '';
				document.getElementById('slide_direction_'+option).style.display = '';
				document.getElementById('transition_effect_'+option).style.display = '';
				document.getElementById('carousel_effect_duration_'+option).style.display = '';
				document.getElementById('product_by_slide_'+option).style.display = '';
				document.getElementById('slide_one_by_one_'+option).style.display = '';
				document.getElementById('auto_slide_'+option).style.display = '';
				document.getElementById('auto_slide_duration_'+option).style.display = '';
				document.getElementById('slide_pagination_'+option).style.display = '';
				document.getElementById('pagination_width_'+option).style.display = '';
				document.getElementById('pagination_height_'+option).style.display = '';
				document.getElementById('pagination_position_'+option).style.display = '';
				document.getElementById('display_button_'+option).style.display = '';
			}
			else{
				document.getElementById('carousel_type_'+option).style.display = 'none';
				document.getElementById('slide_direction_'+option).style.display = 'none';
				document.getElementById('transition_effect_'+option).style.display = 'none';
				document.getElementById('carousel_effect_duration_'+option).style.display = 'none';
				document.getElementById('product_by_slide_'+option).style.display = 'none';
				document.getElementById('slide_one_by_one_'+option).style.display = 'none';
				document.getElementById('auto_slide_'+option).style.display = 'none';
				document.getElementById('auto_slide_duration_'+option).style.display = 'none';
				document.getElementById('slide_pagination_'+option).style.display = 'none';
				document.getElementById('pagination_width_'+option).style.display = 'none';
				document.getElementById('pagination_height_'+option).style.display = 'none';
				document.getElementById('pagination_position_'+option).style.display = 'none';
				document.getElementById('display_button_'+option).style.display = 'none';
			}
		}

		function setVisibleAutoSlide(value,option){
			value=parseInt(value);
			if(value==1){
				document.getElementById('auto_slide_duration_'+option).style.display = '';
			}else{
				document.getElementById('auto_slide_duration_'+option).style.display = 'none';
			}
		}

		function setVisiblePagination(value,option){
			if(value==\"no_pagination\"){
				document.getElementById('pagination_width_'+option).style.display = 'none';
				document.getElementById('pagination_height_'+option).style.display = 'none';
				document.getElementById('pagination_position_'+option).style.display = 'none';
			}else if(value==\"thumbnails\"){
				document.getElementById('pagination_width_'+option).style.display = '';
				document.getElementById('pagination_height_'+option).style.display = '';
				document.getElementById('pagination_position_'+option).style.display = '';
			}else{
				document.getElementById('pagination_width_'+option).style.display = 'none';
				document.getElementById('pagination_height_'+option).style.display = 'none';
				document.getElementById('pagination_position_'+option).style.display = '';
			}
		}

		function setVisibleEffect(value,option){
			if(value==\"fade\"){
				document.getElementById('transition_effect_'+option).style.display = 'none';
				document.getElementById('slide_one_by_one_'+option).style.display = 'none';
			}else{
				document.getElementById('transition_effect_'+option).style.display = '';
				document.getElementById('slide_one_by_one_'+option).style.display = '';
			}
		}

		function setVisibleLayoutEffect(value, option){
			if(value==\"slider_vertical\" || value==\"slider_horizontal\"){
				document.getElementById('product_effect_'+option).style.display = '';
				document.getElementById('product_effect_duration_'+option).style.display = '';
			}else if(value==\"fade\"){
				document.getElementById('product_effect_'+option).style.display = 'none';
				document.getElementById('product_effect_duration_'+option).style.display = '';
			}else if(value==\"img_pane\"){
				document.getElementById('product_effect_'+option).style.display = 'none';
				document.getElementById('product_effect_duration_'+option).style.display = 'none';
			}else{
				document.getElementById('product_effect_'+option).style.display = 'none';
				document.getElementById('product_effect_duration_'+option).style.display = 'none';
			}
		}
		";
	 	$doc = JFactory::getDocument();
	 	$doc->addScriptDeclaration($jsHide);

		$this->assignRef('js',$js);
		$this->_assignTypes();
	}

	function _loadCategory(&$element){
		if(empty($element->hikashop_params['selectparentlisting'])){
			$db 	= JFactory::getDBO();
			$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE category_type=\'root\' AND category_parent_id=0 LIMIT 1';
			$db->setQuery($query);
			$root = $db->loadResult();
			$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE category_type=\'product\' AND category_parent_id='.$root.' LIMIT 1';
			$db->setQuery($query);
			$element->hikashop_params['selectparentlisting'] = $db->loadResult();
		}
		if(!empty($element->hikashop_params['selectparentlisting'])){
			$class=hikashop_get('class.category');
			$element->category = $class->get($element->hikashop_params['selectparentlisting']);
		}
	}

	function _assignTypes(){
		$js = "
		var old_value_layout = '';
		var old_value_content = '';
		function switchPanelMod(name,options,type,control){
			var len = options.length;
			if(type=='layout'){
				if(name=='table'){
					el4 = document.getElementById('content_select'+control);
					if(el4 && (el4.value=='category' || el4.value=='manufacturer')){
						el5 = document.getElementById('layout_select'+control);
						el5.value = old_value_layout;
						alert('".JText::_('CATEGORY_CONTENT_DOES_NOT_SUPPORT_TABLE_LAYOUT',true)."');
						return;
					}
				}
				el3 = document.getElementById('number_of_columns'+control);
				if(el3){
					if(name=='table'){
						el3.style.display='none';
					}else{
						el3.style.display='';
					}
				}
			}else if(type=='content'){
				if(name=='manufacturer'){
					name = 'category';
				}
				if(name=='category'){
					el4 = document.getElementById('layout_select'+control);
					if(el4 && el4.value=='table'){
						el5 = document.getElementById('content_select'+control);
						el5.value = old_value_content;
						alert('".JText::_('CATEGORY_CONTENT_DOES_NOT_SUPPORT_TABLE_LAYOUT',true)."');
						return;
					}
				}
			}
			for (var i = 0; i < len; i++){
				var el = document.getElementById(type+'_'+options[i]+control);
				if(el) el.style.display='none';
			}
			if(type=='layout'){
				old_value_layout = name;
			}else{
				old_value_content = name;
			}
			var el2 = document.getElementById(type+'_'+name+control);
			if(el2) el2.style.display='block';
		}
		function switchDisplay(value,name,activevalue,control){
			var el = document.getElementById(name+control);
			if(el){
				if(value==activevalue){
					el.style.display='';
				}else{
					el.style.display='none';
				}
			}
		}
		function hikashopToggleCart(minicart){
			if(minicart>0){
				displayStatus ='none';
			}else{
				displayStatus = '';
			}
			var el = document.getElementById('cart_price');
			if(el){
				el.style.display=displayStatus;
			}
			var el = document.getElementById('cart_prod_name');
			if(el){
				el.style.display=displayStatus;
			}
		}
		";
		$document= JFactory::getDocument();
		$document->addScriptDeclaration($js);
		JHTML::_('behavior.modal');
		$popup = hikashop_get('helper.popup');
		$this->assignRef('popup',$popup);
		$colorType = hikashop_get('type.color');
		$this->assignRef('colorType',$colorType);
		$listType = hikashop_get('type.list');
		$this->assignRef('listType',$listType);

		$contentType = hikashop_get('type.content');
		$this->assignRef('contentType',$contentType);
		$layoutType = hikashop_get('type.layout');
		$this->assignRef('layoutType',$layoutType);
		$orderdirType = hikashop_get('type.orderdir');
		$this->assignRef('orderdirType',$orderdirType);
		$orderType = hikashop_get('type.order');
		$this->assignRef('orderType',$orderType);
		$itemType = hikashop_get('type.item');
		$this->assignRef('itemType',$itemType);
		$effectType = hikashop_get('type.effect');
		$this->assignRef('effectType',$effectType);
		$directionType = hikashop_get('type.direction');
		$this->assignRef('directionType',$directionType);
		$transition_effectType = hikashop_get('type.transition_effect');
		$this->assignRef('transition_effectType',$transition_effectType);
		$slide_paginationType = hikashop_get('type.slide_pagination');
		$this->assignRef('slide_paginationType',$slide_paginationType);
		$positionType = hikashop_get('type.position');
		$this->assignRef('positionType',$positionType);
		$childdisplayType = hikashop_get('type.childdisplay');
		$this->assignRef('childdisplayType',$childdisplayType);
		$pricetaxType = hikashop_get('type.pricetax');
		$this->assignRef('pricetaxType',$pricetaxType);
		$priceDisplayType = hikashop_get('type.pricedisplay');
		$this->assignRef('priceDisplayType',$priceDisplayType);
		$productSyncType = hikashop_get('type.productsync');
		$this->assignRef('productSyncType',$productSyncType);
		$discountDisplayType = hikashop_get('type.discount_display');
		$this->assignRef('discountDisplayType',$discountDisplayType);
		if(!HIKASHOP_J16){
			$query = 'SELECT a.name, a.id as itemid, b.title  FROM `#__menu` as a LEFT JOIN `#__menu_types` as b on a.menutype = b.menutype ORDER BY b.title ASC,a.ordering ASC';
		}elseif(!HIKASHOP_J30){
			$query = 'SELECT a.title as name, a.id as itemid, b.title  FROM `#__menu` as a LEFT JOIN `#__menu_types` as b on a.menutype = b.menutype WHERE a.client_id=0 AND (a.link LIKE \'%view=product%\' OR a.link LIKE \'%view=category%\') ORDER BY b.title ASC,a.ordering ASC';
		}else{
			$query = 'SELECT a.title as name, a.id as itemid, b.title  FROM `#__menu` as a LEFT JOIN `#__menu_types` as b on a.menutype = b.menutype WHERE a.client_id=0 AND (a.link LIKE \'%view=product%\' OR a.link LIKE \'%view=category%\') ORDER BY b.title ASC';
		}
		$db 	= JFactory::getDBO();
		$db->setQuery($query);
		$joomMenus = $db->loadObjectList();
		$menuvalues = array();
		$menuvalues[] = JHTML::_('select.option', '0',JText::_('HIKA_NONE'));
		$lastGroup = '';
		foreach($joomMenus as $oneMenu){
			if($oneMenu->title != $lastGroup){
				if(!empty($lastGroup)) $menuvalues[] = JHTML::_('select.option', '</OPTGROUP>');
				$menuvalues[] = JHTML::_('select.option', '<OPTGROUP>',$oneMenu->title);
				$lastGroup = $oneMenu->title;
			}
			$menuvalues[] = JHTML::_('select.option', $oneMenu->itemid,$oneMenu->name);
		}
		$this->assignRef('hikashop_menu',$menuvalues);
		if(!$this->noForm) $this->toolbar = array(
			'save',
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);
		if(!empty($this->toolbarJoomlaModule)){
			array_unshift($this->toolbar,'|');
			array_unshift($this->toolbar,$this->toolbarJoomlaModule);
		}
	}


	function listing(){
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	= JFactory::getDBO();

		$filters = array('(module = \'mod_hikashop\' OR module = \'mod_hikashop_cart\' OR module = \'mod_hikashop_wishlist\')');
		$searchMap = array('module','title');

		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped(JString::strtolower(trim($pageInfo->search)),true).'%\'';
			$filters[] =  implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		if(!empty($filters)){
			$filters = ' WHERE ('. implode(') AND (',$filters).')';
		}else{
			$filters = '';
		}
		$query = ' FROM '.hikashop_table('modules',false).' '.$filters.$order;
		$database->setQuery('SELECT *'.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'id');
		}
		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$config =& hikashop_config();
		foreach($rows as $k => $row){
			$rows[$k]->hikashop_params = $config->get('params_'.$row->id);
			if(empty($rows[$k]->hikashop_params)){
				$rows[$k]->hikashop_params = $config->get('default_params');
			}
		}
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$this->getPagination();

		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_modules_manage','all'));
		$this->assignRef('manage',$manage);
		$this->toolbar = array(
			array('name'=>'addNew','display'=>$manage),
			array('name'=>'editList','display'=>$manage),
			array('name'=>'deleteList','display'=>hikashop_isAllowed($config->get('acl_modules_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);

	}

	function selectmodules(){
		$this->modules = JRequest::getString('modules','');

		$query='SELECT * FROM '.hikashop_table('modules',false). ' WHERE module IN (\'mod_hikashop\')';
		$this->database = JFactory::getDBO();
		$this->database->setQuery($query);
		$rows = $this->database->loadObjectList();

		if(!empty($this->modules)){
			$this->modules=explode(',',$this->modules);
			JArrayHelper::toInteger($this->modules);

			foreach($this->modules as $i=>$id){
				foreach($rows as $k => $row){
					if($row->id==$id){
						$rows[$k]->module_ordering = $i+1;
						$rows[$k]->module_used = 1;
						break;
					}
				}
			}
		}
		foreach(get_object_vars($this) as $key => $var){
			$this->assignRef($key,$this->$key);
		}
		$this->assignRef('rows',$rows);
	}

	function savemodules(){
		$modules = array();
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		foreach($formData['module']['used'] as $id => $used){
			if((bool)$used){
				$modules[$formData['module']['ordering'][$id]]=$id;
			}
		}
		if(!empty($modules)){
			ksort($modules);
			$modules = array_values($modules);
		}
		$this->assignRef('modules',$modules);
		$control = JRequest::getString('control','');
		$name = JRequest::getString('name','');
		if(empty($control) || empty($name)){
			$id = 'modules_display';
		}else{
			$id = $control.$name;
		}


		$document = JFactory::getDocument();
		$js = "window.hikashop.ready( function() {
				window.top.document.getElementById('".$id."').value = document.getElementById('result').innerHTML;
				window.parent.hikashop.closeBox();
		});";
		$document->addScriptDeclaration($js);
	}

	function options(&$params){
		$this->id = $params->get('id');
		$this->name = str_replace('[]', '', $params->get('name'));
		$this->element = $params->get('value');
		$this->layoutType = hikashop_get('type.layout');
		$this->orderdirType = hikashop_get('type.orderdir');
		$this->childdisplayType = hikashop_get('type.childdisplay');
		$this->orderType = hikashop_get('type.order');
		$this->listType = hikashop_get('type.list');
		$this->nameboxType = hikashop_get('type.namebox');
		$this->effectType = hikashop_get('type.effect');
		$this->directionType = hikashop_get('type.direction');
		$this->transition_effectType = hikashop_get('type.transition_effect');
		$this->slide_paginationType = hikashop_get('type.slide_pagination');
		$this->positionType = hikashop_get('type.position');
		$this->pricetaxType = hikashop_get('type.pricetax');
		$this->discountDisplayType = hikashop_get('type.discount_display');
		$this->priceDisplayType = hikashop_get('type.priceDisplay');
		$this->colorType = hikashop_get('type.color');
		$this->itemType = hikashop_get('type.item');
		$this->contentType = hikashop_get('type.content');
		$this->productSyncType = hikashop_get('type.productsync');
		$this->arr = array(
			JHTML::_('select.option',  '-1', JText::_( 'HIKA_INHERIT' ) ),
			JHTML::_('select.option',  '1', JText::_( 'HIKASHOP_YES' ) ),
			JHTML::_('select.option',  '0', JText::_( 'HIKASHOP_NO' ) ),
		);
		$this->arr[0]->class = 'btn-primary';
		$this->arr[1]->class = 'btn-success';
		$this->arr[2]->class = 'btn-danger';

		$db = JFactory::getDBO();
		$query = 'SELECT a.title as name, a.id as itemid, b.title  FROM `#__menu` as a LEFT JOIN `#__menu_types` as b on a.menutype = b.menutype WHERE a.client_id=0 AND (a.link LIKE \'%view=product%\' OR a.link LIKE \'%view=category%\') ORDER BY b.title ASC';
		$db->setQuery($query);
		$joomMenus = $db->loadObjectList();
		$menuvalues = array();
		$menuvalues[] = JHTML::_('select.option', '0',JText::_('HIKA_NONE'));
		$lastGroup = '';
		foreach($joomMenus as $oneMenu){
			if($oneMenu->title != $lastGroup){
				if(!empty($lastGroup)) $menuvalues[] = JHTML::_('select.option', '</OPTGROUP>');
				$menuvalues[] = JHTML::_('select.option', '<OPTGROUP>',$oneMenu->title);
				$lastGroup = $oneMenu->title;
			}
			$menuvalues[] = JHTML::_('select.option', $oneMenu->itemid,$oneMenu->name);
		}
		$this->assignRef('hikashop_menu',$menuvalues);

		$cid = JRequest::getInt('id','');
		if(empty($cid))
			$cid = hikashop_getCID();

		$module = $this->getModuleData($cid);
		if(empty($this->element)) {
			$this->element = $module->hikashop_params;
		}

		if(isset($this->element['content_type']))
			$this->type = $this->element['content_type'];
		elseif(isset($module->hikashop_params['content_type']))
			$this->type = $module->hikashop_params['content_type'];
		else
			$this->type = 'product';

		$this->noForm = true;
		$config = hikashop_config();
		$this->default_params = $config->get('default_params');

		$extra_blocks = array(
			'products' => array(),
			'layouts' => array()
		);
		$element = new stdClass;
		$element->content_type = $this->type;
		$element->hikashop_params =& $this->element;
		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onHkContentParamsDisplay', array('module', $this->name, &$element, &$extra_blocks));
		$this->assignRef('extra_blocks', $extra_blocks);
	}

	protected function getModuleData($id){
		if(!empty($id)) {
			$modulesClass = hikashop_get('class.modules');
			$element = $modulesClass->get($id);
			if(!empty($element->content_type) && $element->content_type != 'product') {
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('HIKA_MODULE_TYPE_NOT_SUPPORTED'), 'error');
				if(!HIKASHOP_J16) {
					$url = JRoute::_('index.php?option=com_modules&task=edit&cid[]='.$id, false);
				} else {
					$url = JRoute::_('index.php?option=com_modules&task=item.edit&id='.$id, false);
				}
				$app->redirect($url);
			}
		}
		if(!isset($element->hikashop_params['layout_type']))
			$element->hikashop_params['layout_type'] = 'div';

		return $element;
	}
}
