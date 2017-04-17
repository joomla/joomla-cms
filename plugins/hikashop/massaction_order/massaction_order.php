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
class plgHikashopMassaction_order extends JPlugin
{
	var $message = '';

	function onMassactionTableLoad(&$externalValues){
		$obj = new stdClass();
		$obj->table ='order';
		$obj->value ='order';
		$obj->text =JText::_('HIKASHOP_ORDER');
		$externalValues[] = $obj;

	}

	function __construct(&$subject, $config){
		parent::__construct($subject, $config);
		$this->massaction = hikashop_get('class.massaction');
		$this->massaction->datecolumns = array( 'order_created',
												'order_modified',
												'order_invoice_created'
											);
		$this->order = hikashop_get('class.order');
	}

	function onProcessOrderMassFilterlimit(&$elements, &$query,$filter,$num){
		$query->start = (int)$filter['start'];
		$query->value = (int)$filter['value'];
	}

	function onProcessOrderMassFilterordering(&$elements, &$query,$filter,$num){
		if(!empty($filter['value'])){
			if(isset($query->ordering['default']))
				unset($query->ordering['default']);
			$query->ordering[] = $filter['value'];
		}
	}

	function onProcessOrderMassFilterdirection(&$elements, &$query,$filter,$num){
		if(empty($query->ordering))
			$query->ordering['default'] = 'order_id';
		$query->direction = $filter['value'];
	}

