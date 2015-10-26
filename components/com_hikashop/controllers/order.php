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
class orderController extends hikashopController{
	var $modify = array();
	var $delete = array();
	var $modify_views = array();
	function __construct($config = array(),$skip=false){
		parent::__construct($config,$skip);
		$this->display[]='cancel';
		$this->display[]='invoice';
		$this->display[]='download';
		$this->display[]='pay';
		$this->display[]='cancel_order';
		$this->display[]='reorder';
	}
	function authorize($task){
		if($this->isIn($task,array('display'))){
			return true;
		}
		return false;
	}

	function listing(){
		$user_id = hikashop_loadUser();
		if(empty($user_id)){
			$app=JFactory::getApplication();
			$app->enqueueMessage(JText::_('PLEASE_LOGIN_FIRST'));
			global $Itemid;
			$url = '';
			if(!empty($Itemid)){
				$url='&Itemid='.$Itemid;
			}
			if(version_compare(JVERSION,'1.6','<')){
				$url = 'index.php?option=com_user&view=login'.$url;
			}else{
				$url = 'index.php?option=com_users&view=login'.$url;
			}
			$app->redirect(JRoute::_($url.'&return='.urlencode(base64_encode(hikashop_currentUrl('',false))),false));
			return true;
		}
		return parent::listing();
	}

	function show(){
		if($this->_check()){
			return parent::show();
		}
		return true;
	}

	function cancel_order(){
		$app = JFactory::getApplication();
		$order_id = hikashop_getCID('order_id');
		if(empty($order_id)){
			$order_id = $app->getUserState( HIKASHOP_COMPONENT.'.order_id');
		}
		$class = hikashop_get('class.order');
		$order = $class->get($order_id);
		$config =& hikashop_config();
		$checkout = explode(',',$config->get('checkout'));
		$step = JRequest::getInt('step',0);
		if (empty($step))
			$step = max(count($checkout)-2,0);
		$itemid_for_checkout = $config->get('checkout_itemid','0');
		$item ='';
		if(!empty($itemid_for_checkout)){
			$item='&Itemid='.(int)$itemid_for_checkout;
		}
		$cancel_url =  hikashop_completeLink('checkout&step='.$step.$item,false,true);

		if(!empty($order)){
			$user_id = hikashop_loadUser();
			if($order->order_user_id==$user_id){
				$status = $config->get('cancelled_order_status');
				$unpaid_statuses = explode(',',$config->get('order_unpaid_statuses','created'));
				$cancellable_statuses = explode(',',$config->get('cancellable_order_status'));

				if( in_array($order->order_status, $unpaid_statuses) || in_array($order->order_status, $cancellable_statuses) ) {
					if(!empty($status)){
						$statuses = explode(',',$status);
						$newOrder = new stdClass();
						$newOrder->order_status = reset($statuses);
						$newOrder->order_id = $order_id;
						$class->save($newOrder);

						if( JRequest::getVar('email',false) ) {
							$mailClass = hikashop_get('class.mail');
							$infos = null;
							$infos =& $order;
							$mail = $mailClass->get('order_cancel',$infos);
							if( !empty($mail) ) {
								$mail->subject = JText::sprintf($mail->subject,HIKASHOP_LIVE);
								$config =& hikashop_config();
								if(!empty($infos->email)){
									$mail->dst_email = $infos->email;
								}else{
									$mail->dst_email = $config->get('from_email');
								}
								if(!empty($infos->name)){
									$mail->dst_name = $infos->name;
								}else{
									$mail->dst_name = $config->get('from_name');
								}
								$mailClass->sendMail($mail);
							}
						}
					}
				}
			}
			$db = JFactory::getDBO();
			$query = 'SELECT * FROM '.hikashop_table('payment').' WHERE payment_type='.$db->Quote($order->order_payment_method).' AND payment_id='.$db->Quote($order->order_payment_id);
			$db->setQuery($query);
			$paymentData = $db->loadObjectList();
			$pluginsClass = hikashop_get('class.plugins');
			$pluginsClass->params($paymentData,'payment');
			$paymentOptions=reset($paymentData);
			if(!empty($paymentOptions->payment_params->cancel_url)){
				$cancel_url = $paymentOptions->payment_params->cancel_url;
			}
		}
		$redirect_url = JRequest::getVar('redirect_url');
		if( !empty($redirect_url) )
			$cancel_url = $redirect_url;

		$app->redirect($cancel_url);
		return true;
	}

