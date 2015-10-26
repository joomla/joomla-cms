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
class MenusViewMenus extends hikashopView{
	var $ctrl= 'menus';
	var $nameListing = 'MENUS';
	var $nameForm = 'MENU';
	var $icon = 'menu';
	function display($tpl = null,$params=null){
		$this->config = hikashop_config();
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function($params);
		parent::display($tpl);
	}

	function _loadCategory(&$element){
		if(empty($element)) $element = new stdClass();
		if(!isset($element->hikashop_params)) $element->hikashop_params = array();
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
		function switchPanel(name,options,type){
			var len = options.length;
			if(type=='layout'){
				if(name=='table'){
					el4 = document.getElementById('content_select');
					if(el4 && (el4.value=='category' || el4.value=='manufacturer')){
						el5 = document.getElementById('layout_select');
						el5.value = old_value_layout;
						alert('".JText::_('CATEGORY_CONTENT_DOES_NOT_SUPPORT_TABLE_LAYOUT',true)."');
						return;
					}
				}
				el3 = document.getElementById('number_of_columns');
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
					el4 = document.getElementById('layout_select');
					if(el4 && el4.value=='table'){
						el5 = document.getElementById('content_select');
						el5.value = old_value_content;
						alert('".JText::_('CATEGORY_CONTENT_DOES_NOT_SUPPORT_TABLE_LAYOUT',true)."');
						return;
					}
				}
			}
			for (var i = 0; i < len; i++){
				var el = document.getElementById(type+'_'+options[i]);
				if(el) el.style.display='none';
			}
			if(type=='layout'){
				old_value_layout = name;
			}else{
				old_value_content = name;
			}
			var el2 = document.getElementById(type+'_'+name);
			if(el2) el2.style.display='block';
		}
		function switchDisplay(value,name,activevalue){
			var el = document.getElementById(name);
			if(el){
				if(value==activevalue){
					el.style.display='';
				}else{
					el.style.display='none';
				}
			}
		}
		";
		$document= JFactory::getDocument();
		$document->addScriptDeclaration($js);
		JHTML::_('behavior.modal');
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
		$childdisplayType = hikashop_get('type.childdisplay');
		$this->assignRef('childdisplayType',$childdisplayType);
		$pricetaxType = hikashop_get('type.pricetax');
		$this->assignRef('pricetaxType',$pricetaxType);
		$priceDisplayType = hikashop_get('type.pricedisplay');
		$this->assignRef('priceDisplayType',$priceDisplayType);
		$discountDisplayType = hikashop_get('type.discount_display');
		$this->assignRef('discountDisplayType',$discountDisplayType);
		$transition_effectType = hikashop_get('type.transition_effect');
		$this->assignRef('transition_effectType',$transition_effectType);
		$popup = hikashop_get('helper.popup');
		$this->assignRef('popup',$popup);
		$this->toolbar = array(
			'save',
			'apply',
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);
		if(!empty($this->toolbarJoomlaMenu)){
			array_unshift($this->toolbar,'|');
			array_unshift($this->toolbar,$this->toolbarJoomlaMenu);
		}
	}

	protected function getMenuData($cid) {
		if(!empty($cid)) {
			$menusClass = hikashop_get('class.menus');
			$element = $menusClass->get($cid);
			if(!empty($element->content_type) && !in_array($element->content_type, array('product','category'))) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('HIKA_MENU_TYPE_NOT_SUPPORTED'), 'error');
				if(!HIKASHOP_J16) {
					$url = JRoute::_('index.php?option=com_menus&task=edit&cid[]='.$cid, false);
				} else {
					$url = JRoute::_('index.php?option=com_menus&task=item.edit&id='.$cid, false);
				}
				$app->redirect($url);
			}
		}
		if(!isset($element->hikashop_params['layout_type']))
			$element->hikashop_params['layout_type'] = 'div';

		return $element;
	}

	protected function getModuleData($id) {
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

	function options(&$params){
		$this->id = $params->get('id');
		$this->name = str_replace('[]', '', $params->get('name'));
		$this->element = $params->get('value');
		$this->type = $params->get('type');
		$this->menu = $params->get('menu');
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
		$this->arr = array(
			JHTML::_('select.option',  '-1', JText::_( 'HIKA_INHERIT' ) ),
			JHTML::_('select.option',  '1', JText::_( 'HIKASHOP_YES' ) ),
			JHTML::_('select.option',  '0', JText::_( 'HIKASHOP_NO' ) ),
		);

		$this->mainProductCategory = 'product';
		$categoryClass = hikashop_get('class.category');
		$categoryClass->getMainElement($this->mainProductCategory);

		$cid = JRequest::getInt('id','');
		if(empty($cid))
			$cid = hikashop_getCID();
		if(empty($this->element)) {
			$menu = $this->getMenuData($cid);
			$this->element = $menu->hikashop_params;
			if(!isset($this->element['category']) && isset($this->element['selectparentlisting']))
				$this->element['category'] = $this->element['selectparentlisting'];

			if(isset($this->element['modules']) && $this->type != $this->menu){

				$db = JFactory::getDBO();
				$db->setQuery('SELECT template FROM '.hikashop_table('template_styles',false).' WHERE client_id = 0 AND home = 1');
				$template = $db->loadResult();
				if(file_exists(JPATH_ROOT .'/templates/'.$template.'/html/com_hikashop/category/listing.php')){
					$app = JFactory::getApplication();
					$app->enqueueMessage(JText::_('CATEGORY_LISTING_VIEW_OVERRIDE_WARNING'),'warning');
				}

				$moduleIds = explode(',',$this->element['modules']);
				$module = $this->getModuleData(reset($moduleIds));
				$this->element = $module->hikashop_params;
			}
		}
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
		$dispatcher->trigger('onHkContentParamsDisplay', array('menu', $this->name, &$element, &$extra_blocks));
		$this->assignRef('extra_blocks', $extra_blocks);
	}

	function form(){
		$cid = hikashop_getCID('id');
		if(empty($cid)){
			$element = new stdClass();
			$element->hikashop_params = $this->config->get('default_params');
			$task='add';
			$control = 'config[menu_0]';
			$element->hikashop_params['link_to_product_page'] = '1';
			$element->hikashop_params['border_visible']= true;

			$element->hikashop_params['layout_type'] = 'inherit';
			$element->hikashop_params['columns'] = '';
			$element->hikashop_params['limit'] = '';
			$element->hikashop_params['random'] = '-1';
			$element->hikashop_params['order_dir'] = 'inherit';
			$element->hikashop_params['filter_type'] = 2;
			$element->hikashop_params['product_order'] = 'inherit';
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
			$element->hikashop_params['number_of_products'] = '-1';
			$element->hikashop_params['only_if_products'] = '-1';
			$element->hikashop_params['div_item_layout_type'] = 'inherit';
			$element->hikashop_params['background_color'] = '';
			$element->hikashop_params['margin'] = '';
			$element->hikashop_params['border_visible'] = '-1';
			$element->hikashop_params['rounded_corners'] = '-1';
			$element->hikashop_params['text_center'] = '-1';
			$element->hikashop_params['ul_class_name'] = '';

		}else{
			$modulesClass = hikashop_get('class.menus');
			$element = $modulesClass->get($cid);
			$task='edit';
			$control = 'config[menu_'.$cid.']';
			if(strpos($element->link,'view=product')!==false){
				$element->hikashop_params['content_type'] = 'product';
			}elseif(empty($element->hikashop_params['content_type']) || !in_array($element->hikashop_params['content_type'],array('manufacturer','category'))){
				$element->hikashop_params['content_type'] = 'category';
			}
			$element->content_type = $element->hikashop_params['content_type'];

			if(!isset($element->hikashop_params['link_to_product_page'])){
				$element->hikashop_params['link_to_product_page'] = '1';
			}
		}
		if(!isset($element->hikashop_params['layout_type'])){
			$element->hikashop_params['layout_type'] = 'div';
		}
		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&cid[]='.$cid);
		$this->_loadCategory($element);
		if(!empty($cid)){
			if(!HIKASHOP_J16){
				$url = JRoute::_('index.php?option=com_menus&task=edit&cid[]='.$element->id);
			}else{
				$url = JRoute::_('index.php?option=com_menus&task=item.edit&id='.$element->id);
			}
			$this->toolbarJoomlaMenu = array('name'=>'link','icon'=>'upload','alt'=> JText::_('JOOMLA_MENU_OPTIONS'),'url'=>$url);
		}
		$js="
		function setVisibleLayoutEffect(value){
			if(value==\"slider_vertical\" || value==\"slider_horizontal\"){
				document.getElementById('product_effect').style.display = '';
				document.getElementById('product_effect_duration').style.display = '';
			}else if(value==\"fade\"){
				document.getElementById('product_effect').style.display = 'none';
				document.getElementById('product_effect_duration').style.display = '';
			}else if(value==\"img_pane\"){
				document.getElementById('product_effect').style.display = 'none';
				document.getElementById('product_effect_duration').style.display = 'none';
			}else{
				document.getElementById('product_effect').style.display = 'none';
				document.getElementById('product_effect_duration').style.display = 'none';
			}
		}";
		$doc = JFactory::getDocument();
	 	$doc->addScriptDeclaration($js);
		$this->assignRef('element',$element);
		$this->assignRef('control',$control);
		$this->_assignTypes();

		$extra_blocks = array(
			'products' => array(),
			'layouts' => array()
		);
		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onHkContentParamsDisplay', array('menu', $control, &$element, &$extra_blocks));
		$this->assignRef('extra_blocks', $extra_blocks);
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
		$database	= JFactory::getDBO();
		if(version_compare(JVERSION,'1.6','<')){
			$query = 'SELECT id FROM '.hikashop_table('components',false).' WHERE link=\'option='.HIKASHOP_COMPONENT.'\' LIMIT 1';
			$database->setQuery($query);
			$filters = array('(componentid='.$database->loadResult().' OR (componentid=0 AND link LIKE \'%option='.HIKASHOP_COMPONENT.'%\'))','type=\'component\'');
			$searchMap = array('alias','link','name');
		}else{
			$query = 'SELECT extension_id FROM '.hikashop_table('extensions',false).' WHERE type=\'component\' AND element=\''.HIKASHOP_COMPONENT.'\' LIMIT 1';
			$database->setQuery($query);
			$filters = array('(component_id='.$database->loadResult().' OR (component_id=0 AND link LIKE \'%option='.HIKASHOP_COMPONENT.'%\'))','type=\'component\'','client_id=0');
			$searchMap = array('alias','link','title');
		}
		$filters[] = 'published>-2';
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
		$query = ' FROM '.hikashop_table('menu',false).' '.$filters.$order;
		$database->setQuery('SELECT *'.$query);
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
		$unset=array();
		foreach($rows as $k => $row){
			if(strpos($row->link,'view=product')!==false  && strpos($row->link,'layout=show')===false){
				$rows[$k]->hikashop_params = $this->config->get('menu_'.$row->id);
				$rows[$k]->hikashop_params['content_type'] = 'product';
			}elseif(strpos($row->link,'view=category')!==false || strpos($row->link,'view=')===false){
				$rows[$k]->hikashop_params = $this->config->get('menu_'.$row->id);
				$rows[$k]->hikashop_params['content_type'] = 'category';
			}else{
				$unset[]=$k;
				continue;
			}
			if(empty($rows[$k]->hikashop_params)){
				$rows[$k]->hikashop_params = $this->config->get('default_params');
			}

			$rows[$k]->content_type = $rows[$k]->hikashop_params['content_type'];
		}
		foreach($unset as $u){
			unset($rows[$u]);
		}
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$manage = hikashop_isAllowed($this->config->get('acl_menus_manage','all'));
		$this->assignRef('manage',$manage);
		$this->toolbar = array(
			array('name'=>'editList','display'=>$manage),
			array('name'=>'deleteList','display'=>hikashop_isAllowed($this->config->get('acl_menus_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);
	}
}