	function onProcessOrderMassFilterorderColumn(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(!isset($this->massaction))$this->massaction = hikashop_get('class.massaction');

		if(count($elements)){
			foreach($elements as $k => $element){
				$in = $this->massaction->checkInElement($element, $filter);
				if(!$in) unset($elements[$k]);
			}
		}else{
			$db = JFactory::getDBO();
			if(!empty($filter['value']) || (empty($filter['value']) && in_array($filter['operator'],array('IS NULL','IS NOT NULL')))){
				if($filter['type'] == 'order_currency_id' && !is_int($filter['value'])){
					$nquery = 'SELECT currency_id FROM '.hikashop_table('currency').' WHERE currency_symbol = '.$db->quote($filter['value']).' OR currency_code = '.$db->quote($filter['value']).' OR currency_name = '.$db->quote($filter['value']);
					$db->setQuery($nquery);
					$result = $db->loadResult();

					$query->where[] = 'hk_order.'.$filter['type'].' = '.(int)$result;
				}else{
					$query->where[] = $this->massaction->getRequest($filter,'hk_order');
				}
			}
		}
	}
	function onCountOrderMassFilterorderColumn(&$query,$filter,$num){
		$elements = array();
		$this->onProcessOrderMassFilterorderColumn($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_order.order_id'));
	}

	function onProcessOrderMassFilterorderStatus(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(count($elements)){
			foreach($elements as $k => $element){
				if($element->order_status != $filter['type']) unset($elements[$k]);
			}
		}else{
			$db = JFactory::getDBO();
			$query->where[] = 'hk_order.order_status = '.$db->quote($filter['type']);
		 }
	}
	function onCountOrderMassFilterorderStatus(&$query,$filter,$num){
		$elements = array();
		$this->onProcessOrderMassFilterorderStatus($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_order.order_id'));
	}

	function onProcessOrderMassFilterorder_productColumn(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(!isset($this->massaction))$this->massaction = hikashop_get('class.massaction');
		if(count($elements)){
			foreach($elements as $k => $element){
				$orderClass = hikashop_get('class.order');
				$orderClass->loadProducts($element);
				if(empty($element->products)){unset($elements[$k]); continue;}
				$del = true;
				foreach($element->products as $product){
					$in = $this->massaction->checkInElement($product, $filter);
					if($in){$del = false;}
				}
				if($del){
					unset($elements[$k]);
				}
				else{
					unset($elements[$k]->products);
					unset($elements[$k]->additional);
				}
			}
		}else{
			$db = JFactory::getDBO();
			if(!empty($filter['value']) || (empty($filter['value']) && in_array($filter['operator'],array('IS NULL','IS NOT NULL')))){
				$query->leftjoin['order_product'] = hikashop_table('order_product').' as hk_order_product ON hk_order_product.order_id = hk_order.order_id';
				$query->where[] = $this->massaction->getRequest($filter,'hk_order_product');
			}
		 }
	}
	function onCountOrderMassFilterorder_productColumn(&$query,$filter,$num){
		$elements = array();
		$this->onProcessOrderMassFilterorder_productColumn($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_order.order_id'));
	}

	function onProcessOrderMassFilteruserColumn(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(!isset($this->massaction))$this->massaction = hikashop_get('class.massaction');
		if(count($elements)){
			foreach($elements as $k => $element){
				$userClass = hikashop_get('class.user');
				$result = $userClass->get($element->order_user_id);

				$filter['type'] = str_replace('hk_user.','',$filter['type']);
				$filter['type'] = str_replace('joomla_user.','',$filter['type']);

				$in = $this->massaction->checkInElement($result, $filter);
				if(!$in) unset($elements[$k]);
			}
		}else{
			$db = JFactory::getDBO();
			if(!empty($filter['value']) || (empty($filter['value']) && in_array($filter['operator'],array('IS NULL','IS NOT NULL')))){
				$query->leftjoin['user'] = hikashop_table('user').' as hk_user ON hk_order.order_user_id = hk_user.user_id';
				$query->leftjoin['joomla_user'] = hikashop_table('users',false).' as joomla_user ON joomla_user.id = hk_user.user_cms_id';
				$query->where[] = $this->massaction->getRequest($filter);
			}
		}
	}
	function onCountOrderMassFilteruserColumn(&$query,$filter,$num){
		$elements = array();
		$this->onProcessOrderMassFilteruserColumn($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_order.order_id'));
	}

	function onProcessOrderMassFilteraddressColumn(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(!isset($this->massaction))$this->massaction = hikashop_get('class.massaction');
		$db = JFactory::getDBO();
		if(count($elements)){
			foreach($elements as $k => $element){
				if($filter['address'] == 'both'){ $where = 'address_id = '.(int)$element->order_billing_address_id.' OR address_id = '.(int)$element->order_shipping_address_id;}
				elseif($filter['address'] == 'bill'){ $where = 'address_id = '.(int)$element->order_billing_address_id;}
				elseif($filter['address'] == 'ship'){ $where = 'address_id = '.(int)$element->order_shipping_address_id;}
				$db->setQuery('SELECT * FROM '.hikashop_table('address').' WHERE '.$where.' GROUP BY address_id');
				$results = $db->loadObjectList();

				$del = true;
				foreach($results as $result){
					$in = $this->massaction->checkInElement($result, $filter);
					if($in) $del = false;
				}
				if($del) unset($elements[$k]);
			}
		}else{
			if(!empty($filter['value']) || (empty($filter['value']) && in_array($filter['operator'],array('IS NULL','IS NOT NULL')))){
				if($filter['address'] == 'both'){
					$query->leftjoin[] = hikashop_table('address'). ' as hk_address ON hk_address.address_id = hk_order.order_billing_address_id OR hk_address.address_id = hk_order.order_shipping_address_id';
				}else if($filter['address'] == 'ship'){
					$query->leftjoin[] = hikashop_table('address'). ' as hk_address ON hk_address.address_id = hk_order.order_shipping_address_id';
				}else if($filter['address'] == 'bill'){
					$query->leftjoin[] = hikashop_table('address'). ' as hk_address ON hk_address.address_id = hk_order.order_billing_address_id';
				}
				$query->where[] = $this->massaction->getRequest($filter,'hk_address');
			}
		 }
	}
	function onCountOrderMassFilteraddressColumn(&$query,$filter,$num){
		$elements = array();
		$this->onProcessOrderMassFilteraddressColumn($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_order.order_id'));
	}
	function onProcessOrderMassFilteraccessLevel(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(count($elements)){
			foreach($elements as $k => $element){
				if($element->$filter['type']!=$filter['value']) unset($elements[$k]);
			}
		}else{
			$db = JFactory::getDBO();
			$operator = (empty($filter['type']) || $filter['type'] == 'IN') ? ' = ' : ' != ';
			$query->leftjoin['user'] = hikashop_table('user'). ' as hk_user ON hk_user.user_id = hk_order.order_user_id';
			$query->leftjoin['joomla_user'] = hikashop_table('users',false). ' as joomla_user ON joomla_user.id = hk_user.user_cms_id';
			if(!HIKASHOP_J16){
				$query->leftjoin['core_acl_aro_groups'] = hikashop_table('core_acl_aro_groups',false). ' as core_acl_aro_groups ON core_acl_aro_groups.value = joomla_user.usertype';
				$query->where[] = 'core_acl_aro_groups.id'.' '.$operator.' '.(int)$filter['group'];
			}else{
				$query->leftjoin['user_usergroup_map'] = hikashop_table('user_usergroup_map',false). ' as user_usergroup_map ON user_usergroup_map.user_id = joomla_user.id';
				$query->where[] = 'user_usergroup_map.group_id'.' '.$operator.' '.(int)$filter['group'];
			}
		}
	}
	function onCountOrderMassFilteraccessLevel(&$query,$filter,$num){
		$elements = array();
		$this->onProcessOrderMassFilteraccessLevel($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_order.order_id'));
	}

	function onProcessOrderMassFiltertotalPurchase(&$elements,&$query,$filter,$num){
		if(empty($filter['type']) || $filter['type']=='all') return;
		if(!isset($this->massaction))$this->massaction = hikashop_get('class.massaction');
		if(count($elements)){
			foreach($elements as $k => $element){
				if($filter['type'] == 'orderAmount'){
					$filter['type'] = 'order_full_price';
					$result = $element;
				}else{
					$userClass = hikashop_get('class.user');
					$result = $userClass->get($element->order_user_id);
					$filter['type'] = str_replace('hk_user.','',$filter['type']);
					$filter['type'] = str_replace('joomla_user.','',$filter['type']);
				}
				$in = $this->massaction->checkInElement($result, $filter);
				if(!$in) unset($elements[$k]);
			}
		}else{
			$db = JFactory::getDBO();
			if(!empty($filter['value']) || (empty($filter['value']) && in_array($filter['operator'],array('IS NULL','IS NOT NULL')))){
				$query->leftjoin['user'] = hikashop_table('user').' as hk_user ON hk_order.order_user_id = hk_user.user_id';
				$query->leftjoin['joomla_user'] = hikashop_table('users',false).' as joomla_user ON joomla_user.id = hk_user.user_cms_id';

				if($filter['type'] == 'orderAmount'){
					$cQuery = 'SELECT order_user_id FROM '.hikashop_table('order').' WHERE 1 GROUP BY order_user_id HAVING sum(order_full_price) '.$filter['operator'].' '.$filter['value'];
				}elseif($filter['type'] == 'productTotal'){
					$cQuery = 'SELECT a.order_user_id FROM '.hikashop_table('order').' AS a LEFT JOIN '.hikashop_table('order_product').' AS b ON a.order_id = b.order_id WHERE 1 GROUP BY a.order_user_id HAVING sum(b.order_product_quantity) '.$filter['operator'].' '.$filter['value'];
				}else{
					$cQuery = 'SELECT order_user_id FROM '.hikashop_table('order').' WHERE 1 GROUP BY order_user_id HAVING count(order_id) '.$filter['operator'].' '.$filter['value'];
				}
				$db->setQuery($cQuery);

				if(!HIKASHOP_J25){
					$userList = $db->loadResultArray();
				} else {
					$userList = $db->loadColumn();
				}
				if(!empty($userList))
					$query->where[] = 'hk_user.user_id IN ('.implode(',',$userList).')';
				else
					$query->where[] = 'hk_user.user_id = 0';
				$query->group = 'hk_order.order_user_id';
			}
		}
	}
	function onCountOrderMassFiltertotalPurchase(&$query,$filter,$num){
		$elements = array();
		$this->onProcessOrderMassFiltertotalPurchase($elements,$query,$filter,$num);
		return JText::sprintf('SELECTED_PRODUCTS',$query->count('hk_order.order_id'));
	}

	function onProcessOrderMassActiondisplayResults(&$elements,&$action,$k){
		$params = $this->massaction->_displayResults('order',$elements,$action,$k);
		$params->action_id = $k;
		$js = '';

		$app = JFactory::getApplication();
		if($app->isAdmin() && JRequest::getVar('ctrl','massaction') == 'massaction'){
			echo hikashop_getLayout('massaction','results',$params,$js);
		}
	}
	function onProcessOrderMassActionexportCsv(&$elements,&$action,$k){
		$formatExport = $action['formatExport']['format'];
		$email = $action['formatExport']['email'];
		$path = $action['formatExport']['path'];
		if(!empty($path)){
			$url = $this->massaction->setExportPaths($path);
		}else{
			$url = array('server'=>'','web'=>'');
			ob_get_clean();
		}

		$app = JFactory::getApplication();
		if($app->isAdmin() || (!$app->isAdmin() && !empty($path))){
			$params = new stdClass();
			$params->action['order']['order_id'] = 'order_id';
			unset($action['formatExport']);
			$params = $this->massaction->_displayResults('order',$elements,$action,$k);
			$params->formatExport = $formatExport;
			$params->path = $url['server'];
			$params = $this->massaction->sortResult($params->table,$params);
			$this->massaction->_exportCSV($params);
		}
		if(!empty($email) && !empty($path)){
			$config = hikashop_config();
			$mailClass = hikashop_get('class.mail');
			$content = array('type' => 'csv_export');
			$mail = $mailClass->get('massaction_notification',$content);
			$mail->subject = JText::_('MASS_CSV_EMAIL_SUBJECT');
			$mail->html = '1';
			$csv = new stdClass();
			$csv->name = basename($path);
			$csv->filename = basename($path);
			$csv->url = $url['web'];
			$mail->attachments = array($csv);
			$mail->dst_name = '';
			$mail->dst_email = explode(',',$email);
			$mailClass->sendMail($mail);
		}
	}
	function onProcessOrderMassActionupdateValues(&$elements,&$action,$k){
		$current = 'order';
		$current_id = $current.'_id';
		$ids = array();

		foreach($elements as $element){
			$ids[] = $element->$current_id;
			if(isset($element->$action['type']))
				$element->$action['type'] = $action['value'];
		}

		$action['type'] = strip_tags($action['type']);
		$alias = explode('_',$action['type']);
		if(preg_match('/order_product/',$action['type'])) $alias = array('order_product');
		$queryTables = array($current);
		$possibleTables = array($current, 'order_product');
		if(!isset($this->massaction))$this->massaction = hikashop_get('class.massaction');
		$value = $this->massaction->updateValuesSecure($action,$possibleTables,$queryTables);
		JArrayHelper::toInteger($ids);
		$db = JFactory::getDBO();

		$max = 500;
		if(count($ids) > $max){
			$c = ceil((int)count($ids) / $max);
			for($i = 0; $i < $c; $i++){
				$offset = $max * $i;
				$id = array_slice($ids, $offset, $max);
				$query = 'UPDATE '.hikashop_table($current).' AS hk_'.$current.' ';
				$queryTables = array_unique($queryTables);
				foreach($queryTables as $queryTable){
					switch($queryTable){
						case 'order_product':
							$query .= 'LEFT JOIN '.hikashop_table('order_product').' AS hk_order_product ON hk_order_product.order_id = hk_order.order_id ';
							break;
					}
				}
				$query .= 'SET hk_'.$alias[0].'.'.$action['type'].' = '.$value.' ';
				$query .= 'WHERE hk_'.$current.'.'.$current.'_id IN ('.implode(',',$id).')';

				$db->setQuery($query);
				$db->query();
			}
		}else{
			$query = 'UPDATE '.hikashop_table($current).' AS hk_'.$current.' ';
			$queryTables = array_unique($queryTables);
			foreach($queryTables as $queryTable){
				switch($queryTable){
					case 'order_product':
						$query .= 'LEFT JOIN '.hikashop_table('order_product').' AS hk_order_product ON hk_order_product.order_id = hk_order.order_id ';
						break;
				}
			}
			$query .= 'SET hk_'.$alias[0].'.'.$action['type'].' = '.$value.' ';
			$query .= 'WHERE hk_'.$current.'.'.$current.'_id IN ('.implode(',',$ids).')';

			$db->setQuery($query);
			$db->query();
		}

	}

	function onProcessOrderMassActionchangeStatus(&$elements,&$action,$k){
		$orderClass = hikashop_get('class.order');
		foreach($elements as $element){
			$element->order_status = $action['value'];
			unset($element->order_payment_price);
			unset($element->order_shipping_price);
			unset($element->order_discount_price);
			if(isset($action['notify'])){
				if(!is_object($element->history))
					$element->history = new stdClass();
				$element->history->history_notified = 1;
			}
			$orderClass->save($element);
		}
	}

	function onProcessOrderMassActionaddProduct(&$elements,&$action,$k){
		if(!empty($action['value'])){
			if(!isset($action['quantity'])) $action['quantity'] = '';
			$orderClass = hikashop_get('class.order');
			$order_productClass = hikashop_get('class.order_product');
			$unsetValues = array();
			foreach($elements as $order){
				$orderClass->loadProducts($order);
				foreach($order->products as $product){
					if(in_array($product->product_id,$action['value'])){
						if($action['type'] == 'add'){
							(!empty($action['quantity']))?$action['quantity']:1;
							$product->order_product_quantity = (int)$product->order_product_quantity + (int)$action['quantity'];
						}
						else{
							if(isset($action['quantity']) && !empty($action['quantity']))
								$product->order_product_quantity = (int)$product->order_product_quantity - (int)$action['quantity'];
							else
								$product->order_product_quantity = 0;
							if($product->order_product_quantity < 0)
								$product->order_product_quantity = 0;
						}
						if(isset($action['update']))
							$product->no_update ='update';

						$order_productClass->update($product);

						$unsetValues[] = $product->product_id;
					}
				}
				foreach($action['value'] as $k => $value){
					if(in_array($value,$unsetValues))
						unset($action['value'][$k]);
				}
				if(!empty($action['value'])){
					$product_ids = array();
					foreach($action['value'] as $value){
						$product = new stdClass();
						$product->product_id = $value;
						if(isset($action['update']))
							$product->no_update ='update';
						if($action['type'] == 'add'){
							(!empty($action['quantity']))?$action['quantity']:1;
							$product->order_product_quantity = (int)$action['quantity'];
							$product_ids[] = $value;
						}
						else{
							$product->order_product_quantity = 0;
							$order_productClass->update($product);
						}
					}
					if($action['type'] == 'add'){
						$productClass = hikashop_get('class.product');
						JRequest::setVar( 'order_id', $order->order_id);
						$data = array();
						$data['products'] = true;
						$data['order']['product']['many'] = true;
						$productClass->getProducts($product_ids);
						foreach($productClass->products as $k => $product){
							$data['order']['product'][$k]['order_id'] = $order->order_id;
							$data['order']['product'][$k]['product_id'] = $product->product_id;
							$data['order']['product'][$k]['order_product_quantity'] = (!empty($action['quantity']))?$action['quantity']:1;
							$data['order']['product'][$k]['order_product_id'] = '';
							$data['order']['product'][$k]['order_product_name'] = $product->product_name;
							$data['order']['product'][$k]['order_product_code'] = $product->product_code;
							$data['order']['product'][$k]['order_product_price'] = $product->prices[0]->price_value;
						}
						JRequest::setVar('data', $data);
						$orderClass->saveForm('products');
					}
				}
			}
		}
	}

	function onProcessOrderMassActiondeleteElements(&$elements,&$action,$k){
		$ids = array();
		foreach($elements as $element){
			$ids[] = $element->order_id;
		}
		$orderClass = hikashop_get('class.order');

		$max = 500;
		if(count($ids) > $max){
			$c = ceil((int)count($ids) / $max);
			for($i = 0; $i < $c; $i++){
				$offset = $max * $i;
				$id = array_slice($ids, $offset, $max);
				$result = $orderClass->delete($id);
			}
		}else{
			$result = $orderClass->delete($ids);
		}
	}

	function onProcessOrderMassActionchangeGroup(&$elements,&$action,$k){
		$user_ids = array();
		$values = array();

		foreach($elements as $element){
			$user_ids[] = $element->order_user_id;
		}

		$db = JFactory::getDBO();
		$db->setQuery('SELECT user_cms_id FROM '.hikashop_table('user').' WHERE user_id IN ('.implode(',',$user_ids).') AND user_cms_id != 0');
		if(!HIKASHOP_J25){
			$user_ids = $db->loadResultArray();

			$db->setQuery('SELECT id FROM '.hikashop_table('core_acl_aro',false).' WHERE value IN ('.implode(',',$user_ids).')');
			$user_ids = $db->loadResultArray();

			$db->setQuery('DELETE FROM '.hikashop_table('core_acl_groups_aro_map',false).' WHERE aro_id IN ('.implode(',',$user_ids).')');
			$db->query();

			foreach($user_ids as $user_id){
				$values[$user_id] = '('.$action['value'].',"",'.$user_id.')';
			}
			$db->setQuery('INSERT INTO '.hikashop_table('core_acl_groups_aro_map',false).' VALUES '.implode(',',$values));
			$db->query();

		}else {
			$user_ids = $db->loadColumn();
			foreach($user_ids as $user_id){
				$values[$user_id] = '('.$user_id.','.$action['value'].')';
			}
			if($action['type'] == 'replace'){
				$db->setQuery('DELETE FROM '.hikashop_table('user_usergroup_map',false).' WHERE user_id IN ('.implode(',',$user_ids).')');
				$db->query();
			}

			$db->setQuery('REPLACE INTO '.hikashop_table('user_usergroup_map',false).' VALUES '.implode(',',$values));
			$db->query();
		}
	}
	function onProcessOrderMassActionsendEmail(&$elements,&$action,$k){
		if(!empty($action['emailAddress'])){
			$config = hikashop_config();
			$mailClass = hikashop_get('class.mail');
			$content = array('elements' => $elements, 'action' => $action, 'type' => 'order_notification');
			$mail = $mailClass->get('massaction_notification',$content);
			$mail->subject = !empty($action['emailSubject'])?JText::_($action['emailSubject']):JText::_('MASS_NOTIFICATION_EMAIL_SUBJECT');
			$mail->body = $action['bodyData'];
			$mail->html = '1';
			$mail->dst_name = '';
			if(!empty($action['emailAddress']))
				$mail->dst_email = explode(',',$action['emailAddress']);
			else
				$mail->dst_email = $config->get('from_email');
			$mailClass->sendMail($mail);
		}
	}


	function onBeforeOrderCreate(&$order,&$do){
		$orders = array($order);
		$this->massaction->trigger('onBeforeOrderCreate',$orders);
	}

	function onBeforeOrderUpdate(&$order,&$do){
		if(!empty($order->old)){
			foreach($order->old as $key => $value) {
				if(!isset($order->$key))
					$order->$key = $value;
			}
		}
		$orders = array($order);
		$this->massaction->trigger('onBeforeOrderUpdate',$orders);
	}

	function onBeforeOrderDelete(&$elements, &$do){
		$orderClass = hikashop_get('class.order');
		$toDelete = array();
		if(!is_array($elements)) $clone = array($elements);
		else $clone = $elements;
		foreach($clone as $element){
			$toDelete[] = $orderClass->get($element);
		}
		$this->deletedOrder = &$toDelete;
		$this->massaction->trigger('onBeforeOrderDelete',$toDelete);
	}

	function onAfterOrderCreate(&$order,&$send_email){
		$getOrder = $this->order->get($order->order_id);
		$getOrder->old = '';
		$orders = array($getOrder);
		$this->massaction->trigger('onAfterOrderCreate',$orders);
	}

	function onAfterOrderUpdate(&$order,&$send_email){
		$orders = array($order);
		$this->massaction->trigger('onAfterOrderUpdate',$orders);
	}

	function onAfterOrderDelete($elements){
		$this->massaction->trigger('onAfterOrderDelete', $this->deletedOrder);
	}
}