	function invoice(){
		if($this->_check()){
			JRequest::setVar( 'layout', 'invoice'  );
			return parent::display();
		}
		return true;
	}

	function reorder(){
		if(!hikashop_level(1) || !$this->_check()){
			return false;
		}
		$order_id = hikashop_getCID('order_id');
		if(empty($order_id)){
			parent::listing();
			return false;
		}

		$class = hikashop_get('class.order');
		$order = $class->loadFullOrder($order_id,true);

		$app = JFactory::getApplication();


		if(empty($order->order_id)){
			$app->enqueueMessage('The order '.$order_id.' could not be found');
			parent::listing();
			return false;
		}

		$cartClass = hikashop_get('class.cart');
		$cartClass->resetCart(false);

		$array = array();
		$hasOptions = array();
		foreach($order->products as $product){
			if(!empty($product->order_product_option_parent_id)){
				$hasOptions[$product->order_product_option_parent_id]=$product->order_product_option_parent_id;
			}
		}

		$fieldsClass = hikashop_get('class.field');
		$row = null;
		$itemFields = $fieldsClass->getFields('frontcomp',$row,'item','checkout&task=state');

		$done = array();
		foreach($order->products as $product){
			if(!empty($done[$product->order_product_id]) || empty($product->product_id) || !empty($product->order_product_option_parent_id)) continue;

			foreach($product as $k => $v){
				if(!is_object($v) && !is_array($v) && isset($itemFields[$k])){
					$_REQUEST['data']['item'][$k] = $v;
					$_POST['data']['item'][$k] = $v;
					$_GET['data']['item'][$k] = $v;
					JRequest::setVar('data',$_REQUEST['data']);
					JRequest::setVar('data',$_GET['data'],'get');
					JRequest::setVar('data',$_POST['data'],'post');
				}
			}
			if(isset($hasOptions[$product->order_product_id])){
				$cartClass->mainProduct = $product->product_id;
				$cartClass->options = array();
				foreach($order->products as $option){
					if($option->order_product_option_parent_id==$product->order_product_id){
						$cartClass->options[$option->product_id] = $product->order_product_quantity;
						$done[$option->order_product_id] = $option->order_product_id;
					}
				}
			}
			$cartClass->updateEntry($product->order_product_quantity,$array,$product->product_id,0,false,'product',2);

			if(!empty($cartClass->options)){
				foreach($cartClass->options as $id => $qty){
					$cartClass->updateEntry($qty,$array,$id,0,false);
				}
				$cartClass->options = array();
				$cartClass->mainProduct = null;
			}

			$done[$product->order_product_id] = $product->order_product_id;
		}

		if(!empty($order->order_discount_code)){
			$cartClass->update($order->order_discount_code,1,0,'coupon',false);
		}
		$cartClass->loadCart(0,true);
		$app->setUserState( HIKASHOP_COMPONENT.'.shipping_method',null);
		$app->setUserState( HIKASHOP_COMPONENT.'.shipping_id',null);
		$app->setUserState( HIKASHOP_COMPONENT.'.shipping_data',null);

		global $Itemid;
		$url = 'checkout';
		if(!empty($Itemid)){
			$url.='&Itemid='.$Itemid;
		}
		$url = hikashop_completeLink($url,false,true);

		$app->redirect($url);
	}

