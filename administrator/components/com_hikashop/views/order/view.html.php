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
	var $displayCompleted = false;
	var $triggerView = true;

	function display($tpl = null, $params = null){
		if(empty($params))
			$params = new HikaParameter('');
		$this->assignRef('params',$params);
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		if(empty($this->displayCompleted)) parent::display($tpl);
	}

	function listing(){
		$app = JFactory::getApplication();
		$fieldsClass = hikashop_get('class.field');
		$fields = $fieldsClass->getData('backend_listing','order',false);
		$popup = (JRequest::getString('tmpl') === 'component');
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'b.order_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if(empty($pageInfo->limit->value))
			$pageInfo->limit->value = 500;
		if(JRequest::getVar('search') != $app->getUserState($this->paramBase.".search")){
			$app->setUserState( $this->paramBase.'.limitstart',0);
			$pageInfo->limit->start = 0;
		}else{
			$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		}
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string');
		$pageInfo->search = trim($pageInfo->search);

		$pageInfo->filter->filter_status = $app->getUserStateFromRequest($this->paramBase.".filter_status",'filter_status','','array');
		$pageInfo->filter->filter_payment = $app->getUserStateFromRequest($this->paramBase.".filter_payment",'filter_payment','','string');
		$pageInfo->filter->filter_partner = $app->getUserStateFromRequest($this->paramBase.".filter_partner",'filter_partner','','int');
		$pageInfo->filter->filter_end = $app->getUserStateFromRequest($this->paramBase.".filter_end",'filter_end','','string');
		$pageInfo->filter->filter_start = $app->getUserStateFromRequest($this->paramBase.".filter_start",'filter_start','','string');
		$pageInfo->filter->filter_product = JRequest::getInt('filter_product',0);
		$database = JFactory::getDBO();
		$tables = array();
		$filters = array('b.order_type=\'sale\'');

		if(is_array($pageInfo->filter->filter_status) && count($pageInfo->filter->filter_status) == 1) {
			$pageInfo->filter->filter_status = reset($pageInfo->filter->filter_status);
		}
		switch($pageInfo->filter->filter_status){
			case '':
				break;
			default:
				if(!is_array($pageInfo->filter->filter_status)) {
					$filters[] = 'b.order_status = '.$database->Quote($pageInfo->filter->filter_status);
					break;
				}
				if(!count($pageInfo->filter->filter_status) || in_array('', $pageInfo->filter->filter_status))
					break;
				$statuses = array();
				foreach($pageInfo->filter->filter_status as $status){
					$statuses[] = $database->Quote($status);
				}
				$filters[]='b.order_status IN ('.implode(',',$statuses).')';
				break;
		}
		switch($pageInfo->filter->filter_start){
			case '':
				switch($pageInfo->filter->filter_end){
					case '':
						break;
					default:
						$filter_end=explode('-',$pageInfo->filter->filter_end);
						$noHourDay=explode(' ',$filter_end[2]);
						$filter_end[2]=$noHourDay[0];
						$filter_end= hikashop_getTime(mktime(23, 59, 59, $filter_end[1], $filter_end[2], $filter_end[0]));
						$filters[]='b.order_created < '.(int)$filter_end;
						$pageInfo->filter->filter_end=(int)$filter_end;
						break;
				}
				break;
			default:
				$filter_start=explode('-',$pageInfo->filter->filter_start);
				$noHourDay=explode(' ',$filter_start[2]);
				$filter_start[2]=$noHourDay[0];
				$filter_start= hikashop_getTime(mktime(0, 0, 0, $filter_start[1], $filter_start[2], $filter_start[0]));
				switch($pageInfo->filter->filter_end){
					case '':
						$filters[]='b.order_created > '.hikashop_getTime((int)$filter_start);
						$pageInfo->filter->filter_start=(int)$filter_start;
						break;
					default:
						$filter_end=explode('-',$pageInfo->filter->filter_end);
						$noHourDay=explode(' ',$filter_end[2]);
						$filter_end[2]=$noHourDay[0];
						$filter_end= hikashop_getTime(mktime(23, 59, 59, $filter_end[1], $filter_end[2], $filter_end[0]));
						$filters[]='b.order_created > '.(int)$filter_start. ' AND b.order_created < '.(int)$filter_end;
						$pageInfo->filter->filter_start=(int)$filter_start;
						$pageInfo->filter->filter_end=(int)$filter_end;
						break;
				}
				break;
		}
		switch($pageInfo->filter->filter_payment){
			case '':
				break;
			default:
				$filters[]='b.order_payment_method = '.$database->Quote($pageInfo->filter->filter_payment);
				break;
		}
		$searchMap = array('c.id','c.username','c.name','a.user_email','b.order_user_id','b.order_number','b.order_id','b.order_invoice_number','b.order_invoice_id','b.order_full_price','d.address_firstname','d.address_lastname');
		foreach($fields as $field){
			$searchMap[]='b.'.$field->field_namekey;
		}

		$extrafilters = array();
		JPluginHelper::importPlugin('hikashop');
		if(hikashop_level(2))
			JPluginHelper::getPlugin('system', 'hikashopaffiliate');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onBeforeOrderListing', array($this->paramBase, &$extrafilters, &$pageInfo, &$filters, &$tables, &$searchMap));
		$this->assignRef('extrafilters',$extrafilters);

		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped(JString::strtolower(trim($pageInfo->search)),true).'%\'';
			$filter = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
			$filters[] =  $filter;
		}
		if(!empty($pageInfo->filter->filter_product)){
			$tables['order_product'] = 'INNER JOIN '.hikashop_table('order_product').' AS order_product ON b.order_id = order_product.order_id ';
			$tables['product'] = 'INNER JOIN '.hikashop_table('product').' AS product ON (product.product_id = order_product.product_id OR (product.product_parent_id > 0 AND product.product_parent_id = order_product.product_id))';
			$filters[] = 'product.product_id = '.(int)$pageInfo->filter->filter_product.' OR product.product_parent_id = '.(int)$pageInfo->filter->filter_product;
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
		$query = ' FROM '.hikashop_table('order').' AS b LEFT JOIN '.hikashop_table('address').' AS d ON b.order_billing_address_id=d.address_id LEFT JOIN '.hikashop_table('user').' AS a ON b.order_user_id=a.user_id LEFT JOIN '.hikashop_table('users',false).' AS c ON a.user_cms_id=c.id '.implode(' ', $tables).' '.$filters.$order;
		$database->setQuery('SELECT a.*,b.*,c.*,IFNULL(c.name,CONCAT_WS(\' \',d.address_firstname,d.address_middle_name,d.address_lastname)) AS hikashop_name '.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();

		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'order_id');
		}
		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		if($pageInfo->limit->value == 500) $pageInfo->limit->value = 100;

		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);

		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_order_manage','all'));
		$this->assignRef('manage',$manage);
		$this->toolbar = array(
			array('name' => 'export'),
			array('name' => 'custom', 'icon' => 'copy', 'alt' => JText::_('HIKA_COPY'), 'task' => 'copy', 'display' => $manage),
			array('name' => 'link', 'icon' => 'new', 'alt' => JText::_('HIKA_NEW'),'url' => hikashop_completeLink('order&task=neworder'),'display' => $manage),
			array('name'=> 'editList', 'display' => $manage),
			array('name'=> 'deleteList', 'display' => hikashop_isAllowed($config->get('acl_order_delete','all'))),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);

		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);

		$this->assignRef('fields',$fields);
		$this->assignRef('fieldsClass',$fieldsClass);
		$fieldsClass->handleZoneListing($fields,$rows);
		$pluginClass = hikashop_get('class.plugins');
		$payments = $pluginClass->getMethods('payment');
		$newPayments = array();
		foreach($payments as $payment){
			$newPayments[$payment->payment_id] = $payment;
			$newPayments[$payment->payment_type] = $payment; //backward compat for old order listing views overrides
		}

		$this->assignRef('payments',$newPayments);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
		$category = hikashop_get('type.categorysub');
		$category->type = 'status';
		$this->assignRef('category',$category);
		$payment = hikashop_get('type.payment');
		$this->assignRef('payment',$payment);
		$this->assignRef('popup',$popup);
		$popupHelper = hikashop_get('helper.popup');
		$this->assignRef('popupHelper',$popupHelper);
		$extrafields = array();
		$dispatcher->trigger('onAfterOrderListing', array(&$this->rows, &$extrafields, $pageInfo));
		$this->assignRef('extrafields',$extrafields);

		$this->getPagination();
	}

	function form(){
		$order_id = hikashop_getCID('order_id');
		$fieldsClass = hikashop_get('class.field');
		$fields = null;
		if(!empty($order_id)){
			$class = hikashop_get('class.order');
			$order = $class->loadFullOrder($order_id,true);

			if(hikashop_level(2)){
				$fields['order'] = $fieldsClass->getFields('backend',$order,'order');
				$null = null;
				$fields['entry'] = $fieldsClass->getFields('backend_listing',$null,'entry');
				$fields['item'] = $fieldsClass->getFields('backend_listing',$null,'item');
			}
			$task='edit';

			if(!empty($order->products)) {
				$options = false;
				$products = array();
				foreach($order->products as &$product) {
					if(!empty($product->order_product_option_parent_id)) {
						if(empty($products[$product->order_product_option_parent_id]))
							$products[$product->order_product_option_parent_id] = array();
						if(empty($products[$product->order_product_option_parent_id]['options']))
							$products[$product->order_product_option_parent_id]['options'] = array();
						$products[$product->order_product_option_parent_id]['options'][] = &$product;

						$options = true;
					} else {
						if(empty($products[$product->order_product_id]))
							$products[$product->order_product_id] = array();
						$products[$product->order_product_id]['product'] = &$product;
					}
				}
				unset($product);

				if($options) {
					$order->products = array();
					foreach($products as &$product) {
						if(!empty($product['product']))
							$order->products[] = $product['product'];
						if(!empty($product['options'])) {
							foreach($product['options'] as &$opt) {
								$order->products[] = $opt;
							}
							unset($opt);
						}
					}
					unset($product);
				}
			}
		}
		if(empty($order)){
			$app = JFactory::getApplication();
			$app->redirect(hikashop_completeLink('order&task=listing',false,true));
		}
		$config =& hikashop_config();
		$order_status_for_download = $config->get('order_status_for_download','confirmed,shipped');
		$download_time_limit = $config->get('download_time_limit',0);
		$download_number_limit = $config->get('download_number_limit',0);
		$this->assignRef('order_status_for_download',$order_status_for_download);
		$this->assignRef('download_time_limit',$download_time_limit);
		$this->assignRef('download_number_limit',$download_number_limit);
		$this->assignRef('config',$config);

		$category = hikashop_get('type.categorysub');
		$category->type = 'status';
		$category->load(true);
		$this->assignRef('category',$category);

		$pluginsPayment = hikashop_get('type.plugins');
		$pluginsPayment->type='payment';
		$this->assignRef('payment',$pluginsPayment);

		$pluginsShipping = hikashop_get('type.plugins');
		$pluginsShipping->type='shipping';
		$this->assignRef('shipping',$pluginsShipping);

		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);

		JPluginHelper::importPlugin( 'hikashop' );
		JPluginHelper::importPlugin( 'hikashoppayment' );
		JPluginHelper::importPlugin( 'hikashopshipping' );
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger( 'onHistoryDisplay', array( & $order->history) );

		$this->assignRef('order',$order);
		$this->assignRef('fields',$fields);
		$this->assignRef('fieldsClass',$fieldsClass);

		$user_id = JRequest::getInt('user_id',0);
		if(!empty($user_id)){
			$user_info='&user_id='.$user_id;
			$url = hikashop_completeLink('user&task=edit&user_id='.$user_id);
		}else{
			$user_info='';
			$cancel_url = JRequest::getVar('cancel_redirect');
			if(!empty($cancel_url)){
				$url = base64_decode($cancel_url);
			}else{
				$url = hikashop_completeLink('order');
			}
		}

		if(version_compare(JVERSION,'1.6','<')){
			$url_email = hikashop_completeLink('order&task=mail&order_id='.$order_id,true);
			$url_invoice = hikashop_completeLink('order&task=invoice&type=full&order_id='.$order_id,true);
			$url_shipping = hikashop_completeLink('order&task=invoice&type=shipping&order_id='.$order_id,true);
		} else {
			$url_email = 'index.php?option=com_hikashop&ctrl=order&task=mail&tmpl=component&order_id='.$order_id;
			$url_invoice = 'index.php?option=com_hikashop&ctrl=order&task=invoice&tmpl=component&type=full&order_id='.$order_id;
			$url_shipping = 'index.php?option=com_hikashop&ctrl=order&task=invoice&tmpl=component&type=shipping&order_id='.$order_id;
		}

		$this->toolbar = array(
			array('name' => 'Popup', 'icon' => 'send', 'id' => 'send', 'alt' => JText::_('HIKA_EMAIL'), 'url' => $url_email, 'width' => 720),
			array('name' => 'Popup', 'icon' => 'invoice', 'id' => 'invoice', 'alt' => JText::_('INVOICE'), 'url' => $url_invoice, 'width' => 720),
			array('name' => 'Popup', 'icon' => 'shipping', 'id' => 'shipping', 'alt' => JText::_('SHIPPING_INVOICE'), 'url' => $url_shipping, 'width' => 720),
			array('name' => 'Link', 'icon' => 'cancel', 'alt' => JText::_('HIKA_BACK'), 'url' => $url),
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);
		$popupHelper = hikashop_get('helper.popup');
		$this->assignRef('popup',$popupHelper);
		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&order_id='.$order_id.$user_info);
	}

	function changestatus(){
		$order_id = hikashop_getCID('order_id');
		$new_status = JRequest::getVar('status','');
		if(!empty($order_id)){
			$class = hikashop_get('class.order');
			$order = $class->get($order_id,$new_status);
			$order->order_old_status = $order->order_status;
			$order->order_status = $new_status;
			$class->loadOrderNotification($order);
		}else{
			$order = new stdClass();
		}

		$order->order_status = $new_status;

		$this->assignRef('element',$order);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'hikashop_mail_body';
		$editor->content = $order->mail->body;
		$this->assignRef('editor',$editor);
	}

	function partner(){
		$order_id = hikashop_getCID('order_id');
		if(!empty($order_id)){
			$class = hikashop_get('class.order');
			$order = $class->loadNotification($order_id);
		}else{
			$order = new stdClass();
		}
		$this->assignRef('element',$order);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'hikashop_mail_body';
		$editor->content = '';
		$order->mail->altbody='';
		$this->assignRef('editor',$editor);
		$partners = hikashop_get('type.partners');
		$this->assignRef('partners',$partners);
		$currencyType=hikashop_get('type.currency');
		$this->assignRef('currencyType',$currencyType);
	}

	function discount(){
		$order_id = hikashop_getCID('order_id');
		if(!empty($order_id)){
			$class = hikashop_get('class.order');
			$order = $class->loadNotification($order_id);
		}else{
			$order = new stdClass();
		}
		if(!empty($order->order_tax_info)){
			foreach($order->order_tax_info as $tax){
				if(isset($tax->tax_amount_for_coupon)){
					$order->order_discount_tax_namekey=$tax->tax_namekey;
					break;
				}
			}
		}
		$this->assignRef('element',$order);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'hikashop_mail_body';
		$editor->content = '';
		$order->mail->altbody='';
		$this->assignRef('editor',$editor);
		$ratesType = hikashop_get('type.rates');
		$this->assignRef('ratesType',$ratesType);
	}

	function fields(){
		$order_id = hikashop_getCID('order_id');
		$fieldsClass = hikashop_get('class.field');
		$fields = null;
		if(!empty($order_id)){
			$class = hikashop_get('class.order');
			$order = $class->loadNotification($order_id);
			if(hikashop_level(2)){
				$fields['order'] = $fieldsClass->getFields('backend',$order,'order');
			}
		}else{
			$order = new stdClass();
		}
		$this->assignRef('element',$order);
		$this->assignRef('fields',$fields);
		$this->assignRef('fieldsClass',$fieldsClass);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'hikashop_mail_body';
		$editor->content = '';
		$order->mail->altbody='';
		$this->assignRef('editor',$editor);
	}

	function changeplugin(){
		$order_id = hikashop_getCID('order_id');
		$new_status = JRequest::getVar('status','');
		if(!empty($order_id)){
			$class = hikashop_get('class.order');
			$order = $class->loadNotification($order_id);
		}else{
			$order = new stdClass();
		}

		if(!empty($order->order_tax_info)){
			foreach($order->order_tax_info as $tax){
				if(isset($tax->tax_amount_for_shipping)){
					$order->order_shipping_tax_namekey = $tax->tax_namekey;
				}
				if(isset($tax->tax_amount_for_payment)){
					$order->order_payment_tax_namekey = $tax->tax_namekey;
				}
			}
		}
		$this->assignRef('element',$order);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'hikashop_mail_body';
		$editor->content = '';
		$order->mail->altbody='';
		$this->assignRef('editor',$editor);
		$pluginsPayment = hikashop_get('type.plugins');
		$pluginsPayment->type=JRequest::getWord('type');
		$this->assignRef($pluginsPayment->type,$pluginsPayment);
		$this->assignRef('type',$pluginsPayment->type);
		$full_id = JRequest::getCmd('plugin');
		$this->assignRef('full_id',$full_id);
		$parts = explode('_',$full_id);
		$id = array_pop($parts);
		$this->assignRef('id',$id);
		$method = implode('_',$parts);
		$this->assignRef('method',$method);
		$ratesType = hikashop_get('type.rates');
		$this->assignRef('ratesType',$ratesType);
	}

	function mail(){
		$element = new stdClass();
		$element->order_id = JRequest::getInt('order_id',0);

		if(empty($element->order_id)){
			$user_id = JRequest::getInt('user_id',0);
			$userClass = hikashop_get('class.user');
			$element->customer = $userClass->get($user_id);
			$mailClass = hikashop_get('class.mail');
			$element->mail = new stdClass();
			$element->mail->body='';
			$element->mail->altbody='';
			$element->mail->html=1;
			$mailClass->loadInfos($element->mail, 'user_notification');
			$element->mail->dst_email =& $element->customer->user_email;
			if(!empty($element->customer->name)){
				$element->mail->dst_name =& $element->customer->name;
			}else{
				$element->mail->dst_name = '';
			}
		}else{
			$orderClass = hikashop_get('class.order');
			$orderClass->loadMail($element);
		}
		$editor = hikashop_get('helper.editor');
		$editor->name = 'hikashop_mail_body';
		$editor->content = $element->mail->body;
		$this->assignRef('editor',$editor);
		$this->assignRef('element',$element);
	}

	function export() {
		$ids = JRequest::getVar('cid', array(), '', 'array');
		$database = JFactory::getDBO();
		$fieldsClass = hikashop_get('class.field');
		$fields = $fieldsClass->getData('all','order',false);
		$filters = array();

		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();

		if(empty($ids)) {
			$filters['order_type'] = 'hk_order.order_type = \'sale\'';

			$app = JFactory::getApplication();
			$search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
			$filter_status = $app->getUserStateFromRequest( $this->paramBase.".filter_status",'filter_status','','array');
			$filter_payment = $app->getUserStateFromRequest( $this->paramBase.".filter_payment",'filter_payment','','string');
			$filter_partner = $app->getUserStateFromRequest( $this->paramBase.".filter_partner",'filter_partner','','int');
			$filter_end = $app->getUserStateFromRequest( $this->paramBase.".filter_end",'filter_end','','string');
			$filter_start = $app->getUserStateFromRequest( $this->paramBase.".filter_start",'filter_start','','string');

			switch($filter_start) {
				case '':
					switch($filter_end) {
						case '':
							break;
						default:
							$filter_end = explode('-', $filter_end);
							$noHourDay = explode(' ', $filter_end[2]);
							$filter_end[2] = $noHourDay[0];
							$filter_end = hikashop_getTime(mktime(23, 59, 59, $filter_end[1], $filter_end[2], $filter_end[0]));
							$filters['order_created'] = 'hk_order.order_created < '.(int)$filter_end;
							break;
					}
					break;
				default:
					$filter_start = explode('-', $filter_start);
					$noHourDay = explode(' ', $filter_start[2]);
					$filter_start[2] = $noHourDay[0];
					$filter_start = hikashop_getTime(mktime(0, 0, 0, $filter_start[1], $filter_start[2], $filter_start[0]));
					switch($filter_end) {
						case '':
							$filters['order_created'] = 'hk_order.order_created > '.(int)$filter_start;
							break;
						default:
							$filter_end = explode('-',$filter_end);
							$noHourDay = explode(' ',$filter_end[2]);
							$filter_end[2] = $noHourDay[0];
							$filter_end = hikashop_getTime(mktime(23, 59, 59, $filter_end[1], $filter_end[2], $filter_end[0]));
							$filters['order_created'] = 'hk_order.order_created > '.(int)$filter_start. ' AND hk_order.order_created < '.(int)$filter_end;
							break;
					}
					break;
			}

			if(!empty($filter_partner)) {
				$filters['order_partner_id'] = ($filter_partner == 1) ? 'hk_order.order_partner_id != 0' : 'hk_order.order_partner_id = 0';
			}

			switch($filter_status) {
				case '':
					break;
				default:
					if(!is_array($filter_status) || !count($filter_status) || in_array('', $filter_status)) {
						break;
					}
					$statuses = array();
					foreach($filter_status as $status) {
						$statuses[] = $database->Quote($status);
					}
					$filters['order_status'] = 'hk_order.order_status IN (' . implode(',', $statuses) . ')';
					break;
			}
			switch($filter_payment) {
				case '':
					break;
				default:
					$filters['order_payment_method'] = 'hk_order.order_payment_method = '.$database->Quote($filter_payment);
					break;
			}

			$searchMap = array(
				'j_user.id',
				'j_user.username',
				'j_user.name',
				'hk_user.user_email',
				'hk_order.order_user_id',
				'hk_order.order_id',
				'hk_order.order_full_price'
			);
			foreach($fields as $field) {
				$searchMap[] = 'hk_order.'.$field->field_namekey;
			}
			if(!empty($search)) {
				$searchVal = '\'%'.hikashop_getEscaped(JString::strtolower(trim($search)),true).'%\'';
				$id = hikashop_decode($pageInfo->search);
				$filter = implode(' LIKE '.$searchVal.' OR ', $searchMap).' LIKE '.$searchVal;
				if(!empty($id)) {
					$filter .= ' OR hk_order.order_id LIKE \'%'.hikashop_getEscaped($id, true).'%\'';
				}
				$filters['search'] = $filter;
			}

			$dispatcher->trigger('onBeforeOrderExportQuery', array(&$filters, $this->paramBase));
		} else {
			JArrayHelper::toInteger($ids,0);
			$filters['order_id'] = 'hk_order.order_id IN ('.implode(',', $ids).')';
		}

		$filters = '(' . implode(') AND (', $filters) . ')';

		$query = ' FROM ' . hikashop_table('order').' AS hk_order '.
				' LEFT JOIN '.hikashop_table('user').' AS hk_user ON hk_order.order_user_id = hk_user.user_id '.
				' LEFT JOIN '.hikashop_table('users',false).' AS j_user ON hk_user.user_cms_id = j_user.id '.
				' WHERE '.$filters;
		$database->setQuery('SELECT hk_user.*, hk_order.*, j_user.*' . $query);

		$rows = $database->loadObjectList('order_id');
		if(!empty($rows)) {
			$addressIds = array();
			foreach($rows as $k => $row) {
				$rows[$k]->products = array();
				$addressIds[$row->order_shipping_address_id] = $row->order_shipping_address_id;
				$addressIds[$row->order_billing_address_id] = $row->order_billing_address_id;
			}
			if(!empty($addressIds)) {
				$database->setQuery('SELECT * FROM '.hikashop_table('address').' WHERE address_id IN ('.implode(',',$addressIds).')');
				$addresses = $database->loadObjectList('address_id');
				if(!empty($addresses)) {
					$zoneNamekeys = array();
					foreach($addresses as $address) {
						$zoneNamekeys[$address->address_country] = $database->Quote($address->address_country);
						$zoneNamekeys[$address->address_state] = $database->Quote($address->address_state);
					}
					if(!empty($zoneNamekeys)) {
						$database->setQuery('SELECT zone_namekey,zone_name FROM ' . hikashop_table('zone') . ' WHERE zone_namekey IN (' . implode(',', $zoneNamekeys) . ')');
						$zones = $database->loadObjectList('zone_namekey');
						if(!empty($zones)) {
							foreach($addresses as $i => $address) {
								if(!empty($zones[$address->address_country])) {
									$addresses[$i]->address_country = $zones[$address->address_country]->zone_name;
								}
								if(!empty($zones[$address->address_state])) {
									$addresses[$i]->address_state = $zones[$address->address_state]->zone_name;
								}
							}
						}
					}
					$fields = array_keys(get_object_vars(reset($addresses)));
					foreach($rows as $k => $row) {
						if(!empty($addresses[$row->order_shipping_address_id])) {
							foreach($addresses[$row->order_shipping_address_id] as $key => $val) {
								$key = 'shipping_'.$key;
								$rows[$k]->$key = $val;
							}
						} else {
							foreach($fields as $field) {
								$key = 'shipping_'.$field;
								$rows[$k]->$key = '';
							}
						}
						if(!empty($addresses[$row->order_billing_address_id])) {
							foreach($addresses[$row->order_billing_address_id] as $key => $val) {
								$key = 'billing_'.$key;
								$rows[$k]->$key = $val;
							}
						} else {
							foreach($fields as $field) {
								$key = 'billing_'.$field;
								$rows[$k]->$key = '';
							}
						}
					}
				}
			}
			$orderIds = array_keys($rows);
			$database->setQuery('SELECT * FROM '.hikashop_table('order_product').' WHERE order_id IN ('.implode(',',$orderIds).')');
			$products = $database->loadObjectList();

			foreach($products as $product) {
				$order =& $rows[$product->order_id];
				$order->products[] = $product;
				if(!isset($order->order_full_tax)) {
					$order->order_full_tax=0;
				}
				$order->order_full_tax += round($product->order_product_quantity * $product->order_product_tax, 2);
			}
			foreach($rows as $k => $row){
				$rows[$k]->order_full_tax += $row->order_shipping_tax + $row->order_payment_tax - $row->order_discount_tax;
			}
		}
		$dispatcher->trigger('onBeforeOrderExport', array(&$rows, &$this));
		$this->assignRef('orders', $rows);
	}

	function invoice() {
		$order_id = hikashop_getCID('order_id');
		$fieldsClass = hikashop_get('class.field');
		$fields = array();
		if(!empty($order_id)){
			$class = hikashop_get('class.order');
			$order = $class->loadFullOrder($order_id);
			$null = null;
			$fields['item'] = $fieldsClass->getFields('backend_listing',$null,'item');
			$task='edit';
		}else{
			$order = new stdClass();
			$task='add';
		}
		$config =& hikashop_config();
		$store = str_replace(array("\r\n","\n","\r"),array('<br/>','<br/>','<br/>'),$config->get('store_address',''));
		if(JText::_($store)!=$store){
			$store = JText::_($store);
		}
		$this->assignRef('store_address',$store);
		$this->assignRef('element',$order);
		$this->assignRef('order',$order);
		$this->assignRef('fields',$fields);

		if(!empty($order->order_payment_id)){
			$pluginsPayment = hikashop_get('type.plugins');
			$pluginsPayment->type='payment';
			$this->assignRef('payment',$pluginsPayment);
		}
		if(!empty($order->order_shipping_id)){
			$pluginsShipping = hikashop_get('type.plugins');
			$pluginsShipping->type='shipping';
			$this->assignRef('shipping',$pluginsShipping);

			if(empty($order->order_shipping_method)) {
				$shippingClass = hikashop_get('class.shipping');
				$this->assignRef('shippingClass', $shippingClass);

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

		$type = JRequest::getWord('type');
		$this->assignRef('invoice_type',$type);
		$nobutton = true;
		$this->assignRef('nobutton',$nobutton);
		$display_type = 'frontcomp';
		$this->assignRef('display_type',$display_type);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
		$config =& hikashop_config();
		$this->assignRef('config',$config);
		$this->assignRef('fieldsClass',$fieldsClass);
	}

	function product(){
		$product_id = hikashop_getCID('product_id');
		$orderClass = hikashop_get('class.order');
		if(!empty($product_id)){
			$class = hikashop_get('class.order_product');
			$product = $class->get($product_id);
		}else{
			$product = new stdClass();
			$product->order_id = JRequest::getInt('order_id');
			$product->mail = new stdClass();
			$product->mail->body = '';
		}

		$orderClass->loadMail($product);

		if(!empty($product->order_product_tax_info)){
			$tax = reset($product->order_product_tax_info);
			$product->tax_namekey = $tax->tax_namekey;
		}

		$this->assignRef('element',$product);

		$editor = hikashop_get('helper.editor');
		$editor->name = 'hikashop_mail_body';
		$editor->content = $product->mail->body;
		$this->assignRef('editor',$editor);
		$extraFields=array();
		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass',$fieldsClass);
		$null=null;
		$extraFields['item'] = $fieldsClass->getFields('backend',$null,'item','user&task=state');
		$this->assignRef('extraFields',$extraFields);
		$ratesType = hikashop_get('type.rates');
		$this->assignRef('ratesType',$ratesType);
	}
	function user(){
		$element = new stdClass();
		$element->order_id = JRequest::getInt('order_id');
		$element->mail = new stdClass();
		$element->mail->body = '';
		$orderClass = hikashop_get('class.order');
		$orderClass->loadMail($element);
		$this->assignRef('element',$element);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'hikashop_mail_body';
		$editor->content = $element->mail->body;
		$this->assignRef('editor',$editor);
	}

	function product_delete(){
		$product_id = hikashop_getCID('product_id');
		$orderClass = hikashop_get('class.order');
		if(!empty($product_id)){
			$class = hikashop_get('class.order_product');
			$product = $class->get($product_id);
			$orderClass->loadMail($product);
			$this->assignRef('element',$product);
			$editor = hikashop_get('helper.editor');
			$editor->name = 'hikashop_mail_body';
			$editor->content = $product->mail->body;
			$this->assignRef('editor',$editor);
		}
	}

	function address(){
		$address_id = hikashop_getCID('address_id');
		$address_type = JRequest::getCmd('type');
		$fieldsClass = hikashop_get('class.field');
		$orderClass = hikashop_get('class.order');
		$order = new stdClass();
		$order->order_id = JRequest::getInt('order_id');
		$addressClass=hikashop_get('class.address');
		$name = $address_type.'_address';
		if(!empty($address_id)){
			$order->$name=$addressClass->get($address_id);
		}
		$fieldClass = hikashop_get('class.field');
		$order->fields = $fieldClass->getData('backend','address');
		$orderClass->loadMail($order);
		$name = $address_type.'_address';
		$fieldsClass->prepareFields($order->fields,$order->$name,'address','field&task=state');

		$this->assignRef('fieldsClass',$fieldsClass);

		$this->assignRef('element',$order);
		$this->assignRef('type',$address_type);
		$this->assignRef('id',$address_id);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'hikashop_mail_body';
		$editor->content = $order->mail->body;
		$this->assignRef('editor',$editor);
	}
	function product_select(){
		$app = JFactory::getApplication();
		$config =& hikashop_config();
		$this->assignRef('config',$config);
		$this->paramBase.="_product_select";
		$element = new stdClass();
		$element->order_id = JRequest::getInt('order_id');
		$this->assignRef('element',$element);
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.ordering','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'asc',	'word' );
		if(JRequest::getVar('search')!=$app->getUserState($this->paramBase.".search")){
			$app->setUserState( $this->paramBase.'.limitstart',0);
			$pageInfo->limit->start = 0;
		}else{
			$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		}
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 500;
		$selectedType = $app->getUserStateFromRequest( $this->paramBase.".filter_type",'filter_type',0,'int');
		$pageInfo->filter->filter_id = $app->getUserStateFromRequest( $this->paramBase.".filter_id",'filter_id',0,'string');
		$pageInfo->filter->filter_product_type = $app->getUserStateFromRequest( $this->paramBase.".filter_product_type",'filter_product_type','main','word');
		$database = JFactory::getDBO();
		$filters = array();
		$searchMap = array('b.product_name','b.product_description','b.product_id','b.product_code');
		if(empty($pageInfo->filter->filter_id)|| !is_numeric($pageInfo->filter->filter_id)){
			$pageInfo->filter->filter_id='product';
			$class = hikashop_get('class.category');
			$class->getMainElement($pageInfo->filter->filter_id);
		}
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$order = '';
		if(!$selectedType){
			$filters[]='a.category_id='.(int)$pageInfo->filter->filter_id;
			$select='SELECT a.ordering, b.*';
		}else{
			$categoryClass = hikashop_get('class.category');
			$categoryClass->parentObject =& $this;
			$children = $categoryClass->getChildren((int)$pageInfo->filter->filter_id,true,array(),'',0,0);
			$filter = 'a.category_id IN (';
			foreach($children as $child){
				$filter .= $child->category_id.',';
			}
			$filters[]=$filter.(int)$pageInfo->filter->filter_id.')';
			$select='SELECT DISTINCT b.*';
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

		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$childClass = hikashop_get('type.childdisplay');
		$childDisplay = $childClass->display('filter_type',$selectedType,false);
		$this->assignRef('childDisplay',$childDisplay);
		$productClass = hikashop_get('type.product');
		$this->assignRef('productType',$productClass);
		$breadcrumbClass = hikashop_get('type.breadcrumb');
		$breadCrumb = $breadcrumbClass->display('filter_id',$pageInfo->filter->filter_id,'product');
		$this->assignRef('breadCrumb',$breadCrumb);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->getPagination();
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
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
	}
	function _loadPrices(&$rows){
		$ids = array();
		foreach($rows as $row){
			$ids[]=(int)$row->product_id;
		}
		$query = 'SELECT * FROM '.hikashop_table('price').' WHERE price_product_id IN ('.implode(',',$ids).')';
		$database = JFactory::getDBO();
		$database->setQuery($query);
		$prices = $database->loadObjectList();
		if(!empty($prices)){
			foreach($prices as $price){
				foreach($rows as $k => $row){
					if($price->price_product_id==$row->product_id){
						if(!isset($row->prices)) $row->prices=array();
						$rows[$k]->prices[]=$price;
						break;
					}
				}
			}
		}
	}

	public function show($tpl = null, $toolbar = true) {
		$this->form();

		$edit = JRequest::getVar('task','') == 'edit';
		$this->assignRef('edit', $edit);
		$order_status_type = hikashop_get('type.order_status');
		$this->assignRef('order_status', $order_status_type);
		$shippingClass = hikashop_get('class.shipping');
		$this->assignRef('shippingClass',$shippingClass);
		$paymentClass = hikashop_get('class.payment');
		$this->assignRef('paymentClass',$paymentClass);
	}

	public function show_general($tpl = null) {
		$this->show($tpl, false);
	}

	public function show_history($tpl = null) {
		$this->show($tpl, false);
	}

	public function edit_additional($tpl = null) {
		$this->show($tpl, false);

		$ratesType = hikashop_get('type.rates');
		$this->assignRef('ratesType',$ratesType);

		$pluginsPayment = hikashop_get('type.plugins');
		$pluginsPayment->type = 'payment';
		$this->assignRef('paymentPlugins', $pluginsPayment);

		$pluginsShipping = hikashop_get('type.plugins');
		$pluginsShipping->type = 'shipping';
		$this->assignRef('shippingPlugins', $pluginsShipping);

		if(!empty($this->order->order_tax_info)){
			foreach($this->order->order_tax_info as $tax){
				if(isset($tax->tax_amount_for_shipping)){
					$this->order->order_shipping_tax_namekey = $tax->tax_namekey;
				}
				if(isset($tax->tax_amount_for_coupon)){
					$this->order->order_discount_tax_namekey = $tax->tax_namekey;
				}
				if(isset($tax->tax_amount_for_payment)){
					$this->order->order_payment_tax_namekey = $tax->tax_namekey;
				}
			}
		}
	}

	public function show_additional($tpl = null) {
		$task = JRequest::getCmd('task', '');
		if($task == 'save') {
			$html = '<html><body><script type="text/javascript">'."\r\n".
				'window.parent.hikashop.submitFct();'."\r\n".
				'</script></body></html>';
			die($html);
		}
		$shippingClass = hikashop_get('class.shipping');
		$this->assignRef('shippingClass',$shippingClass);
		$paymentClass = hikashop_get('class.payment');
		$this->assignRef('paymentClass',$paymentClass);
		$this->show($tpl, false);
	}

	public function show_shipping_address($tpl = null) {
		$address_type = 'shipping';
		$this->assignRef('type', $address_type);
		$this->show($tpl, false);

		if($this->edit) {
			if(!empty($this->order->order_shipping_address_id)) {
				$addressClass = hikashop_get('class.address');
				$this->order->shipping_address = $addressClass->get($this->order->order_shipping_address_id);
			}
			$this->fieldsClass->prepareFields($this->order->fields, $this->order->shipping_address, 'address', 'user&task=state');
		}

		$this->setLayout('show_address');
	}

	public function show_billing_address($tpl = null) {
		$address_type = 'billing';
		$this->assignRef('type', $address_type);
		$this->show($tpl, false);

		if($this->edit) {
			if(!empty($this->order->order_billing_address_id)) {
				$addressClass = hikashop_get('class.address');
				$this->order->billing_address = $addressClass->get($this->order->order_billing_address_id);
			}
			$this->fieldsClass->prepareFields($this->order->fields, $this->order->billing_address, 'address', 'user&task=state');
		}

		$this->setLayout('show_address');
	}

	public function show_products($tpl = null) {
		$task = JRequest::getCmd('task', '');
		if($task == 'save') {
			$html = '<html><body><script type="text/javascript">'."\r\n".
				'window.parent.hikashop.submitFct();'."\r\n".
				'</script></body></html>';
			die($html);
		}
		$this->show($tpl, false);
	}

	public function edit_products($tpl = null) {
		$config = hikashop_config();
		$this->assignRef('config', $config);
		$productClass = hikashop_get('class.product');
		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass', $fieldsClass);

		$order_id = JRequest::getInt('order_id');
		$order_product_id = JRequest::getInt('order_product_id', 0);

		$orderClass = hikashop_get('class.order');
		$order = $orderClass->loadFullOrder($order_id);
		$originalProduct = new stdClass();

		if(!empty($order_product_id)){
			$orderProductClass = hikashop_get('class.order_product');
			$orderProduct = $orderProductClass->get($order_product_id);
			if(empty($orderProduct) || $orderProduct->order_id != $order_id) {
				$orderProduct = new stdClass();
				$orderProduct->order_id = $order_id;
			}
			if(!empty($orderProduct->product_id)) {
				$originalProduct = $productClass->get($orderProduct->product_id);
			}
		}else{
			$orderProduct = new stdClass();
			$orderProduct->order_id = $order_id;
			$orderProduct->order_product_quantity = 1;

			$product_id = JRequest::getVar('cid', array(), '', 'array');

			if(!empty($product_id)) {
				$database = JFactory::getDBO();
				$query = 'SELECT product_parent_id FROM '.hikashop_table('product').' WHERE product_id = '. (int)$product_id[0];
				$database->setQuery($query);
				$product_parent_id = $database->loadResult();
				$isVariant = false;
				if($product_parent_id != 0){
					$product_id[1] = $product_parent_id;
					$isVariant = true;
				}

				if($productClass->getProducts($product_id)) {
					$products = $productClass->products;
					$allproducts = $productClass->all_products;
					if(!$isVariant)
						$product = $products[ (int)$product_id[0] ];
					else
						$product = $allproducts[ (int)$product_id[1] ]->variants[ (int)$product_id[0] ];
					$product->options = array();

					$originalProduct = $product;

					$orderProduct->product_id = $product->product_id;
					if($isVariant && empty($product->product_name)){
						$product->product_name = $allproducts[ (int)$product_id[1] ]->product_name;
					}

					$orderProduct->order_product_name = strip_tags($product->product_name);

					$orderProduct->order_product_code = $product->product_code;


					$currencyClass = hikashop_get('class.currency');
					$main_currency = (int)$config->get('main_currency',1);
					$discount_before_tax = (int)$config->get('discount_before_tax',0);
					$currency_id = $order->order_currency_id;

					if($config->get('tax_zone_type', 'shipping') == 'billing'){
						$orderClass->loadAddress($order->order_billing_address_id,$order,'billing','object','backend');
						if(!empty($order->billing_address->address_state->zone_id)){
							$zone_id = $order->billing_address->address_state->zone_id;
						}elseif(!empty($order->billing_address->address_country->zone_id)){
							$zone_id = $order->billing_address->address_country->zone_id;
						}else{
							$zone_id = hikashop_getZone('billing');
						}
					} else {
						$orderClass->loadAddress($order->order_shipping_address_id,$order,'shipping','object','backend');
						if(!empty($order->shipping_address->address_state->zone_id)){
							$zone_id = $order->shipping_address->address_state->zone_id;
						}elseif(!empty($order->shipping_address->address_country->zone_id)){
							$zone_id = $order->shipping_address->address_country->zone_id;
						}else{
							$zone_id = hikashop_getZone('shipping');
						}
					}



					$rows = array($product);
					if($isVariant){
						$rows[]=$allproducts[ (int)$product_id[1] ];
					}

					$currencyClass->getPrices($rows, $product_id, $currency_id, $main_currency, $zone_id, $discount_before_tax,(int)$order->order_user_id);
					if(empty($rows[0]->prices) && !empty($rows[1]->prices)) {
						$rows[0]->prices = $rows[1]->prices;
					}
					$currencyClass->pricesSelection($rows[0]->prices, 0);
					if(!empty($rows[0]->prices)) {
						foreach($rows[0]->prices as $price) {
							$orderProduct->order_product_price = $price->price_value;
							$orderProduct->order_product_tax = (@$price->price_value_with_tax - @$price->price_value);
							$orderProduct->order_product_tax_info = @$price->taxes;
						}
					}
				}
			}
		}
		$this->assignRef('orderProduct', $orderProduct);
		$this->assignRef('originalProduct', $originalProduct);

		$ratesType = hikashop_get('type.rates');
		$this->assignRef('ratesType',$ratesType);

		if(hikashop_level(2)) {
			$null = null;
			$this->fields['item'] = $this->fieldsClass->getFields('backend',$null,'item','user&task=state');
		}
	}

	public function customer_set() {
		$users = JRequest::getVar('cid', array(), '', 'array');
		$closePopup = JRequest::getInt('finalstep', 0);

		if($closePopup) {
			$formData = JRequest::getVar('data', array(), '', 'array');
			$users = array( (int)$formData['order']['order_user_id'] );
		}
		$rows = array();
		$data = '';
		$singleSelection = true; //JRequest::getVar('single', false);
		$order_id = JRequest::getInt('order_id', 0);

		$elemStruct = array(
			'user_email',
			'user_cms_id',
			'name',
			'username',
			'email'
		);

		$set_address = JRequest::getInt('set_user_address', 0);

		if(!empty($users)) {
			JArrayHelper::toInteger($users);
			$db = JFactory::getDBO();
			$query = 'SELECT a.*, b.* FROM '.hikashop_table('user').' AS a LEFT JOIN '.hikashop_table('users', false).' AS b ON a.user_cms_id = b.id WHERE a.user_id IN ('.implode(',',$users).')';
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			if(!empty($rows)) {
				$data = array();
				foreach($rows as $v) {
					$d = '{id:'.$v->user_id;
					foreach($elemStruct as $s) {
						if($s == 'id')
							continue;
						$d .= ','.$s.':\''. str_replace('"','\'',$v->$s).'\'';
					}
					if($set_address && $singleSelection)
						$d .= ',updates:[\'billing\',\'shipping\',\'history\']';
					$data[] = $d.'}';
				}
				if(!$singleSelection)
					$data = '['.implode(',',$data).']';
				else {
					$data = $data[0];
					$rows = $rows[0];
				}
			}
		}
		$this->assignRef('rows', $rows);
		$this->assignRef('data', $data);
		$this->assignRef('singleSelection', $singleSelection);
		$this->assignRef('order_id', $order_id);

		if($closePopup) {
			hikashop_loadJslib('mootools');
			$js = 'window.hikashop.ready( function(){window.parent.hikashop.submitBox('.$data.');});';
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration($js);
		}
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
