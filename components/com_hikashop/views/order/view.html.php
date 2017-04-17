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
class OrderViewOrder extends hikashopView{
	var $ctrl= 'order';
	var $nameListing = 'ORDERS';
	var $nameForm = 'HIKASHOP_ORDER';
	var $icon = 'order';
	var $triggerView = true;

	function display($tpl = null,$params=array()){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		$this->params = $params;
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}

	function listing(){
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.order_created','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$oldValue = $app->getUserState($this->paramBase.'.list_limit');
		if(empty($oldValue)){
			$oldValue = $app->getCfg('list_limit');
		}
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if($oldValue!=$pageInfo->limit->value){
			$pageInfo->limit->start = 0;
			$app->setUserState($this->paramBase.'.limitstart',0);
		}

		$database = JFactory::getDBO();
		$searchMap = array('a.order_id','a.order_status','a.order_number');
		$filters = array();
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped(JString::strtolower(trim($pageInfo->search)),true).'%\'';
			$filter = '('.implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal".')';
			$filters[] =  $filter;
		}
		if(is_array($filters) && count($filters)){
			$filters = ' AND '.implode(' AND ',$filters);
		}else{
			$filters = '';
		}
		$query = 'FROM '.hikashop_table('order').' AS a WHERE a.order_type = '.$database->Quote('sale').' AND a.order_user_id='.(int)hikashop_loadUser().$filters.$order;
		$database->setQuery('SELECT a.* '.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'order_id');
		}
		$database->setQuery('SELECT COUNT(*) '.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
		if(!$pageInfo->elements->page){
			$app->enqueueMessage(JText::_('NO_ORDERS_FOUND'));
		}
		$pagination = hikashop_get('helper.pagination', $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);
		$pagination->hikaSuffix = '';
		$this->assignRef('pagination',$pagination);

		$this->assignRef('pageInfo',$pageInfo);

		$string = '';
		$params = new HikaParameter($string);
		$params->set('show_quantity_field',0);
		$config =& hikashop_config();
		if(hikashop_level(1) && $config->get('allow_payment_button',1)){
			$unpaid_statuses = explode(',',$config->get('order_unpaid_statuses','created'));
			if(!empty($rows)){
				foreach($rows as $k => $order){
					if(in_array($order->order_status,$unpaid_statuses)){
						$rows[$k]->show_payment_button = true;
					}
				}
			}
			$payment_change = $config->get('allow_payment_change',1);
			$this->assignRef('payment_change',$payment_change);
			$pluginsPayment = hikashop_get('type.plugins');
			$pluginsPayment->type='payment';
			$this->assignRef('payment',$pluginsPayment);
		}
		if( $config->get('cancellable_order_status','') != '' ) {
			$cancellable_order_status = explode(',',$config->get('cancellable_order_status',''));
			foreach($rows as $k => $order){
				if( in_array($order->order_status, $cancellable_order_status) ){
					$rows[$k]->show_cancel_button = true;
				}
			}
		}
		$this->assignRef('params',$params);
		$this->assignRef('rows',$rows);
		$this->assignRef('config',$config);
		$cart = hikashop_get('helper.cart');
		$this->assignRef('cart',$cart);

		$category = hikashop_get('type.categorysub');
		$category->type = 'status';
		$category->load(true);
		$this->assignRef('order_statuses',$category);
		hikashop_setPageTitle('ORDERS');
	}

	function show(){
		$type = 'order';
		$order =& $this->_order($type);
		$config =& hikashop_config();
		$download_time_limit = $config->get('download_time_limit',0);
		$this->assignRef('download_time_limit',$download_time_limit);
		$download_number_limit = $config->get('download_number_limit',0);
		$this->assignRef('download_number_limit',$download_number_limit);
		$order_status_download_ok=false;
		$order_status_for_download = $config->get('order_status_for_download','confirmed,shipped');
		if(in_array($order->order_status,explode(',',$order_status_for_download))){
			$order_status_download_ok=true;
		}
		$this->assignRef('order_status_download_ok',$order_status_download_ok);

		$popup = hikashop_get('helper.popup');
		$this->assignRef('popup',$popup);

		hikashop_setPageTitle(JText::_('HIKASHOP_ORDER').':'.$this->element->order_number);
	}

	function invoice(){
		$type = 'invoice';
		$this->setLayout('show');
		$order =& $this->_order($type);
		$js = "window.hikashop.ready( function() {setTimeout(function(){window.focus();window.print();setTimeout(function(){hikashop.closeBox();}, 1000);},1000);});";
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
	}

	function &_order($type){
		$order_id = hikashop_getCID('order_id');
		$app = JFactory::getApplication();
		if(empty($order_id)){
			$order_id = $app->getUserState('com_hikashop.order_id');
		}
		if(!empty($order_id)){
			$class = hikashop_get('class.order');
			$order = $class->loadFullOrder($order_id,($type=='order'?true:false));
		}
		if(empty($order)){
			$app->redirect(hikashop_completeLink('order&task=listing',false,true));
		}
		$config =& hikashop_config();
		$this->assignRef('config',$config);
		$store = str_replace(array("\r\n","\n","\r"),array('<br/>','<br/>','<br/>'),$config->get('store_address',''));
		if(JText::_($store)!=$store){
			$store = JText::_($store);
		}

		if(!empty($order->order_payment_id)){
			$pluginsPayment = hikashop_get('type.plugins');
			$pluginsPayment->type='payment';
			$this->assignRef('payment',$pluginsPayment);
		}
		if(!empty($order->order_shipping_id)){
			$pluginsShipping = hikashop_get('type.plugins');
			$pluginsShipping->type='shipping';
			$this->assignRef('shipping',$pluginsShipping);

			$shippingClass = hikashop_get('class.shipping');
			$this->assignRef('shippingClass', $shippingClass);

			if(empty($order->order_shipping_method)) {
				$shippings_data = array();
				$shipping_ids = explode(';', $order->order_shipping_id);
				foreach($shipping_ids as $key) {
					$shipping_data = '';
					list($k, $w) = explode('@', $key);
					$shipping_id = $k;
					if(isset($order->shippings[$shipping_id])) {
						$shipping = $order->shippings[$shipping_id];
						$shipping_data = $shipping->shipping_name;
					} else {
						foreach($order->products as $order_product) {
							if($order_product->order_product_shipping_id == $key) {
								if(!is_numeric($order_product->order_product_shipping_id)) {
									$shipping_name = $this->getShippingName($order_product->order_product_shipping_method, $shipping_id);
									$shipping_data = $shipping_name;
								} else {
									$shipping_method_data = $this->shippingClass->get($shipping_id);
									$shipping_data = $shipping_method_data->shipping_name;
								}
								break;
							}
						}
						if(empty($shipping_data))
							$shipping_data = '[ ' . $key . ' ]';
					}
					$shippings_data[] = $shipping_data;
				}
				$order->order_shipping_method = $shippings_data;
			}
		}

		$this->assignRef('store_address',$store);
		$this->assignRef('element',$order);
		$this->assignRef('order',$order);
		$this->assignRef('invoice_type',$type);
		$display_type = 'frontcomp';
		$this->assignRef('display_type',$display_type);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass',$fieldsClass);
		if(is_string($order->order_shipping_method))
			$currentShipping = hikashop_import('hikashopshipping',$order->order_shipping_method);
		else
			$currentShipping = hikashop_import('hikashopshipping', reset($order->order_shipping_method));
		$this->assignRef('currentShipping',$currentShipping);
		$fields = array();
		if(hikashop_level(2)){
			$null = null;
			$fields['entry'] = $fieldsClass->getFields('frontcomp',$null,'entry');
			$fields['item'] = $fieldsClass->getFields('frontcomp',$null,'item');
			$fields['order'] = $fieldsClass->getFields('',$null,'order');
		}
		$this->assignRef('fields',$fields);
		return $order;
	}

	function getShippingName($shipping_method, $shipping_id) {
		$shipping_name = $shipping_method . ' ' . $shipping_id;
		if(strpos($shipping_id, '-') !== false) {
			$shipping_ids = explode('-', $shipping_id, 2);
			$shipping = $this->shippingClass->get($shipping_ids[0]);
			if(!empty($shipping->shipping_params) && is_string($shipping->shipping_params))
				$shipping->shipping_params = unserialize($shipping->shipping_params);
			$shippingMethod = hikashop_import('hikashopshipping', $shipping_method);
			$methods = $shippingMethod->shippingMethods($shipping);

			if(isset($methods[$shipping_id])){
				$shipping_name = $shipping->shipping_name.' - '.$methods[$shipping_id];
			}else{
				$shipping_name = $shipping_id;
			}
		}
		return $shipping_name;
	}
}