	function pay(){
		if(!$this->_check()){
			return false;
		}
		$order_id = hikashop_getCID('order_id');
		if(empty($order_id)){
			parent::listing();
			return false;
		}

		$class = hikashop_get('class.order');
		$order = $class->loadFullOrder($order_id,true);
		if(empty($order->order_id)){
			$app =& JFactory::getApplication();
			$app->enqueueMessage('The order '.$order_id.' could not be found');
			parent::listing();
			return false;
		}

		$config =& hikashop_config();
		$unpaid_statuses = explode(',',$config->get('order_unpaid_statuses','created'));
		if(!in_array($order->order_status,$unpaid_statuses)){
			$app =& JFactory::getApplication();
			$app->enqueueMessage('The order '.$order->order_number.' cannot be paid anymore.');
			parent::listing();
			return false;
		}

		if(empty($order->order_currency_id)){
			$null = new stdClass();
			$null->order_currency_id = hikashop_getCurrency();
			$null->order_id = $order->order_id;
			$order->order_currency_id = $null->order_currency_id;
			$class->save($null);
		}
		$new_payment_method = JRequest::getVar('new_payment_method','');
		$config =& hikashop_config();
		if($config->get('allow_payment_change',1) && !empty($new_payment_method)){
			$new_payment_method = explode('_',$new_payment_method);
			$payment_id = array_pop($new_payment_method);
			$payment_method = implode('_',$new_payment_method);
			if($payment_id!=$order->order_payment_id || $payment_method!=$order->order_payment_method){
				$updateOrder=new stdClass();
				$updateOrder->order_id=$order->order_id;
				$updateOrder->order_payment_id = $payment_id;
				$updateOrder->order_payment_method = $payment_method;
				$paymentClass = hikashop_get('class.payment');
				$payment = $paymentClass->get($payment_id);
				if(!empty($payment->payment_params)&&is_string($payment->payment_params)){
					$payment->payment_params=unserialize($payment->payment_params);
				}
				$full_price_without_payment = $order->order_full_price-$order->order_payment_price;
				$new_payment = $payment;
				$new_payment_price = $paymentClass->computePrice( $order, $new_payment, $full_price_without_payment, @$payment->payment_price, hikashop_getCurrency());
				$new_payment_tax = @$new_payment->payment_tax;
				$updateOrder->order_payment_price = $new_payment_price;
				$updateOrder->order_full_price = $full_price_without_payment+$new_payment_price+$new_payment_tax;
				$updateOrder->history = new stdClass();
				$updateOrder->history->history_payment_id = $payment_id;
				$updateOrder->history->history_payment_method = $payment_method;
				$class->save($updateOrder);
				$order->order_payment_id = $payment_id;
				$order->order_payment_method = $payment_method;
				$order->order_payment_price = $updateOrder->order_payment_price;
				$order->order_full_price = $updateOrder->order_full_price;
			}
		}

		$userClass = hikashop_get('class.user');
		$order->customer = $userClass->get($order->order_user_id);
		$db = JFactory::getDBO();
		$query = 'SELECT * FROM '.hikashop_table('payment').' WHERE payment_type='.$db->Quote($order->order_payment_method);
		$db->setQuery($query);
		$paymentData = $db->loadObjectList('payment_id');
		$pluginsClass = hikashop_get('class.plugins');
		$pluginsClass->params($paymentData,'payment');
		if(empty($paymentData)){
			$app =& JFactory::getApplication();
			$app->enqueueMessage('The payment method '.$order->order_payment_method.' could not be found');

			parent::listing();
			return false;
		}
		$order->cart =& $order;
		$order->cart->coupon = new stdClass();
		$price = new stdClass();
		$price->price_value_with_tax = $order->order_full_price;
		$order->cart->full_total = new stdClass();
		$order->cart->full_total->prices = array($price);
		$price2 = new stdClass();
		$total = 0;
		$class = hikashop_get('class.currency');
		$order->cart->total = new stdClass();
		$price2 = $class->calculateTotal($order->products,$order->cart->total,$order->order_currency_id);
		$order->cart->coupon->discount_value =& $order->order_discount_price;

		$shippingClass = hikashop_get('class.shipping');
		$methods = $shippingClass->getMethods($order->cart);
		$data = hikashop_import('hikashopshipping',$order->order_shipping_method);
		if(!empty($data))
			$order->cart->shipping = $data->onShippingSave($order->cart,$methods,$order->order_shipping_id);

		$app = JFactory::getApplication();
		$app->setUserState( HIKASHOP_COMPONENT.'.shipping_address',$order->order_shipping_address_id);
		$app->setUserState( HIKASHOP_COMPONENT.'.billing_address',$order->order_billing_address_id);
		ob_start();
		$data = hikashop_import('hikashoppayment',$order->order_payment_method);
		if(!empty($data)){
			$needCC = false;
			if( method_exists($data, 'needCC') ) {
				$method =& $paymentData[$order->order_payment_id];
				$needCC = $data->needCC($method);
			}
			if( !$needCC ) {
				$itemid_for_checkout = $config->get('checkout_itemid','0');
				if($itemid_for_checkout){
					global $Itemid;
					$Itemid = $itemid_for_checkout;
				}
				if(method_exists($data,'onAfterOrderConfirm')) $data->onAfterOrderConfirm($order,$paymentData,$order->order_payment_id);
			} else {
				$paymentClass = hikashop_get('class.payment');
				$do = false;

				$app->setUserState( HIKASHOP_COMPONENT.'.payment_method',$order->order_payment_method);
				$app->setUserState( HIKASHOP_COMPONENT.'.payment_id',$order->order_payment_id);
				$app->setUserState( HIKASHOP_COMPONENT.'.payment_data',$method);

				if( $paymentClass->readCC() ) {
					$do = true;
					if(method_exists($data,'onBeforeOrderCreate')) $data->onBeforeOrderCreate($order, $do);
				}

				if( !$do ) {
					$app->setUserState( HIKASHOP_COMPONENT.'.cc_number','');
					$app->setUserState( HIKASHOP_COMPONENT.'.cc_month','');
					$app->setUserState( HIKASHOP_COMPONENT.'.cc_year','');
					$app->setUserState( HIKASHOP_COMPONENT.'.cc_CCV','');
					$app->setUserState( HIKASHOP_COMPONENT.'.cc_type','');
					$app->setUserState( HIKASHOP_COMPONENT.'.cc_owner','');

					$params = '';
					$js = '';
					echo hikashop_getLayout('checkout','ccinfo',$params,$js);
				} else {
					$order->history->history_notified = 1;
					$class = hikashop_get('class.order');
					$updateOrder=new stdClass();
					$updateOrder->order_id=$order->order_id;
					$updateOrder->order_status=$order->order_status;
					$updateOrder->order_payment_id = $payment_id;
					$updateOrder->order_payment_method = $payment_method;
					$updateOrder->history =& $order->history;

					$class->save($updateOrder);

					$app->redirect( hikashop_completeLink('checkout&task=after_end', false, true) );
				}
			}
		}
		$html = ob_get_clean();
		if(empty($html)){
			$app =& JFactory::getApplication();
			$app->enqueueMessage('The payment method '.$order->order_payment_method.' does not handle payments after the order has been created');
			parent::listing();
			return false;
		}
		echo $html;
		return true;
	}

