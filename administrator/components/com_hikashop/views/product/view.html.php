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

class ProductViewProduct extends hikashopView
{
	var $type = 'main';
	var $ctrl= 'product';
	var $nameListing = 'PRODUCTS';
	var $nameForm = 'PRODUCTS';
	var $icon = 'product';
	var $displayCompleted = false;
	var $triggerView = true;

	function display($tpl = null, $params = array()) {
		$this->params =& $params;
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$fct = $this->getLayout();
		if(method_exists($this, $fct)) {
			if($this->$fct() === false)
				return;
		}
		if(empty($this->displayCompleted))
			parent::display($tpl);
	}

	function listing(){
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$config =& hikashop_config();

		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.ordering','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'asc',	'word' );
		$newFilterId = JRequest::getVar('filter_id');
		$newSearch = JRequest::getVar('search');
		if((!empty($newSearch)&&$newSearch!=$app->getUserState($this->paramBase.".search"))||(!empty($newFilterId) && $newFilterId!=$app->getUserState($this->paramBase.".filter_id"))){
			$app->setUserState( $this->paramBase.'.limitstart',0);
			$pageInfo->limit->start = 0;
		}else{
			$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		}
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));

		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 500;
		$selectedType = $app->getUserStateFromRequest( $this->paramBase.".filter_type",'filter_type',$config->get('sub_products_display_all',1),'int');
		$pageInfo->selectedType = $selectedType;
		$pageInfo->filter->filter_id = $app->getUserStateFromRequest( $this->paramBase.".filter_id",'filter_id',0,'string');
		$pageInfo->filter->filter_product_type = $app->getUserStateFromRequest( $this->paramBase.".filter_product_type",'filter_product_type','main','word');
		$pageInfo->filter->filter_published = $app->getUserStateFromRequest( $this->paramBase.".filter_published",'filter_published',0,'int');
		$pageInfo->filter->filter_manufacturer = $app->getUserStateFromRequest( $this->paramBase.".filter_manufacturer",'filter_manufacturer','','string');
		$database	= JFactory::getDBO();
		$filters = array();

		if($pageInfo->filter->filter_published==2){
			$filters[]='b.product_published=1';
		}elseif($pageInfo->filter->filter_published==1){
			$filters[]='b.product_published=0';
		}

		if(empty($pageInfo->filter->filter_id)|| !is_numeric($pageInfo->filter->filter_id)){
			$pageInfo->filter->filter_id='product';
			$class = hikashop_get('class.category');
			$class->getMainElement($pageInfo->filter->filter_id);
		}

		$manufacturerDisplay = hikashop_get('type.manufacturer');
		$manufacturer = $manufacturerDisplay->display('filter_manufacturer',$pageInfo->filter->filter_manufacturer);
		$this->assignRef('manufacturerDisplay',$manufacturer);

		if (!empty($manufacturer))
		{
			if(!empty($pageInfo->filter->filter_manufacturer))
			{
				if($pageInfo->filter->filter_manufacturer=='none')
					$filters[]='b.product_manufacturer_id = 0';
				else if ($pageInfo->filter->filter_manufacturer!='')
					$filters[]='b.product_manufacturer_id='.(int)$pageInfo->filter->filter_manufacturer;
			}
		}

		$order = '';
		$categoryClass = hikashop_get('class.category');
		if(!$selectedType){
			$filters[]='a.category_id='.(int)$pageInfo->filter->filter_id;
			$select='SELECT a.ordering, b.*';
			$cat_ids = array((int)$pageInfo->filter->filter_id);
		}else{
			$categoryClass->parentObject =& $this;
			$children = $categoryClass->getChildren((int)$pageInfo->filter->filter_id,true,array(),'',0,0);
			$filter = 'a.category_id IN (';
			$cat_ids = array();
			foreach($children as $child){
				$filter .= $child->category_id.',';
				$cat_ids[$child->category_id]=$child->category_id;
			}
			$filters[]=$filter.(int)$pageInfo->filter->filter_id.')';
			$select='SELECT DISTINCT b.*';
		}

		$searchMap = array('b.product_name','b.product_description','b.product_id','b.product_code');

		$fieldsClass = hikashop_get('class.field');
		$parent_cat_ids = array();
		if(!empty($cat_ids)){
			$parents = $categoryClass->getParents($cat_ids,true,array(),'',0,0);
			if(!empty($parents)){
				foreach($parents as $parent){
					$parent_cat_ids[]=$parent->category_id;
				}
			}
		}
		$categories=array('originals'=>$cat_ids,'parents'=>$parent_cat_ids);

		$fields = $fieldsClass->getData('backend_listing','product',false,$categories);
		$this->assignRef('fields',$fields);

		$this->assignRef('fieldsClass',$fieldsClass);


		if(!empty($fields)){
			foreach($fields as $field){
				$searchMap[]='b.'.$field->field_namekey;
			}
		}


		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = '('.implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal".')';
		}


		if($pageInfo->filter->filter_product_type=='all'){
			if(!empty($pageInfo->filter->order->value)){
				$select.=','.$pageInfo->filter->order->value.' as sorting_column';
				$order = ' ORDER BY sorting_column '.$pageInfo->filter->order->dir;
			}
		}else{
			if(!empty($pageInfo->filter->order->value)){
				$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
			}
		}

		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger( 'onBeforeProductListingLoad', array( & $filters, & $order, &$this, & $select, & $select2, & $a, & $b, & $on) );

		if($pageInfo->filter->filter_product_type=='all'){
			$query = '( '.$select.' FROM '.hikashop_table('product_category').' AS a LEFT JOIN '.hikashop_table('product').' AS b ON a.product_id=b.product_id WHERE '.implode(' AND ',$filters).' AND b.product_id IS NOT NULL )
			UNION
						( '.$select.' FROM '.hikashop_table('product_category').' AS a LEFT JOIN '.hikashop_table('product').' AS b ON a.product_id=b.product_parent_id WHERE '.implode(' AND ',$filters).' AND b.product_parent_id IS NOT NULL ) ';
			$database->setQuery($query.$order,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		}else{
			$filters[]='b.product_type = '.$database->Quote($pageInfo->filter->filter_product_type);
			if($pageInfo->filter->filter_product_type!='variant'){
				$lf = 'a.product_id=b.product_id';
			}else{
				$lf = 'a.product_id=b.product_parent_id';
			}
			$query = ' FROM '.hikashop_table('product_category').' AS a LEFT JOIN '.hikashop_table('product').' AS b ON '.$lf.' WHERE '.implode(' AND ',$filters);

			$database->setQuery($select.$query.$order,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		}

		$rows = $database->loadObjectList();
		$fieldsClass->handleZoneListing($fields,$rows);
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'product_id');
		}
		if($pageInfo->filter->filter_product_type=='all'){
			$database->setQuery('SELECT COUNT(*) FROM ('.$query.') as u');
		}else{
			$database->setQuery('SELECT COUNT(DISTINCT(b.product_id))'.$query);
		}
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		if($pageInfo->elements->page){
			$this->_loadPrices($rows);
		}
		if($pageInfo->limit->value == 500) $pageInfo->limit->value = 100;

		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);

		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_product_manage','all'));
		$this->assignRef('manage',$manage);
		$this->toolbar = array(
			array('name' => 'export'),
			array('name' => 'publishList', 'display' => $manage),
			array('name' => 'unpublishList', 'display' => $manage),
			array('name' => 'custom', 'icon' => 'copy', 'alt' => JText::_('HIKA_COPY'), 'task' => 'copy', 'display' => $manage),
			array('name' => 'addNew', 'display' => $manage),
			array('name' => 'editList', 'display' => $manage),
			array('name' => 'deleteList', 'display' => hikashop_isAllowed($config->get('acl_product_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);

		if(!empty($rows)){
			$ids = array();
			foreach($rows as $key => $row){
				$ids[]=$row->product_id;
			}
			$queryImage = 'SELECT * FROM '.hikashop_table('file').' WHERE file_ref_id IN ('.implode(',',$ids).') AND file_type=\'product\' ORDER BY file_ref_id ASC, file_ordering ASC, file_id ASC';
			$database->setQuery($queryImage);
			$images = $database->loadObjectList();

			foreach($rows as $k=>$row){
				if(!empty($images)){
					foreach($images as $image){
						if($row->product_id==$image->file_ref_id){
							if(!isset($row->file_ref_id)){
								foreach(get_object_vars($image) as $key => $name){
									$rows[$k]->$key = $name;
								}
							}
							break;
						}
					}
				}
				if(!isset($rows[$k]->file_name)){
					$rows[$k]->file_name = $row->product_name;
				}
			}
		}

		$image=hikashop_get('helper.image');
		$this->assignRef('image',$image);
		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$childClass = hikashop_get('type.childdisplay');
		$this->assignRef('childDisplayType',$childClass);
		$filter_type=$childClass->display('filter_type',$selectedType,false);
		$this->assignRef('childDisplay',$filter_type);
		$publishDisplay = hikashop_get('type.published');
		$publish = $publishDisplay->display('filter_published',$pageInfo->filter->filter_published);
		$this->assignRef('publishDisplay',$publish);
		$productClass = hikashop_get('type.product');
		$this->assignRef('productType',$productClass);
		$breadcrumbClass = hikashop_get('type.breadcrumb');
		$breadcrumb = $breadcrumbClass->display('filter_id',$pageInfo->filter->filter_id,'product');
		$this->assignRef('breadCrumb',$breadcrumb);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$doOrdering = !$selectedType;
		if($doOrdering && !(empty($pageInfo->filter->filter_product_type) || $pageInfo->filter->filter_product_type=='main')){
			$doOrdering=false;
		}
		$this->assignRef('doOrdering',$doOrdering);
		if($doOrdering){
			$order = new stdClass();
			$order->ordering = false;
			$order->orderUp = 'orderup';
			$order->orderDown = 'orderdown';
			$order->reverse = false;
			if($pageInfo->filter->order->value == 'a.ordering'){
				$order->ordering = true;
				if($pageInfo->filter->order->dir == 'desc'){
					$order->orderUp = 'orderdown';
					$order->orderDown = 'orderup';
					$order->reverse = true;
				}
			}
			$this->assignRef('order',$order);
		}
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
		$config =& hikashop_config();
		$this->assignRef('config',$config);
		$this->getPagination();
	}

	function form_legacy() {
		$product_id = hikashop_getCID('product_id');
		$class = hikashop_get('class.product');
		$popup = hikashop_get('helper.popup');
		$this->assignRef('popup',$popup);
		$database	= JFactory::getDBO();
		$config =& hikashop_config();
		if(!empty($product_id)){
			$element = $class->get($product_id,true);
			$task='edit';
			if($element){
				$query = 'SELECT b.* FROM '.hikashop_table('product_category').' AS a LEFT JOIN '.hikashop_table('category').' AS b ON a.category_id=b.category_id WHERE a.product_id = '.$product_id.' ORDER BY a.product_category_id';
				$database->setQuery($query);
				$element->categories = $database->loadObjectList();
				$query = 'SELECT a.*,b.* FROM '.hikashop_table('product_related').' AS a LEFT JOIN '.hikashop_table('product').' AS b ON a.product_related_id=b.product_id WHERE a.product_related_type=\'related\' AND a.product_id = '.$product_id;
				$database->setQuery($query);
				$element->related = $database->loadObjectList();
				$query = 'SELECT a.*,b.* FROM '.hikashop_table('product_related').' AS a LEFT JOIN '.hikashop_table('product').' AS b ON a.product_related_id=b.product_id WHERE a.product_related_type=\'options\' AND a.product_id = '.$product_id;
				$database->setQuery($query);
				$element->options = $database->loadObjectList();
				$query = 'SELECT * FROM '.hikashop_table('file').' WHERE file_ref_id = '.$product_id.' AND file_type=\'product\' ORDER BY file_ordering, file_id';
				$database->setQuery($query);
				$element->images = $database->loadObjectList();
				$query = 'SELECT * FROM '.hikashop_table('file').' WHERE file_ref_id = '.$product_id.' AND file_type=\'file\' ORDER BY file_ordering, file_id';
				$database->setQuery($query);
				$element->files = $database->loadObjectList('file_id');
				if(!empty($element->files)){
					$query = 'SELECT SUM(download_number) AS download_number,file_id FROM '.hikashop_table('download').' WHERE file_id IN ( '.implode(',',array_keys($element->files)).' ) GROUP BY file_id';
					$database->setQuery($query);
					$downloads = $database->loadObjectList('file_id');
					if(!empty($downloads)){
						foreach($downloads as $download){
							$element->files[$download->file_id]->download_number = $download->download_number;
						}
					}
				}
				if($element->product_type=='variant'){
					$query = 'SELECT b.* FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic').' AS b ON a.variant_characteristic_id=b.characteristic_id WHERE variant_product_id = '.$product_id;
					$database->setQuery($query);
					$characteristics = $database->loadObjectList('characteristic_parent_id');
				}else{
					$element->characteristics = $this->_getCharacteristics($product_id);
				}

				$ids = array($product_id);
			}
		}else{
			$this->product_type = $config->get('default_product_type','virtual');
			$ids = array();
			$element = JRequest::getVar('fail');
			if(empty($element)){
				$element = new stdClass();
				$element->product_published=1;
				if(JRequest::getBool('variant')){
					$element->product_type = 'variant';
					$element->product_parent_id = JRequest::getInt('parent_id');
				}else{
					$element->product_type = 'main';
				}
				$element->product_quantity=-1;
				$categoryClass = hikashop_get('class.category');
				$mainTaxCategory = 'tax';
				$categoryClass->getMainElement($mainTaxCategory);
				$database->setQuery('SELECT category_id FROM '. hikashop_table('category'). ' WHERE category_type=\'tax\' && category_parent_id='.(int)$mainTaxCategory.' ORDER BY category_ordering DESC');
				$element->product_tax_id = $database->loadResult();

				if($element->product_type == 'main') {
					$app = JFactory::getApplication();
					$id = $app->getUserState(HIKASHOP_COMPONENT.'.product.filter_id');
					if(empty($id) || !is_numeric($id)){
						$id='product';
						$class = hikashop_get('class.category');
						$class->getMainElement($id);
					}
					if(!empty($id)){
						$element->categories = array($categoryClass->get($id));
					}
				}
			}else{
				if(!empty($element->related)){
					$rel_ids = array();
					foreach($element->related as $related){
						$rel_ids[(int)$related->product_related_id]=$related->product_related_ordering;
					}
					$query = 'SELECT b.* FROM '.hikashop_table('product').' AS b WHERE b.product_id IN ('.implode(',',array_keys($rel_ids)).')';
					$database->setQuery($query);
					$element->related = $database->loadObjectList();
					foreach($element->related as $k => $option){
						$element->related[$k]->product_related_id = $option->product_id;
						$element->related[$k]->product_related_ordering = $rel_ids[$option->product_id];
					}
				}
				if(!empty($element->options)){
					$rel_ids = array();
					foreach($element->options as $related){
						$rel_ids[(int)$related->product_related_id]=$related->product_related_ordering;
					}
					$query = 'SELECT b.* FROM '.hikashop_table('product').' AS b WHERE b.product_id IN ('.implode(',',array_keys($rel_ids)).')';
					$database->setQuery($query);
					$element->options = $database->loadObjectList();
					foreach($element->options as $k => $option){
						$element->options[$k]->product_related_id = $option->product_id;
						$element->options[$k]->product_related_ordering = $rel_ids[$option->product_id];
					}
				}
				if(!empty($element->characteristics)){
					$char_ids = array();
					foreach($element->characteristics as $k => $char){
						$char_ids[(int)$char->characteristic_id] = $k;
					}
					$query = 'SELECT b.* FROM '.hikashop_table('characteristic').' AS b WHERE b.characteristic_id IN ('.implode(',',array_keys($char_ids)).')';
					$database->setQuery($query);
					$characteristics = $database->loadObjectList();
					foreach($characteristics as $char){
						$element->characteristics[$char_ids[$char->characteristic_id]]->characteristic_value = $char->characteristic_value;
					}
				}
			}
			$task='add';
		}
		if(!empty($element->related)){
			foreach($element->related as $related){
				$ids[]=(int)@$related->product_id;
			}
		}
		if(!empty($element->options)){
			foreach($element->options as $optionElement){
				$ids[]=(int)@$optionElement->product_id;
			}
		}
		if(!empty($ids)){
			$query = 'SELECT * FROM '.hikashop_table('price').' WHERE price_product_id IN ('.implode(',',$ids).')';
			$database->setQuery($query);
			$prices = $database->loadObjectList();
			if(!empty($prices)){
				foreach($prices as $price){
					if($price->price_product_id==$product_id){
						$element->prices[]=$price;
					}
					if(!empty($element->related)){
						foreach($element->related as $k => $related){
							if($price->price_product_id==$related->product_id){
								$element->related[$k]->prices[]=$price;
								break;
							}
						}
					}
					if(!empty($element->options)){
						foreach($element->options as $k => $optionElement){
							if($price->price_product_id==$optionElement->product_id){
								$element->options[$k]->prices[]=$price;
								break;
							}
						}
					}
				}
			}
		}
		$main_currency = $config->get('main_currency',1);
		$currency = hikashop_get('type.currency');
		$this->assignRef('currency',$currency);
		$this->assignRef('config',$config);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
		if($element->product_type=='variant'){
			$element->characteristics = $this->_getCharacteristics(@$element->product_parent_id);
			foreach($element->characteristics as $key => $characteristic){
				if(isset($characteristics[$characteristic->characteristic_id])){
					$element->characteristics[$key]->default_id=$characteristics[$characteristic->characteristic_id]->characteristic_id;
				}
			}
			$parentdata = $class->get($element->product_parent_id);
			$element->product_tax_id=$parentdata->product_tax_id;
		}
		if(!empty($element->product_tax_id)){
			$main_tax_zone = explode(',',$config->get('main_tax_zone',''));
			if(count($main_tax_zone)){
				$main_tax_zone = array_shift($main_tax_zone);
			}
		}
		if(!empty($element->prices)){
			$unset = array();
			foreach($element->prices as $key => $price){
				if(empty($price->price_value)){
					$unset[]=$key;
				}
			}
			foreach($unset as $u){
				unset($element->prices[$u]);
			}
			if(!empty($element->product_tax_id) && !$config->get('floating_tax_prices',0)){
				foreach($element->prices as $key => $price){
					$element->prices[$key]->price_value_with_tax = $currencyClass->getTaxedPrice($price->price_value,$main_tax_zone,$element->product_tax_id);
				}
			}else{
				foreach($element->prices as $key => $price){
					$element->prices[$key]->price_value_with_tax = $price->price_value;
				}
			}
		}
		if(empty($element->prices)){
			$obj = new stdClass();
			$obj->price_value=0;
			$obj->price_value_with_tax=0;
			$obj->price_currency_id = $main_currency;
			$element->prices = array($obj);
		}

		if($element->product_quantity==-1){
			$element->product_quantity=JText::_('UNLIMITED');
		}

		if(empty($element->product_max_per_order)){
			$element->product_max_per_order=JText::_('UNLIMITED');
		}

		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&product_id='.$product_id);

		if(version_compare(JVERSION,'1.6','<')){
			$url = hikashop_completeLink('product&task=updatecart&cid='.$product_id,true);
		}else{
			$url = 'index.php?option=com_hikashop&ctrl=product&task=updatecart&tmpl=component&cid='.$product_id;
		}
		$this->toolbar = array(
			array('name' => 'popup','icon'=>'upload','alt'=>JText::_('ADD_TO_CART_HTML_CODE'),'url'=>$url),
			'|',
			'save',
			array('name' => 'save2new', 'display' => version_compare(JVERSION,'1.7','>=')),
			'apply'
		);
		if(JRequest::getInt('variant')){
			$variant = 1;
			$this->assignRef('variant',$variant);
			$this->toolbar[] = array('name' => 'link', 'icon'=>'cancel','alt'=>JText::_('HIKA_CANCEL'),'url'=>hikashop_completeLink('product&task=variant&cid='.$element->product_parent_id));
		}else{
			$cancel_url = JRequest::getVar('cancel_redirect');
			if(!empty($cancel_url)){
				$url = base64_decode($cancel_url);
				$this->toolbar[] = array('name' => 'link', 'icon'=>'cancel','alt'=>JText::_('HIKA_BACK'),'url'=>$url);
			}else{
				$this->toolbar[] = 'cancel';
			}
		}

		if($element->product_type=='variant'){
			$this->toolbar[] = array('name' => 'link', 'icon'=>'forward','alt'=>JText::_('GO_TO_MAIN_PRODUCT'),'url'=>hikashop_completeLink('product&task=edit&legacy=1&cid='.$element->product_parent_id));
		}
		$this->toolbar[] = '|';
		$this->toolbar[] = array('name' => 'pophelp', 'target' => $this->ctrl.'-form');

		$this->assignRef('element',$element);

		JHTML::_('behavior.modal');
		$type = 'tabs';
		if($config->get('multilang_display','tabs')!='popups'){
			$type = $config->get('multilang_display','tabs');
		}

		$tabs = hikashop_get('helper.tabs');
		$this->assignRef('tabs', $tabs);

		$translation = false;
		$transHelper = hikashop_get('helper.translation');
		if($transHelper && $transHelper->isMulti()){
			$translation = true;
			$transHelper->load('hikashop_product',@$element->product_id,$element);
			$this->assignRef('transHelper',$transHelper);
		}
		$toggle=hikashop_get('helper.toggle');
		$this->assignRef('toggle',$toggle);

		$updatePriceJS = '';
		if(!$config->get('floating_tax_prices',0)){
			$updatePriceJS = 'try{
				new Ajax(\'index.php?option=com_hikashop&tmpl=component&ctrl='.$this->ctrl.'&task=getprice&price=\'+price+\'&tax_id=\'+tax_id+\'&conversion=\'+conversion, { method: \'get\', onComplete: function(result) {window.document.getElementById(divId).value = result;}}).request();
			}catch(err){
				new Request({url:\'index.php?option=com_hikashop&tmpl=component&ctrl='.$this->ctrl.'&task=getprice&price=\'+price+\'&tax_id=\'+tax_id+\'&conversion=\'+conversion,method: \'get\', onComplete: function(result) {window.document.getElementById(divId).value = result;}}).send();
			}';
		}

		$js = '
		function deleteRow(divName,inputName,rowName,div1,input1,div2,input2){
			var d = document.getElementById(divName);
			var olddiv = document.getElementById(inputName);
			if(d && olddiv){
				d.removeChild(olddiv);
				document.getElementById(rowName).style.display=\'none\';
			}
			if(div1 && input1){
				deleteRow(div1,input1,rowName);
			}
			if(div2 && input2){
				deleteRow(div2,input2,rowName);
			}
			return false;
		}
		function updatePrice(divId,price,tax_id,conversion){
			'.$updatePriceJS.'
		}';
		if (!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		}else{
			$doc = JFactory::getDocument();
		}
		$doc->addScriptDeclaration( $js );

		$this->assignRef('translation',$translation);

		$editor = hikashop_get('helper.editor');
		$editor->name = 'product_description';
		$editor->content = @$element->product_description;
		$editor->height=300;
		$this->assignRef('editor',$editor);

		$categoryType = hikashop_get('type.categorysub');
		$categoryType->type='tax';
		$categoryType->field='category_id';
		$this->assignRef('categoryType',$categoryType);

		$manufacturerType = hikashop_get('type.categorysub');
		$manufacturerType->type='manufacturer';
		$manufacturerType->field='category_id';
		$this->assignRef('manufacturerType',$manufacturerType);

		$quantity = hikashop_get('type.quantity');
		$this->assignRef('quantity',$quantity);

		$warehouseType = hikashop_get('type.warehouse');
		$this->assignRef('warehouseType',$warehouseType);
		$weightType = hikashop_get('type.weight');
		$this->assignRef('weight',$weightType);
		$volumeType = hikashop_get('type.volume');
		$this->assignRef('volume',$volumeType);
		$image=hikashop_get('helper.image');
		$this->assignRef('image',$image);
		$characteristicHelper = hikashop_get('type.characteristic');
		$this->assignRef('characteristicHelper',$characteristicHelper);
		$productDisplayType = hikashop_get('type.productdisplay');
		$this->assignRef('productDisplayType',$productDisplayType);
		$quantityDisplayType = hikashop_get('type.quantitydisplay');
		$this->assignRef('quantityDisplayType',$quantityDisplayType);
		$this->_addCustom($element);
	}

	function edit_translation_legacy(){
		$language_id = JRequest::getInt('language_id',0);
		$product_id = hikashop_getCID('product_id');
		$class = hikashop_get('class.product');
		$element = $class->get($product_id);
		$translation = false;
		$transHelper = hikashop_get('helper.translation');
		if($transHelper && $transHelper->isMulti()){
			$translation = true;
			$transHelper->load('hikashop_product',@$element->product_id,$element,$language_id);
			$this->assignRef('transHelper',$transHelper);
		}
		$editor = hikashop_get('helper.editor');
		$editor->name = 'product_description';
		$editor->content = @$element->product_description;
		$editor->height=300;
		$this->assignRef('editor',$editor);
		$toggle=hikashop_get('helper.toggle');
		$this->assignRef('toggle',$toggle);
		$this->assignRef('element',$element);
		$tabs = hikashop_get('helper.tabs');
		$this->assignRef('tabs',$tabs);
	}

	function _addCustom(&$element){
		$fieldsClass = hikashop_get('class.field');
		$fields = $fieldsClass->getFields('',$element,'product','field&task=state');
		$null=array();
		$fieldsClass->addJS($null,$null,$null);
		$fieldsClass->jsToggle($fields,$element,0);
		$this->assignRef('fieldsClass',$fieldsClass);
		$this->assignRef('fields',$fields);
	}

	function _getCharacteristics($product_id){
		$database = JFactory::getDBO();
		$query = 'SELECT a.ordering,b.* FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic').' AS b ON a.variant_characteristic_id=b.characteristic_id WHERE a.variant_product_id = '.(int)$product_id.' ORDER BY a.ordering';
		$database->setQuery($query);
		$characteristics = $database->loadObjectList();
		if(!empty($characteristics)){
			$unsetList = array();
			$ids = array();
			foreach($characteristics as $key => $characteristic){
				if(!empty($characteristic->characteristic_parent_id)){
					$unsetList[]=$key;
					foreach($characteristics as $key2 => $characteristic2){
						if($characteristic->characteristic_parent_id==$characteristic2->characteristic_id){
							$characteristics[$key2]->default_id=$characteristic->characteristic_id;
							break;
						}
					}
				}else{
					$ids[] = (int)$characteristic->characteristic_id;
				}
			}
			if(!empty($unsetList)){
				foreach($unsetList as $item){
					unset($characteristics[$item]);
				}
				$characteristics=array_values($characteristics);
			}
			if(!empty($ids)){
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
				$query = 'SELECT * FROM '.hikashop_table('characteristic').' WHERE characteristic_parent_id IN ('.implode(',',$ids).') ORDER BY '.$order;
				$database->setQuery($query);
				$values = $database->loadObjectList();
				if(!empty($values)){
					foreach($values as $value){
						foreach($characteristics as $key => $characteristic){
							if($value->characteristic_parent_id==$characteristic->characteristic_id){
								if(!isset($characteristics[$key]->values)){
									$characteristics[$key]->values=array();
								}
								$characteristics[$key]->values[$value->characteristic_id]=$value->characteristic_value;
								break;
							}
						}
					}
				}
			}
		}
		return $characteristics;
	}

	function _loadPrices(&$rows){
		$currencyClass = hikashop_get('class.currency');
		$zone_id = hikashop_getZone();
		$ids = array();
		foreach($rows as $row){
			$ids[]=(int)$row->product_id;
		}
		$query = 'SELECT * FROM '.hikashop_table('price').' WHERE price_product_id IN ('.implode(',',$ids).')';
		$database = JFactory::getDBO();
		$database->setQuery($query);
		$prices = $database->loadObjectList();
		if(!empty($prices)){
			foreach($rows as $k => $row){
				foreach($prices as $price){
					if($price->price_product_id==$row->product_id){
						if(!isset($row->prices)) $row->prices=array();
						$rows[$k]->prices[$price->price_min_quantity]=$price;
						$rows[$k]->prices[$price->price_min_quantity]->price_value_with_tax = $currencyClass->getTaxedPrice($price->price_value,$zone_id,$row->product_tax_id);
					}
				}
			}
		}
	}

	function variant_legacy(){
		$app = JFactory::getApplication();
		$database	= JFactory::getDBO();
		$filters = array();
		$product_id = JRequest::getInt('parent_id');
		if(empty($product_id)){
			$product_id = hikashop_getCID('product_id');
		}

		$characteristics = false;
		$filters[]='a.variant_product_id = '.$product_id;
		$query = 'SELECT a.* FROM '.hikashop_table('variant').' AS a WHERE '.implode(' AND ',$filters).' ORDER BY a.variant_product_id ASC';
		$database->setQuery($query);
		$variants = $database->loadObjectList();

		if(count($variants)){
			$filters = array();
			$filters[]='a.product_parent_id = '.$product_id;
			$query = 'SELECT a.* FROM '.hikashop_table('product').' AS a WHERE '.implode(' AND ',$filters);
			$database->setQuery($query);
			$rows = $database->loadObjectList();
			$characteristics = $this->_getCharacteristics($product_id);
			if(count($rows)){

				$this->_loadPrices($rows);

				$ids = array();
				foreach($rows as $row){
					$ids[]=$row->product_id;
				}
				$query = 'SELECT a.variant_product_id,b.* FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic').' AS b ON a.variant_characteristic_id=b.characteristic_id WHERE variant_product_id IN ('.implode(',',$ids).')';
				$database->setQuery($query);
				$variants = $database->loadObjectList();
				if(!empty($variants)){
					foreach($variants as $variant){
						foreach($rows as $k => $row){
							if($variant->variant_product_id==$row->product_id){
								$name = false;
								foreach($characteristics as $characteristic){
									if($characteristic->characteristic_id==$variant->characteristic_parent_id){
										$name = $characteristic->characteristic_value;
										break;
									}
								}
								if($name!==false){
									$rows[$k]->characteristics[$name]=$variant->characteristic_value;
								}
								break;
							}
						}
					}
				}
			}
			$config =& hikashop_config();
			$this->toolbar = array(
				array('name'=>'publishList'),
				array('name'=>'unpublishList'),
				'|',
				array('name' => 'link', 'icon'=>'new','alt'=>JText::_('HIKA_NEW'),'url'=>hikashop_completeLink('product&task=edit&variant=1&parent_id='.$product_id)),
				array('name'=>'editList'),
				array('name'=>'deleteList','display'=>hikashop_isAllowed($config->get('acl_product_delete','all'))),
			);
			$this->assignRef('rows',$rows);
			$this->assignRef('characteristics',$characteristics);
		}else{
			$app->enqueueMessage(JText::_('CHARACTERISTICS_FIRST'));
		}

		hikashop_setTitle(JText::_('VARIANTS'),$this->icon,'product&task=variant&cid='.$product_id);

		$this->toolbar[]=array('name' => 'link', 'icon'=>'cancel','alt'=>JText::_('GO_TO_MAIN_PRODUCT'),'url'=>hikashop_completeLink('product&task=edit&cid='.$product_id));
		$this->toolbar[]='|';
		$this->toolbar[]=array('name' => 'pophelp', 'target' => $this->ctrl.'variant-listing');
		$this->toolbar[]='dashboard';

		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
		$this->assignRef('product_id', $product_id);
		$toggle=hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggle);

		$fieldsClass = hikashop_get('class.field');
		$query = 'SELECT category_id FROM '.hikashop_table('product_category').' WHERE product_id ='.(int)$product_id;
		$database->setQuery($query);
		if(!HIKASHOP_J25){
			$cat_ids = $database->loadResultArray();
		} else {
			$cat_ids = $database->loadColumn();
		}
		$parent_cat_ids = array();
		if(!empty($cat_ids)){
			$categoryClass = hikashop_get('class.category');
			$parents = $categoryClass->getParents($cat_ids,true,array(),'',0,0);
			if(!empty($parents)){
				foreach($parents as $parent){
					$parent_cat_ids[]=$parent->category_id;
				}
			}
		}
		$categories=array('originals'=>$cat_ids,'parents'=>$parent_cat_ids);
		$fields = $fieldsClass->getData('backend_listing','product',false,$categories);
		$this->assignRef('fields',$fields);
		$this->assignRef('fieldsClass',$fieldsClass);
	}

	function selectcategory(){
		$this->paramBase .= '_category';
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.category_ordering','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'asc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$selectedType = $app->getUserStateFromRequest( $this->paramBase.".filter_type",'filter_type',0,'int');
		$pageInfo->filter->filter_id = $app->getUserStateFromRequest( $this->paramBase.".filter_id",'filter_id','product','string');
		$database	= JFactory::getDBO();
		$searchMap = array('a.category_name','a.category_description','a.category_id');
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$class = hikashop_get('class.category');
		$class->parentObject =& $this;
		$rows = $class->getChildren($pageInfo->filter->filter_id,$selectedType,$filters,$order,$pageInfo->limit->start,$pageInfo->limit->value,false);
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'category_id');
		}
		$database->setQuery('SELECT COUNT(*)'.$class->query);
		$pageInfo->elements=new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);

		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);

		$this->toolbar = array(
			'addNew',
			'editList',
			'deleteList',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);
		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$childClass = hikashop_get('type.childdisplay');
		$childDisplay = $childClass->display('filter_type',$selectedType,false);
		$this->assignRef('childDisplay',$childDisplay);
		$breadcrumbClass = hikashop_get('type.breadcrumb');
		$breadcrumb = $breadcrumbClass->display('filter_id',$pageInfo->filter->filter_id,'product');
		$this->assignRef('breadCrumb',$breadcrumb);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$order = new stdClass();
		$order->ordering = false;
		$order->orderUp = 'orderup';
		$order->orderDown = 'orderdown';
		$order->reverse = false;
		if($pageInfo->filter->order->value == 'a.category_ordering'){
			$order->ordering = true;
			if($pageInfo->filter->order->dir == 'desc'){
				$order->orderUp = 'orderdown';
				$order->orderDown = 'orderup';
				$order->reverse = true;
			}
		}
		$this->assignRef('order',$order);
		$config =& hikashop_config();
		$this->assignRef('config',$config);
		$this->getPagination();
	}

	function addcategory(){
		$categories = JRequest::getVar( 'cid', array(), '', 'array' );
		$rows = array();
		if(!empty($categories)){
			JArrayHelper::toInteger($categories);
			$database	= JFactory::getDBO();
			$query = 'SELECT * FROM '.hikashop_table('category').' WHERE category_id IN ('.implode(',',$categories).')';
			$database->setQuery($query);
			$rows = $database->loadObjectList();
		}
		$this->assignRef('rows',$rows);
		if (!HIKASHOP_PHP5) {
			$document=& JFactory::getDocument();
		}else{
			$document= JFactory::getDocument();
		}
		$js = "window.hikashop.ready( function() {
				var dstTable = window.parent.document.getElementById('category_listing');
				var srcTable = document.getElementById('result');
				for (var c = 0,m=srcTable.rows.length;c<m;c++){
					var rowData = srcTable.rows[c].cloneNode(true);
					dstTable.appendChild(rowData);
				}
				window.parent.hikashop.closeBox();
		});";
		$document->addScriptDeclaration($js);
	}

	function selectrelated(){
		$type = JRequest::getCmd('select_type');
		$this->paramBase .= '_related_'.$type;
		switch($type){
			case 'field':
			case 'waitlist':
			case 'menu':
			case 'menu_0':
			case 'menu_1':
			case 'menu_2':
			case 'menu_3':
			case 'menu_4':
			case 'menu_5':
				$_REQUEST['filter_product_type']='all';
			default:
				break;
		}
		$this->listing();
		$this->assignRef('type',$type);
		$control = JRequest::getString('control');
		$this->assignRef('control',$control);
	}

	function addrelated(){
		$elements = JRequest::getVar( 'cid', array(), '', 'array' );
		$type = JRequest::getCmd('select_type');
		$this->assignRef('type',$type);
		$control = JRequest::getString('control');
		$this->assignRef('control',$control);
		$rows = array();
		if(!empty($elements)){
			JArrayHelper::toInteger($elements);
			$database	= JFactory::getDBO();
			$query = 'SELECT * FROM '.hikashop_table('product').' WHERE product_id IN ('.implode(',',$elements).')';
			$database->setQuery($query);
			$rows = $database->loadObjectList();
			if(!empty($rows)){
				$this->_loadPrices($rows);
			}
		}
		$this->assignRef('rows',$rows);
		if (!HIKASHOP_PHP5) {
			$document =& JFactory::getDocument();
		}else{
			$document = JFactory::getDocument();
		}
		$id = 'product_id';
		$layout = $type;
		switch($type){
			case 'menu_0':
				$id.='_0';
				$layout = 'menu';
				break;
			case 'menu_1':
				$id.='_1';
				$layout = 'menu';
				break;
			case 'menu_2':
				$id.='_2';
				$layout = 'menu';
				break;
			case 'menu_3':
				$id.='_3';
				$layout = 'menu';
				break;
			case 'menu_4':
				$id.='_4';
				$layout = 'menu';
				break;
			case 'menu_5':
				$id.='_5';
				$layout = 'menu';
				break;
			case 'menu_6':
				$id.='_6';
				$layout = 'menu';
				break;
			case 'menu_7':
				$id.='_7';
				$layout = 'menu';
				break;
		}
		switch($type){
			case 'discount':
			case 'limit':
			case 'field':
			case 'waitlist':
			case 'menu':
			case 'menu_0':
			case 'menu_1':
			case 'menu_2':
			case 'menu_3':
			case 'menu_4':
			case 'menu_5':
			case 'menu_6':
			case 'menu_7':
				$js = "window.hikashop.ready( function() {
						window.parent.document.getElementById('".$id."').innerHTML = document.getElementById('result').innerHTML;
						window.parent.hikashop.closeBox();
				});";
				$document->addScriptDeclaration($js);
				$this->setLayout($layout);
				break;
			case 'import':
				$js = "window.hikashop.ready( function() {
						window.parent.document.getElementById('template_product').innerHTML = document.getElementById('result').innerHTML;
						window.parent.hikashop.closeBox();
				});";
				$document->addScriptDeclaration($js);
				$this->setLayout('import');
				break;
			default:
				$js = "window.hikashop.ready( function() {
						var dstTable = window.parent.document.getElementById('".$type."_listing');
						var srcTable = document.getElementById('result');
						for (var c = 0,m=srcTable.rows.length;c<m;c++){
							var rowData = srcTable.rows[c].cloneNode(true);
							dstTable.appendChild(rowData);
						}
						window.parent.hikashop.closeBox();
				});";
				$document->addScriptDeclaration($js);
				$currencyClass = hikashop_get('class.currency');
				$this->assignRef('currencyHelper',$currencyClass);
				break;
		}
	}

	function selectimage(){
		$id = (int)hikashop_getCID( 'file_id');
		if(!empty($id)){
			$class = hikashop_get('class.file');
			$element = $class->get($id);
		}else{
			$element = new stdClass();
		}
		$this->assignRef('cid',$id);
		$this->assignRef('element',$element);
		$image=hikashop_get('helper.image');
		$this->assignRef('image',$image);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'file_description';
		$editor->content = @$element->file_description;
		$editor->height=200;
		$this->assignRef('editor',$editor);
	}

	function addimage(){
		$legacy = JRequest::getInt('legacy', 0);
		if($legacy) {
			$element = JRequest::getInt( 'cid');
			$rows = array();
			if(!empty($element)){
				$database	= JFactory::getDBO();
				$query = 'SELECT * FROM '.hikashop_table('file').' WHERE file_id ='.$element;
				$database->setQuery($query);
				$rows = $database->loadObjectList();
				if (!HIKASHOP_PHP5) {
					$document =& JFactory::getDocument();
				}else{
					$document = JFactory::getDocument();
				}
				$id = JRequest::getInt('id');
				$js = "window.hikashop.ready( function() {
						window.top.deleteRow('image_div_".$rows[0]->file_id.'_'.$id."','image[".$rows[0]->file_id."][".$id."]','image_".$rows[0]->file_id.'_'.$id."');
						var dstTable = window.top.document.getElementById('image_listing');
						var srcTable = document.getElementById('result');
						for (var c = 0,m=srcTable.rows.length;c<m;c++){
							var rowData = srcTable.rows[c].cloneNode(true);
							dstTable.appendChild(rowData);
						}
						setTimeout(function(){
							window.parent.hikashop.closeBox();
						},200);
				});";
				$document->addScriptDeclaration($js);
			}
			$this->assignRef('rows',$rows);
			$image=hikashop_get('helper.image');
			$this->assignRef('image',$image);
			$popup = hikashop_get('helper.popup');
			$this->assignRef('popup',$popup);

			return true;
		}

		$files_id = JRequest::getVar('cid', array(), '', 'array');
		$product_id = JRequest::getInt('product_id', 0);

		$output = '[]';
		if(!empty($files_id)) {
			JArrayHelper::toInteger($files_id);
			$query = 'SELECT * FROM '.hikashop_table('file').' WHERE file_id IN ('.implode(',',$files_id).')';
			$db = JFactory::getDBO();
			$db->setQuery($query);
			$files = $db->loadObjectList();

			$helperImage = hikashop_get('helper.image');
			$ret = array();
			foreach($files as $file) {

				$params = new stdClass();
				$params->product_id = $product_id;
				$params->file_id = $file->file_id;
				$params->file_path = $file->file_path;
				$params->file_name = $file->file_name;

				$ret[] = hikashop_getLayout('product', 'form_image_entry', $params, $js);
			}
			if(!empty($ret))
				$output = json_encode($ret);
		}
		$js = 'window.hikashop.ready(function(){window.top.hikashop.submitBox({images:'.$output.'});});';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);

		return false;
	}

	function selectfile(){
		$id = (int)hikashop_getCID( 'file_id');
		if(!empty($id)){
			$class = hikashop_get('class.file');
			$element = $class->get($id);
		}else{
			$element = new stdClass();
		}
		$this->assignRef('cid',$id);
		$this->assignRef('element',$element);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'file_description';
		$editor->content = @$element->file_description;
		$editor->height=200;
		$this->assignRef('editor',$editor);
		$config =& hikashop_config();
		$this->assignRef('config',$config);
	}

	function addfile(){
		$legacy = JRequest::getInt('legacy', 0);
		if($legacy) {
			$element = JRequest::getInt('cid');
			$rows = array();
			if(!empty($element)){
				$database = JFactory::getDBO();
				$query = 'SELECT * FROM '.hikashop_table('file').' WHERE file_id ='.$element;
				$database->setQuery($query);
				$rows = $database->loadObjectList();
				if (!HIKASHOP_PHP5) {
					$document =& JFactory::getDocument();
				}else{
					$document = JFactory::getDocument();
				}
				$id = JRequest::getInt('id');
				$js = "
				window.hikashop.ready( function() {
						window.top.deleteRow('file_div_".$rows[0]->file_id.'_'.$id."','file[".$rows[0]->file_id."][".$id."]','file_".$rows[0]->file_id.'_'.$id."');
						var dstTable = window.top.document.getElementById('file_listing');
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
			$config =& hikashop_config();
			$this->assignRef('config',$config);
			$popup = hikashop_get('helper.popup');
			$this->assignRef('popup',$popup);

			return true;
		}

		$file_id = (int)hikashop_getCID();
		$js = 'window.hikashop.ready(function(){window.parent.hikashop.submitBox({cid:'.$file_id.'});});';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);

		return false;
	}

	function galleryimage() {
		hikashop_loadJslib('otree');
		$app = JFactory::getApplication();

		$config = hikashop_config();
		$this->assignRef('config', $config);

		$pageInfo = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.gallery.list_limit', 'limit', 20, 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.gallery.limitstart', 'limitstart', 0, 'int' );
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.'.gallery.search', 'search', '', 'string');

		$this->assignRef('pageInfo', $pageInfo);

		$galleryHelper = hikashop_get('helper.gallery');
		$config =& hikashop_config();
		$uploadFolder = ltrim(JPath::clean(html_entity_decode($config->get('uploadfolder', ''))),DS);
		$uploadFolder = rtrim($uploadFolder,DS).DS;
		$uploadFolder = JPATH_ROOT.DS.$uploadFolder.DS;
		$galleryHelper->setRoot($uploadFolder);
		$this->assignRef('galleryHelper', $galleryHelper);

		$destFolder = rtrim(JRequest::getString('folder', ''), '/\\');
		if(!$galleryHelper->validatePath($destFolder))
			$destFolder = '';
		if(!empty($destFolder)) $destFolder .= '/';
		$this->assignRef('destFolder', $destFolder);

		$galleryOptions = array(
			'filter' => '.*' . str_replace(array('.','?','*','$','^'), array('\.','\?','\*','$','\^'), $pageInfo->search) . '.*',
			'offset' => $pageInfo->limit->start,
			'length' => $pageInfo->limit->value
		);
		$this->assignRef('galleryOptions', $galleryOptions);

		$treeContent = $galleryHelper->getTreeList(null, $destFolder);
		$this->assignRef('treeContent', $treeContent);

		$dirContent = $galleryHelper->getDirContent($destFolder, $galleryOptions);
		$this->assignRef('dirContent', $dirContent);

		if(empty($this->pageInfo->elements))
			$this->pageInfo->elements = new stdClass();
		$this->pageInfo->elements->total = $galleryHelper->filecount;
		$this->getPagination();
	}

	function priceaccess(){
		$js = "
		function hikashopSetACL() {
			acl = document.getElementById('hidden_price_access');
			price = window.top.document.getElementById('price_access_".JRequest::getInt('id')."');
			if(acl && price){
				price.value = acl.value;
			}
			window.parent.hikashop.closeBox();
		}";
		if (!HIKASHOP_PHP5) {
			$document =& JFactory::getDocument();
		}else{
			$document = JFactory::getDocument();
		}
		$document->addScriptDeclaration($js);
		$access = JRequest::getVar('access','');
		$this->assignRef('access',$access);
	}

	function export(){
		$product = hikashop_get('class.product');
		$products = JRequest::getVar( 'cid', array(), '', 'array' );
		$product->getProducts($products,'object');
		$products =& $product->all_products;

		if(!empty($products)){
			$currencies = array();
			foreach($products as $product){
				if(!empty($product->prices)){
					foreach($product->prices as $price){
						$currencies[$price->price_currency_id]=$price->price_currency_id;
					}
				}
			}
			if(!empty($currencies)){
				$currency = hikashop_get('class.currency');
				$null=null;
				$currencies = $currency->getCurrencies($currencies,$null);
			}

			$this->assignRef('currencies',$currencies);
		}
		$db = JFactory::getDBO();
		$db->setQuery('SELECT * FROM '.hikashop_table('category').' AS a WHERE a.category_type=\'product\' ORDER BY a.category_left ASC');

		$categories = $db->loadObjectList('category_id');

		$db = JFactory::getDBO();
		$db->setQuery('SELECT category_id, category_name FROM '.hikashop_table('category').' AS a WHERE a.category_type=\'brand\'');
		$brands = $db->loadObjectList('category_id');

		$db->setQuery('SELECT * FROM '.hikashop_table('file').' AS a WHERE a.file_type=\'category\' AND a.file_ref_id IN ('.implode(',',array_keys($categories)).')');

		$files = $db->loadObjectList('file_ref_id');
		foreach($categories as $id => $cat){
			if(isset($files[$id])){
				$categories[$id]->file_path=$files[$id]->file_path;
			}
		}
		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger( 'onBeforeProductExport', array( & $products, &$categories, &$this) );
		$this->assignRef('categories',$categories);
		$this->assignRef('brands',$brands);
		$this->assignRef('products',$products);
	}

	public function selection($tpl = null) {
		$singleSelection = JRequest::getVar('single', 0);
		$confirm = JRequest::getVar('confirm', 1);
		$this->assignRef('singleSelection', $singleSelection);
		$this->assignRef('confirm', $confirm);

		$elemStruct = array(
			'product_name',
			'product_code',
			'product_price',
			'product_quantity'
		);
		$this->assignRef('elemStruct', $elemStruct);

		$ctrl = JRequest::getCmd('ctrl');
		$this->assignRef('ctrl', $ctrl);

		$task = 'useselection';
		$this->assignRef('task', $task);

		$afterParams = array();
		$after = JRequest::getString('after', '');
		if(!empty($after)) {
			list($ctrl, $task) = explode('|', $after, 2);

			$afterParams = JRequest::getString('afterParams', '');
			$afterParams = explode(',', $afterParams);
			foreach($afterParams as &$p) {
				$p = explode('|', $p, 2);
				unset($p);
			}
		}
		$this->assignRef('afterParams', $afterParams);

		$cid = hikashop_getCID();
		if(empty($cid))
			$cid = 0;
		$this->assignRef('cid', $cid);
		JRequest::setVar('filter_id', $cid);

		$this->listing();

		$cid = $this->pageInfo->filter->filter_id;
		$shopCategoryType = hikashop_get('type.categorysub');
		$this->assignRef('shopCategoryType', $shopCategoryType);
	}

	public function useselection() {
		$products = JRequest::getVar('pid', array(), '', 'array');
		$rows = array();
		$data = '';
		$confirm = JRequest::getVar('confirm', true);
		$singleSelection = JRequest::getVar('single', false);

		$elemStruct = array(
			'product_name',
			'product_code',
			'product_price',
			'product_quantity'
		);

		if(!empty($products)) {
			JArrayHelper::toInteger($products);
		}

		$this->assignRef('rows', $rows);
		$this->assignRef('data', $data);
		$this->assignRef('confirm', $confirm);
		$this->assignRef('singleSelection', $singleSelection);

		if($confirm == true) {
			hikamarket::loadJslib('mootools');
			$js = 'window.hikashop.ready( function(){window.parent.hikashop.submitBox('.$data.');});';
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration($js);
		}
	}

	public function form() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ctrl = '';
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName().'.edit';
		$task = 'add';

		JHTML::_('behavior.tooltip');
		hikashop_loadJsLib('tooltip');

		$config = hikashop_config();
		$this->assignRef('config', $config);

		$product_id = hikashop_getCID('product_id');
		$productClass = hikashop_get('class.product');

		$main_currency = $config->get('main_currency',1);
		$this->assignRef('main_currency_id', $main_currency);

		$this->loadRef(array(
			'toggleClass' => 'helper.toggle',
			'currencyClass' => 'class.currency',
			'popup' => 'helper.popup',
			'quantityType' => 'type.quantity',
			'productsType' => 'type.products',
			'nameboxType' => 'type.namebox',
			'nameboxVariantType' => 'type.namebox',
			'uploaderType' => 'type.uploader',
			'imageHelper' => 'helper.image',
			'currencyType' => 'type.currency',
			'weight' => 'type.weight',
			'volume' => 'type.volume',
			'productDisplayType' => 'type.productdisplay',
			'quantityDisplayType' => 'type.quantitydisplay',
		));

		$categoryType = hikashop_get('type.categorysub');
		$categoryType->type = 'tax';
		$categoryType->field = 'category_id';
		$this->assignRef('categoryType',$categoryType);

		hikashop_loadJslib('jquery');

		$product = new stdClass();
		$product->product_description = '';
		$product->product_id = $product_id;
		$template_id = 0;
		$variant_id = 0;

		$failed_product = JRequest::getVar('fail', null);

		if(!empty($product_id))
			$product = $productClass->getRaw($product_id, true);

		if(!empty($product_id)) {
			$task = 'edit';

			if((int)$product->product_parent_id > 0) {
				$parentProduct = $productClass->getRaw((int)$product->product_parent_id, true);
				if(!empty($parentProduct)) {
					$variant_id = $product_id;
					$product_id = (int)$product->product_parent_id;
					unset($product);
					$product = $parentProduct;
				} else {
					unset($parentProduct);
				}
			}

			$query = 'SELECT b.* FROM '.hikashop_table('product_category').' AS a LEFT JOIN '.hikashop_table('category').' AS b ON a.category_id = b.category_id WHERE a.product_id = '.(int)$product_id.' ORDER BY a.product_category_id';
			$db->setQuery($query);
			$product->categories = $db->loadObjectList('category_id');

			$query = 'SELECT * FROM '.hikashop_table('file').' WHERE file_ref_id = '.(int)$product_id.' AND file_type=\'product\' ORDER BY file_ordering, file_id';
			$db->setQuery($query);
			$product->images = $db->loadObjectList();

			$query = 'SELECT file.*, SUM(download.download_number) AS download_number FROM '.hikashop_table('file').' AS file '.
				' LEFT JOIN '.hikashop_table('download').' AS download ON file.file_id = download.file_id '.
				' WHERE file_ref_id = '.(int)$product_id.' AND file.file_type='.$db->Quote('file').' '.
				' GROUP BY file.file_id '.
				' ORDER BY file.file_ordering, file.file_id';
			$db->setQuery($query);
			$product->files = $db->loadObjectList('file_id');

			$query = 'SELECT a.*,b.* FROM '.hikashop_table('product_related').' AS a LEFT JOIN '.hikashop_table('product').' AS b ON a.product_related_id=b.product_id WHERE a.product_related_type=\'related\' AND a.product_id = '.(int)$product_id.' ORDER BY a.product_related_ordering';
			$db->setQuery($query);
			$product->related = $db->loadObjectList();

			$query = 'SELECT a.*,b.* FROM '.hikashop_table('product_related').' AS a LEFT JOIN '.hikashop_table('product').' AS b ON a.product_related_id=b.product_id WHERE a.product_related_type=\'options\' AND a.product_id = '.(int)$product_id.' ORDER BY a.product_related_ordering';
			$db->setQuery($query);
			$product->options = $db->loadObjectList();

			$query = 'SELECT variant.*, characteristic.* FROM '.hikashop_table('variant').' as variant LEFT JOIN '.hikashop_table('characteristic').' as characteristic ON variant.variant_characteristic_id = characteristic.characteristic_id WHERE variant.variant_product_id = '.(int)$product_id . ' ORDER BY variant.ordering ASC, characteristic.characteristic_ordering ASC, ordering ASC';
			$db->setQuery($query);
			$product->characteristics = $db->loadObjectList('characteristic_id');
			$query = 'SELECT p.* FROM '.hikashop_table('product').' as p WHERE p.product_type = '.$db->Quote('variant').' AND p.product_parent_id = '.(int)$product_id;
			$db->setQuery($query);
			$product->variants = $db->loadObjectList('product_id');

			if(!empty($product->variants)) {
				$variant_ids = array_keys($product->variants);
				$query = 'SELECT * FROM '.hikashop_table('price').' WHERE price_product_id IN (' . (int)$product_id . ',' . implode(',', $variant_ids).')';
				$db->setQuery($query);
				$prices = $db->loadObjectList();

				$product->prices = array();
				foreach($prices as $price) {
					$ppid = (int)$price->price_product_id;
					if($ppid == $product_id) {
						$product->prices[] = $price;
					} elseif(isset($product->variants[$ppid])) {
						if(empty($product->variants[$ppid]->prices))
							$product->variants[$ppid]->prices = array();
						$product->variants[$ppid]->prices[] = $price;
					}
				}
				unset($prices);

				$query = 'SELECT v.*, c.* FROM '.hikashop_table('variant').' AS v '.
					' INNER JOIN '.hikashop_table('characteristic').' AS c ON c.characteristic_id = v.variant_characteristic_id '.
					' WHERE v.variant_product_id IN ('.implode(',',$variant_ids).')'.
					' ORDER BY v.ordering ASC, c.characteristic_ordering ASC, v.variant_product_id ASC, v.variant_characteristic_id ASC';
				$db->setQuery($query);
				$variant_data = $db->loadObjectList();

				foreach($variant_data as $d) {
					$ppid = (int)$d->variant_product_id;
					if(!isset($product->characteristics[$d->characteristic_parent_id]))
						continue;

					if(!isset($product->variants[$ppid]))
						continue;

					if(empty($product->variants[$ppid]->characteristics))
						$product->variants[$ppid]->characteristics = array();

					$pcid = $product->characteristics[$d->characteristic_parent_id]->characteristic_id;
					$value = new stdClass();
					$value->id = $d->characteristic_id;
					$value->value = $d->characteristic_value;
					$product->variants[$ppid]->characteristics[$pcid] = $value;
				}
			} else {
				$query = 'SELECT * FROM '.hikashop_table('price').' WHERE price_product_id = ' . (int)$product_id;
				$db->setQuery($query);
				$product->prices = $db->loadObjectList();
			}
		} else if(!empty($failed_product)) {
			$product = $failed_product;

			if(!empty($product->related)) {
				$tmp_ids = array();
				foreach($product->related as $v) {
					$tmp_ids[(int)$v->product_related_id] = (int)$v->product_related_ordering;
				}
				$query = 'SELECT p.* FROM '.hikashop_table('product').' AS p WHERE p.product_id IN (' . implode(',', array_keys($tmp_ids)) . ')';
				$db->setQuery($query);
				$product->related = $db->loadObjectList();
				foreach($product->related as $k => $v) {
					$product->related[$k]->product_related_id = $v->product_id;
					$product->related[$k]->product_related_ordering = $tmp_ids[$v->product_id];
				}
			}

			if(!empty($element->options)) {
				$tmp_ids = array();
				foreach($product->options as $v) {
					$rel_ids[(int)$v->product_related_id] = (int)$v->product_related_ordering;
				}
				$query = 'SELECT p.* FROM '.hikashop_table('product').' AS p WHERE p.product_id IN (' . implode(',', array_keys($tmp_ids)) . ')';
				$db->setQuery($query);
				$product->options = $db->loadObjectList();
				foreach($product->options as $k => $v) {
					$product->options[$k]->product_related_id = $v->product_id;
					$product->options[$k]->product_related_ordering = $tmp_ids[$v->product_id];
				}
			}

			if(!empty($product->characteristics)) {
				$tmp_ids = array();
				foreach($product->characteristics as $k => $v) {
					$tmp_ids[ (int)$v->characteristic_id ] = $k;
				}
				$query = 'SELECT c.* FROM '.hikashop_table('characteristic').' AS c WHERE c.characteristic_id IN (' . implode(',', array_keys($tmp_ids)) . ')';
				$db->setQuery($query);
				$characteristics = $db->loadObjectList();
				foreach($characteristics as $char) {
					$product->characteristics[ $tmp_ids[$char->characteristic_id] ]->characteristic_value = $char->characteristic_value;
				}
				unset($characteristics);
			}

			if(!empty($product->categories)) {
				JArrayHelper::toInteger($product->categories);
				$query = 'SELECT c.* FROM '.hikashop_table('category').' AS c WHERE c.category_id IN ('.implode(',', $product->categories).')';
				$db->setQuery($query);
				$product->categories = $db->loadObjectList('category_id');
			}
		} else {
			$product = new stdClass();
			$product->product_published = 1;
			$product->product_type = 'main';
			$product->product_quantity = -1;
			$product->product_description = '';

			$categoryClass = hikashop_get('class.category');
			$mainTaxCategory = 'tax';
			$categoryClass->getMainElement($mainTaxCategory);
			$query = 'SELECT category_id FROM '. hikashop_table('category'). ' WHERE category_type = ' . $db->Quote('tax') . ' AND category_parent_id = '.(int)$mainTaxCategory.' ORDER BY category_ordering DESC';
			$db->setQuery($query);
			$product->product_tax_id = $db->loadResult();
		}

		if(empty($product_id) && empty($product->categories)) {
			$rootCategory = 0;
			$categoryClass = hikashop_get('class.category');
			$category_explorer = $config->get('show_category_explorer', 1);
			if($category_explorer)
				$rootCategory = JRequest::getVar('filter_id','1');
			if(empty($rootCategory) || $rootCategory == 1){
				$rootCategory = 'product';
				$categoryClass->getMainElement($rootCategory);
			}
			if(!empty($rootCategory)) {
				if(empty($product->categories))
					$product->categories = array( $rootCategory => $categoryClass->get($rootCategory) );
				else
					$product->categories[$rootCategory] = $categoryClass->get($rootCategory);
			}
		}

		if(!empty($product->product_tax_id)) {
			$main_tax_zone = explode(',', $config->get('main_tax_zone', ''));
			if(count($main_tax_zone)) {
				$main_tax_zone = array_shift($main_tax_zone);
			}
		}
		$price_currencies = array();
		if(!empty($product->prices)) {
			foreach($product->prices as $key => $price) {
				if(empty($price->price_value))
					unset($product->prices[$key]);
				$price_currencies[ (int)$price->price_currency_id ] = (int)$price->price_currency_id;
			}
			if(!empty($product->product_tax_id)) {
				foreach($product->prices as &$price) {
					$price->price_value_with_tax = $this->currencyClass->getTaxedPrice($price->price_value, $main_tax_zone, $product->product_tax_id);
				}
			} else {
				foreach($product->prices as $key => $price) {
					$price->price_value_with_tax = $price->price_value;
				}
			}
		}
		if(empty($product->prices)) {
			$obj = new stdClass();
			$obj->price_value = 0;
			$obj->price_value_with_tax = 0;
			$obj->price_currency_id = $main_currency;
			$product->prices = array($obj);
		}

		$editor = hikashop_get('helper.editor');
		$editor->setEditor($config->get('editor', ''));
		$editor->name = 'product_description';
		$editor->content = $product->product_description;
		$editor->height = 200;
		$this->assignRef('editor', $editor);

		if(!isset($product->product_quantity) || $product->product_quantity < 0)
			$product->product_quantity = JText::_('UNLIMITED');
		if(!isset($product->product_max_per_order) || $product->product_max_per_order <= 0)
			$product->product_max_per_order = JText::_('UNLIMITED');

		$this->assignRef('product', $product);

		if(hikashop_level(2)) {
			hikashop_loadJslib('otree');
			$joomlaAcl = hikashop_get('type.joomla_acl');
			$this->assignRef('joomlaAcl', $joomlaAcl);
		}

		$translationHelper = hikashop_get('helper.translation');
		if($translationHelper && $translationHelper->isMulti()) {
			$translationHelper->load('hikashop_product', @$product->product_id, $product);
			$this->assignRef('translationHelper', $translationHelper);
		}

		$manufacturerType = hikashop_get('type.categorysub');
		$manufacturerType->type = 'manufacturer';
		$manufacturerType->field = 'category_id';
		$this->assignRef('manufacturerType', $manufacturerType);

		$main_currency = (int)$config->get('main_currency');
		$this->currencyType->load($main_currency);
		$currencies = $this->currencyType->currencies;
		$this->assignRef('currencies', $currencies);
		$default_currency = $this->currencyType->currencies[$main_currency];
		$this->assignRef('default_currency', $default_currency);

		if(!empty($price_currencies)) {
			$missing_currencies = array_diff($price_currencies, array_keys($currencies));
			if(!empty($missing_currencies)) {
				$this->currencyType->currencies = array();
				$this->currencyType->load($price_currencies);
				$currencies = $this->currencyType->currencies;

				$missing_currencies_codes = array();
				foreach($missing_currencies as $k) {
					$missing_currencies_codes[] = $currencies[$k]->currency_code;
				}
				$app->enqueueMessage(JText::sprintf('PRICES_USING_UNPUBLISHED_CURRENCY', implode(',', $missing_currencies_codes)), 'warning');
			}
		}

		$fieldsClass = hikashop_get('class.field');
		$fields = $fieldsClass->getFields('backend', $product, 'product', 'field&task=state');
		$null = array();
		$fieldsClass->addJS($null, $null, $null);
		$fieldsClass->jsToggle($fields, $product, 0);
		$this->assignRef('fieldsClass', $fieldsClass);
		$this->assignRef('fields', $fields);

		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&product_id='.$product_id);

		if(!HIKASHOP_J16)
			$url = hikashop_completeLink('product&task=updatecart&cid='.$product_id,true);
		else
			$url = 'index.php?option=com_hikashop&ctrl=product&task=updatecart&tmpl=component&cid='.$product_id;

		$this->toolbar = array(
			array('name' => 'popup','icon'=>'upload','alt'=>JText::_('ADD_TO_CART_HTML_CODE'),'url'=>$url),
			'|',
			'save',
			array('name' => 'save2new', 'display' => HIKASHOP_J17),
			'apply',
			'cancel' => 'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);

		$cancel_url = JRequest::getVar('cancel_redirect');
		if(!empty($cancel_url)) {
			$url = base64_decode($cancel_url);
			$this->toolbar['cancel'] = array('name' => 'link', 'icon'=>'cancel','alt'=>JText::_('HIKA_BACK'),'url'=>$url);
		}

		$cancel_action = JRequest::getCmd('cancel_action', '');
		$this->assignRef('cancel_action', $cancel_action);
		$cancel_url = JRequest::getCmd('cancel_url', '');
		$this->assignRef('cancel_url', $cancel_url);
	}

	public function form_variants() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ctrl = '';
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName().'.edit';

		$config = hikashop_config();
		$this->assignRef('config', $config);

		$product_id = hikashop_getCID('product_id');
		$productClass = hikashop_get('class.product');

		$main_currency = $config->get('main_currency',1);
		$this->assignRef('main_currency_id', $main_currency);

		$this->loadRef(array(
			'toggleClass' => 'helper.toggle',
			'currencyClass' => 'class.currency',
		));

		$product = new stdClass();
		$product->product_description = '';
		$product->product_id = $product_id;
		$variant_id = 0;

		if(!empty($product_id)) {
			$product = $productClass->getRaw($product_id, true);

			$query = 'SELECT variant.*, characteristic.* FROM '.hikashop_table('variant').' as variant LEFT JOIN '.hikashop_table('characteristic').' as characteristic ON variant.variant_characteristic_id = characteristic.characteristic_id WHERE variant.variant_product_id = '.(int)$product_id . ' ORDER BY ordering ASC';
			$db->setQuery($query);
			$product->characteristics = $db->loadObjectList('characteristic_id');

			$query = 'SELECT p.* FROM '.hikashop_table('product').' as p WHERE p.product_type = '.$db->Quote('variant').' AND p.product_parent_id = '.(int)$product_id;
			$db->setQuery($query);
			$product->variants = $db->loadObjectList('product_id');

			if(!empty($product->variants)) {
				$variant_ids = array_keys($product->variants);
				$query = 'SELECT * FROM '.hikashop_table('price').' WHERE price_product_id IN (' . (int)$product_id . ',' . implode(',', $variant_ids).')';
				$db->setQuery($query);
				$prices = $db->loadObjectList();

				foreach($prices as $price) {
					$ppid = (int)$price->price_product_id;
					if(isset($product->variants[$ppid])) {
						if(empty($product->variants[$ppid]->prices))
							$product->variants[$ppid]->prices = array();
						$product->variants[$ppid]->prices[] = $price;
					}
				}
				unset($prices);

				$query = 'SELECT v.*, c.* FROM '.hikashop_table('variant').' AS v '.
					' INNER JOIN '.hikashop_table('characteristic').' AS c ON c.characteristic_id = v.variant_characteristic_id '.
					' WHERE v.variant_product_id IN ('.implode(',',$variant_ids).') '.
					' ORDER BY v.variant_product_id ASC, v.variant_characteristic_id ASC, v.ordering ASC';
				$db->setQuery($query);
				$variant_data = $db->loadObjectList();

				foreach($variant_data as $d) {
					$ppid = (int)$d->variant_product_id;
					if(isset($product->variants[$ppid])) {
						if(empty($product->variants[$ppid]->characteristics))
							$product->variants[$ppid]->characteristics = array();

						$pcid = (int)$product->characteristics[$d->characteristic_parent_id]->characteristic_id;
						$value = new stdClass();
						$value->id = $d->characteristic_id;
						$value->value = $d->characteristic_value;
						$product->variants[$ppid]->characteristics[$pcid] = $value;
					}
				}
			}
		}

		$this->assignRef('product', $product);
	}

	public function variant() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ctrl = '';
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName().'.edit';

		JHTML::_('behavior.tooltip');

		$config = hikashop_config();
		$this->assignRef('config', $config);

		$product_id = hikashop_getCID('variant_id');
		$product_parent_id = JRequest::getInt('product_id');
		$productClass = hikashop_get('class.product');

		$editing_variant = true;
		$this->assignRef('editing_variant', $editing_variant);

		$main_currency = $config->get('main_currency',1);
		$this->assignRef('main_currency_id', $main_currency);

		$this->loadRef(array(
			'toggleClass' => 'helper.toggle',
			'currencyClass' => 'class.currency',
			'popup' => 'helper.popup',
			'quantityType' => 'type.quantity',
			'uploaderType' => 'type.uploader',
			'imageHelper' => 'helper.image',
			'currencyType' => 'type.currency',
			'weight' => 'type.weight',
			'volume' => 'type.volume',
			'characteristicType' => 'type.characteristic'
		));

		if(!empty($product_id)) {
			$product = $productClass->getRaw($product_id, true);

			if((int)$product->product_parent_id != (int)$product_parent_id)
				return false;

			$product->main = $productClass->get($product_parent_id);
			$product->product_tax_id = $product->main->product_tax_id;

			$query = 'SELECT * FROM '.hikashop_table('file').' WHERE file_ref_id = '.(int)$product_id.' AND file_type=\'product\' ORDER BY file_ordering, file_id';
			$db->setQuery($query);
			$product->images = $db->loadObjectList();

			$query = 'SELECT * FROM '.hikashop_table('file').' WHERE file_ref_id = '.(int)$product_id.' AND file_type=\'file\' ORDER BY file_ordering, file_id';
			$db->setQuery($query);
			$product->files = $db->loadObjectList('file_id');

			$query = 'SELECT * FROM '.hikashop_table('price').' WHERE price_product_id = ' . (int)$product_id;
			$db->setQuery($query);
			$product->prices = $db->loadObjectList();

			$query = 'SELECT v.*, c.* FROM '.hikashop_table('variant').' AS v '.
				' INNER JOIN '.hikashop_table('characteristic').' as c ON v.variant_characteristic_id = c.characteristic_id '.
				' WHERE characteristic_parent_id > 0 AND variant_product_id = ' . (int)$product_id;
			$db->setQuery($query);
			$characteristic_values = $db->loadObjectList('characteristic_parent_id');

			if(!empty($characteristic_values) && count($characteristic_values)){
				$query = 'SELECT * FROM '.hikashop_table('characteristic').
					' WHERE characteristic_id IN ('.implode(',',array_keys($characteristic_values)).') OR characteristic_parent_id IN ('.implode(',',array_keys($characteristic_values)).') '.
					' ORDER BY characteristic_parent_id ASC';
				$db->setQuery($query);
				$characteristics = $db->loadObjectList();

				$product->characteristics = array();
				foreach($characteristics as $c) {
					$charac_pid = ((int)$c->characteristic_parent_id == 0) ? (int)$c->characteristic_id : (int)$c->characteristic_parent_id;
					if(!isset($product->characteristics[$charac_pid])) {
						$product->characteristics[$charac_pid] = new stdClass();
						$product->characteristics[$charac_pid]->values = array();
					}
					if(((int)$c->characteristic_parent_id == 0)) {
						foreach($c as $k => $v)
							$product->characteristics[$charac_pid]->$k = $v;
					} else {
						$product->characteristics[$charac_pid]->values[ (int)$c->characteristic_id ] = $c->characteristic_value;
					}
				}
				foreach($characteristic_values as $k => $v) {
					$product->characteristics[$k]->default_id = (int)$v->characteristic_id;
				}
			}
		}

		if(!empty($product->product_tax_id)) {
			$main_tax_zone = explode(',', $config->get('main_tax_zone', ''));
			if(count($main_tax_zone)) {
				$main_tax_zone = array_shift($main_tax_zone);
			}
		}
		if(!empty($product->prices)) {
			foreach($product->prices as $key => $price) {
				if(empty($price->price_value)){
					unset($product->prices[$key]);
				}
			}
			if(!empty($product->product_tax_id)) {
				foreach($product->prices as &$price) {
					$price->price_value_with_tax = $this->currencyClass->getTaxedPrice($price->price_value, $main_tax_zone, $product->product_tax_id);
				}
			}else{
				foreach($product->prices as $key => $price) {
					$price->price_value_with_tax = $price->price_value;
				}
			}
		}
		if(empty($product->prices)) {
			$obj = new stdClass();
			$obj->price_value = 0;
			$obj->price_value_with_tax = 0;
			$obj->price_currency_id = $main_currency;
			$product->prices = array($obj);
		}

		$editor = hikashop_get('helper.editor');
		$editor->setEditor($config->get('editor', ''));
		$editor->id = 'product_variant_editors_'.time();
		$editor->name = 'product_variant_description';
		$editor->content = $product->product_description;
		$editor->height = 200;
		$this->assignRef('editor', $editor);

		if(!isset($product->product_quantity) || $product->product_quantity < 0)
			$product->product_quantity = JText::_('UNLIMITED');
		if(!isset($product->product_max_per_order) || $product->product_max_per_order <= 0)
			$product->product_max_per_order = JText::_('UNLIMITED');

		$this->assignRef('product', $product);

		if(hikashop_level(2)) {
			hikashop_loadJslib('otree');
			$joomlaAcl = hikashop_get('type.joomla_acl');
			$this->assignRef('joomlaAcl', $joomlaAcl);
		}

		$translationHelper = hikashop_get('helper.translation');
		if($translationHelper && $translationHelper->isMulti()) {
			$translationHelper->load('hikashop_product', @$product->product_id, $product);
			$this->assignRef('translationHelper', $translationHelper);
		}

		$main_currency = (int)$config->get('main_currency');
		$this->currencyType->load($main_currency);
		$currencies = $this->currencyType->currencies;
		$this->assignRef('currencies', $currencies);
		$default_currency = $this->currencyType->currencies[$main_currency];
		$this->assignRef('default_currency', $default_currency);

		$fieldsClass = hikashop_get('class.field');
		$fields = $fieldsClass->getFields('backend', $product, 'product', 'field&task=state');
		$null = array();
		$fieldsClass->addJS($null, $null, $null);
		$fieldsClass->jsToggle($fields, $product, 0);
		$this->assignRef('fieldsClass', $fieldsClass);
		$this->assignRef('fields', $fields);

		return true;
	}

	public function form_image_entry() {
		if(empty($this->popup)) {
			$popup = hikashop_get('helper.popup');
			$this->assignRef('popup', $popup);
		}
		$imageHelper = hikashop_get('helper.image');
		$this->assignRef('imageHelper', $imageHelper);
	}

	public function form_file_entry() {
		$file_id = (int)hikashop_getCID();
		$this->assignRef('cid', $file_id);

		$product_id = JRequest::getInt('pid', 0);
		$this->assignRef('product_id', $product_id);

		$config = hikashop_config(false);
		$this->assignRef('config', $config);

		if(empty($this->popup)) {
			$popup = hikashop_get('helper.popup');
			$this->assignRef('popup', $popup);
		}

		if(empty($this->params) && empty($this->params->file_id)) {
			$element = new stdClass();
			if(!empty($file_id)){
				$fileClass = hikashop_get('class.file');
				$element = $fileClass->get($file_id);
			}
			$element->product_id = $product_id;

			if($element->product_id){
				$productClass = hikashop_get('class.product');
				$product = $productClass->get($element->product_id);
				$element->product_type = $product->product_type;
			}

			$this->assignRef('params', $element);
		}
	}

	public function form_variants_add() {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$ctrl = '';
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName().'.edit';

		$config = hikashop_config();
		$this->assignRef('config', $config);

		$this->loadRef(array(
			'nameboxVariantType' => 'type.namebox',
		));

		$this->nameboxVariantType->setType('characteristic_value', array());

		$product_id = hikashop_getCID('product_id');
		$this->assignRef('product_id', $product_id);

		$subtask = JRequest::getCmd('subtask', '');
		if($subtask == 'duplicate') {

		}
		$this->assignRef('subtask', $subtask);

		$characteristics = array();
		if(!empty($product_id)) {
			$query = 'SELECT v.*, c.* FROM '.hikashop_table('variant').' AS v '.
				' INNER JOIN '.hikashop_table('characteristic').' as c ON v.variant_characteristic_id = c.characteristic_id '.
				' WHERE characteristic_parent_id = 0 AND variant_product_id = ' . (int)$product_id . ' ORDER BY ordering';
			$db->setQuery($query);
			$characteristics = $db->loadObjectList('characteristic_id');
		}
		$this->assignRef('characteristics', $characteristics);
	}

	public function edit_translation() {
		$language_id = JRequest::getInt('language_id', 0);
		$this->assignRef('language_id', $language_id);

		$product_id = hikashop_getCID('product_id');

		$config = hikashop_config();
		$this->assignRef('config', $config);

		$productClass = hikashop_get('class.product');
		$product = $productClass->getRaw($product_id);

		$translationHelper = hikashop_get('helper.translation');
		if($translationHelper && $translationHelper->isMulti()) {
			$translationHelper->load('hikashop_product', @$product->product_id, $product, $language_id);
			$this->assignRef('translationHelper', $translationHelper);
		}

		$editor = hikashop_get('helper.editor');
		$editor->setEditor($config->get('editor', ''));
		$editor->content = @$product->product_description;
		$editor->height = 300;
		$this->assignRef('editor', $editor);

		$toggle = hikashop_get('helper.toggle');
		$this->assignRef('toggle', $toggle);

		$this->assignRef('product', $product);

		$fieldsClass = hikashop_get('class.field');
		$fields = $fieldsClass->getFields('backend', $product, 'product', 'field&task=state');
		$this->assignRef('fieldsClass', $fieldsClass);
		$this->assignRef('fields', $fields);


		$this->toolbar = array(
			array(
				'url' => '#save',
				'linkattribs' => 'onclick="return window.hikashop.submitform(\'save_translation\',\'adminForm\');"',
				'icon' => 'save',
				'name' => JText::_('HIKA_SAVE'), 'pos' => 'right'
			)
		);
	}
}