	function download(){
		$file_id = JRequest::getInt('file_id');
		if(empty($file_id)){
			$field_table = JRequest::getString('field_table');
			$field_namekey = base64_decode(urldecode(JRequest::getString('field_namekey')));
			$name = base64_decode(urldecode(JRequest::getString('name')));
			if(empty($field_table)||empty($field_namekey)||empty($name)){
				$app=JFactory::getApplication();
				$app->enqueueMessage(JText::_('FILE_NOT_FOUND'));
				return false;
			}else{
				$options = array(
					'thumbnail_x' => JRequest::getInt('thumbnail_x', 0),
					'thumbnail_y' => JRequest::getInt('thumbnail_y', 0)
				);
				$fileClass = hikashop_get('class.file');
				$fileClass->downloadFieldFile($name, $field_table, $field_namekey, $options);
				exit;
			}
		}

		$order_id = hikashop_getCID('order_id');
		if(empty($order_id)){
			parent::listing();
			return false;
		}

		$file_pos = JRequest::getInt('file_pos', 1);
		$email = JRequest::getVar('email', '');

		$fileClass = hikashop_get('class.file');
		if(!$fileClass->download($file_id, $order_id, $file_pos, $email)) {
			switch($fileClass->error_type){
				case 'login':
					$this->_check(false);
					break;
				case 'no_order';
					parent::listing();
					break;
				default:
					parent::show();
					break;
			}
		}
		return true;
	}

	function _check($message = true){
		$user_id = hikashop_loadUser();
		if(empty($user_id)){
			$app = JFactory::getApplication();
			if($message) $app->enqueueMessage(JText::_('PLEASE_LOGIN_FIRST'));
			global $Itemid;
			$url = '';
			if(!empty($Itemid)){
				$url='&Itemid='.$Itemid;
			}
			if(version_compare(JVERSION,'1.6','<')){
				$url = 'index.php?option=com_user&view=login'.$url;
			}else{
				$url = 'index.php?option=com_users&view=login'.$url;
			}
			$app->redirect(JRoute::_($url.'&return='.urlencode(base64_encode(hikashop_currentUrl('',false))),false));
			return false;
		}
		$order_id = hikashop_getCID('order_id');
		if(empty($order_id)){
			parent::listing();
			return false;
		}
		return true;
	}

	function cancel(){
		$cancel_redirect = JRequest::getString('cancel_redirect');
		if(empty($cancel_redirect)){
			$cancel_url = JRequest::getString('cancel_url');
			if(!empty($cancel_url)){
				$cancel_url = urldecode($cancel_url);
				if(hikashop_disallowUrlRedirect($cancel_url)) return false;
				$this->setRedirect(base64_decode($cancel_url));
			}else{
				$order_id = hikashop_getCID('order_id');
				if(empty($order_id)){
					global $Itemid;
					$url = '';
					if(!empty($Itemid)){
						$url='&Itemid='.$Itemid;
					}
					$this->setRedirect(hikashop_completeLink('user'.$url,false,true));
				}else{
					return $this->listing();
				}
			}
		}else{
			$cancel_redirect = urldecode($cancel_redirect);
			if(hikashop_disallowUrlRedirect($cancel_redirect)) return false;
			$this->setRedirect($cancel_redirect);
		}
	}

	function getUploadSetting($upload_key, $caller = '') {
		if(empty($upload_key))
			return false;
		if(strpos($upload_key, '-') === false)
			return false;
		if(in_array($caller, array('galleryimage', 'galleryselect', 'image')))
			return false;

		list($field_table, $field_namekey) = explode('-', $upload_key, 2);

		$fieldClass = hikashop_get('class.field');
		$field = $fieldClass->getField($field_namekey, $field_table);

		if(empty($field) || ($field->field_type != 'ajaxfile' && $field->field_type != 'ajaximage'))
			return false;

		$map = JRequest::getString('field_map', '');
		if(empty($map))
			return false;

		$config = hikashop_config();
		$options = array(
			'upload_dir' => $config->get('uploadsecurefolder')
		);

		$type = ($field->field_type == 'ajaxfile') ? 'file' : 'image';

		return array(
			'limit' => 1,
			'type' => $type,
			'options' => $options,
			'extra' => array(
				'field_name' => $map,
				'delete' => empty($field->field_required),
				'uploader_id' => JRequest::getString('uploader_id', '')
			)
		);
	}

	function manageUpload($upload_key, &$ret, $uploadConfig, $caller = '') {
		if(empty($ret) || empty($ret->name))
			return;

		if(empty($upload_key))
			return;
		if(strpos($upload_key, '-') === false)
			return;

		list($field_table, $field_namekey) = explode('-', $upload_key);

		$fieldClass = hikashop_get('class.field');
		$field = $fieldClass->getField($field_namekey, $field_table);

		if(empty($field) || ($field->field_type != 'ajaxfile' && $field->field_type != 'ajaximage'))
			return;

		$map = JRequest::getString('field_map', '');
		if(empty($map))
			return;

		if($field_table == 'item') {
			$app = JFactory::getApplication();
			$itemsData = $app->getUserState(HIKASHOP_COMPONENT.'.items_fields');
			if(empty($itemsData)) $itemsData = array();
			$newItem = new stdClass();
			$newItem->$field_namekey = $ret->name;
			$itemsData[] = $newItem;
			$app->setUserState(HIKASHOP_COMPONENT.'.items_fields', $itemsData);
		}

		if($field_table == 'order') {
			$app = JFactory::getApplication();
			$orderData = $app->getUserState(HIKASHOP_COMPONENT.'.checkout_fields');
			if(empty($orderData)) $orderData = new stdClass();
			$orderData->$field_namekey = $ret->name;
			$app->setUserState(HIKASHOP_COMPONENT.'.checkout_fields', $orderData);
		}

		if(substr($field_table, 0, 4) == 'plg.') {
			$externalValues = array();
			JPluginHelper::importPlugin('hikashop');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onTableFieldsLoad', array( &$externalValues ) );
			$found = false;
			foreach($externalValues as $external) {
				if($external->value == $field_table) {
					$found = true;
					break;
				}
			}
			if($found) {
				$app = JFactory::getApplication();
				$elemData = $app->getUserState(HIKASHOP_COMPONENT.'.plg_fields.' . substr($field_table, 4));
				if(empty($elemData)) $elemData = array();
				$newItem = new stdClass();
				$newItem->$field_namekey = $ret->name;
				$elemData[] = $newItem;
				$app->setUserState(HIKASHOP_COMPONENT.'.plg_fields.' . substr($field_table, 4), $elemData);
			}
		}

		if($field->field_type == 'ajaxfile')
			$ajaxFileClass = new hikashopAjaxfile($fieldClass);
		else
			$ajaxFileClass = new hikashopAjaximage($fieldClass);
		$ajaxFileClass->_manageUpload($field, $ret, $map, $uploadConfig, $caller);
	}
}
