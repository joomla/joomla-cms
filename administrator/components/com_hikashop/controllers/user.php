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
class UserController extends hikashopController{
	var $type='user';
	function __construct($config = array()){
		parent::__construct($config);
		$this->modify_views[]='editaddress';
		$this->modify[]='deleteaddress';
		$this->modify[]='saveaddress';
		$this->modify[]='setdefault';
		$this->display[]='state';
		$this->display[]='clicks';
		$this->display[]='leads';
		$this->display[]='sales';
		$this->display[]='selection';
		$this->display[]='useselection';
		$this->modify_views[]='pay';
		$this->modify[]='pay_confirm';
		$this->modify_views[]='pay_process';
	}

	function getACLName($task){
		$app = JFactory::getApplication();
		if($app->getUserStateFromRequest(HIKASHOP_COMPONENT.'.user.filter_partner', 'filter_partner', '', 'int')==1){
			return 'affiliates';
		}
		return 'user';
	}

	function deleteaddress(){
		$addressdelete = JRequest::getInt('address_id',0);
		if($addressdelete){
			$addressClass = hikashop_get('class.address');
			$oldData = $addressClass->get($addressdelete);
			if(!empty($oldData)){
				$addressClass->delete($addressdelete);
				JRequest::setVar('user_id',$oldData->address_user_id);
			}
		}
		$this->edit();
	}

	function setdefault(){
		$newDefaultId = JRequest::getInt('address_default', 0);
		if($newDefaultId){
			JRequest::checkToken('request') || jexit( 'Invalid Token' );
			$addressClass = hikashop_get('class.address');
			$oldData = $addressClass->get($newDefaultId);
			if(!empty($oldData)){
				$user_id = hikashop_getCID('user_id');
				if($user_id==$oldData->address_user_id){
					$oldData->address_default = 1;
					$addressClass->save($oldData);
				}
			}
		}
		$this->edit();
	}

	function cancel(){
		$order_id = JRequest::getInt('order_id');
		if(empty($order_id)){
			$cancel_redirect = JRequest::getString('cancel_redirect');
			if(empty($cancel_redirect)){
				$this->listing();
			}else{
				$cancel_redirect = base64_decode(urldecode($cancel_redirect));
				if(hikashop_disallowUrlRedirect($cancel_redirect)) return false;
				$this->setRedirect($cancel_redirect);
			}
		}else{
			$this->setRedirect(hikashop_completeLink('order&task=edit&order_id='.$order_id,false,true));
		}
	}

	function saveaddress(){
		$addressClass = hikashop_get('class.address');
		$oldData = null;

		if(!empty($_REQUEST['address']['address_id'])){
			$oldData = $class->get($_REQUEST['address']['address_id']);
		}
		$fieldClass = hikashop_get('class.field');
		$addressData = $fieldClass->getInput('address',$oldData);
		$ok = true;
		if(empty($addressData)){
			$ok=false;
		}else{
			$address_id = $addressClass->save($addressData);
		}
		if(!$ok || !$address_id){
			$app =& JFactory::getApplication();
			if(version_compare(JVERSION,'1.6','<')){
				$session =& JFactory::getSession();
				$session->set('application.queue', $app->_messageQueue);
			}
			echo '<html><head><script type="text/javascript">javascript: history.go(-1);</script></head><body></body></html>';
			exit;
		}
		$url = hikashop_completeLink('user&task=edit&user_id='.$addressData->address_user_id,false,true);
		echo '<html><head><script type="text/javascript">parent.window.location.href=\''.$url.'\';</script></head><body></body></html>';
		exit;
	}

	function editaddress(){
		JRequest::setVar( 'layout', 'editaddress'  );
		return parent::display();
	}

	function state(){
		JRequest::setVar( 'layout', 'state' );
		return parent::display();
	}
	function clicks(){
		JRequest::setVar( 'layout', 'clicks' );
		return parent::display();
	}
	function leads(){
		JRequest::setVar( 'layout', 'leads' );
		return parent::display();
	}
	function sales(){
		JRequest::setVar( 'layout', 'sales' );
		return parent::display();
	}
	function pay(){
		JRequest::setVar( 'layout', 'pay' );
		return parent::display();
	}
	function selection(){
		JRequest::setVar( 'layout', 'selection' );
		return parent::display();
	}
	function useselection(){
		JRequest::setVar( 'layout', 'useselection' );
		return parent::display();
	}
	function pay_confirm(){
		$user_id = hikashop_getCID('user_id');
		if(!empty($user_id)){
			$class = hikashop_get('class.user');
			$user = $class->get($user_id);
			if(!empty($user)){
				$class->loadPartnerData($user);
				if(bccomp($user->accumulated['currenttotal'],0,5)){
					$method = JRequest::getCmd('pay_method');
					$pay = JRequest::getInt('pay',0);
					$order = new stdClass();
					$config =& hikashop_config();
					if(!$config->get('allow_currency_selection',0) || empty($user->user_currency_id)){
						$user->user_currency_id =  $config->get('partner_currency',1);
					}
					$order->order_currency_id = $user->user_currency_id;
					$order->order_full_price = $user->accumulated['currenttotal'];
					if(!empty($method)&&$pay){
						$pluginClass = hikashop_get('class.plugins');
						$methods = $pluginClass->getMethods('payment');
						foreach($methods as $methodItem){
							if($methodItem->payment_type==$method){
								$order->order_payment_id = $methodItem->payment_id;
								$order->order_payment_method = $methodItem->payment_type;
								break;
							}
						}
						if(empty($order->order_payment_id)){
							$app = JFactory::getApplication();
							$app->enqueueMessage('Payment method not found');
							return false;
						}
					}
					$order->order_status = $config->get('order_confirmed_status','confirmed');
					$order->history->history_reason=JText::sprintf('ORDER_CREATED');
					$order->history->history_notified=0;
					$order->history->history_type = 'creation';
					$order->order_type='partner';
					$product = new stdClass();
					$product->order_product_name=JText::sprintf('PAYMENT_TO_PARTNER',@$user->name.' ('.$user->user_partner_email.')');
					$product->order_product_code='';
					$product->order_product_price=$user->accumulated['currenttotal'];
					$product->order_product_quantity=1;
					$product->order_product_tax=0;
					$product->order_product_options='';

					$product->product_id=0;
					$order->cart = new stdClass();
					$order->order_user_id = $user->user_id;
					$order->cart->products = array($product);
					$orderClass = hikashop_get('class.order');
					$order->order_id = $orderClass->save($order);

					if(!empty($order->order_id)){

						$minDelay = $config->get('affiliate_payment_delay',0);
						$maxTime = intval(time() - $minDelay);

						$db = JFactory::getDBO();
						$query = 'UPDATE '.hikashop_table('click').' SET click_partner_paid=1 WHERE click_partner_id='.$user->user_id.' AND click_created < '.$maxTime;
						$db->setQuery($query);
						$db->query();
						$query = 'UPDATE '.hikashop_table('order').' SET order_partner_paid=1 WHERE order_type=\'sale\' AND order_partner_id='.$user->user_id.' AND order_created < '.$maxTime;
						$db->setQuery($query);
						$db->query();
						$query = 'UPDATE '.hikashop_table('user').' SET user_partner_paid=1 WHERE user_partner_id='.$user->user_id.' AND user_created < '.$maxTime;
						$db->setQuery($query);
						$db->query();
						if(!empty($order->order_payment_id) && $pay){
							$url = hikashop_completeLink('user&task=pay_process&order_id='.$order->order_id,false,true);
							echo '<html><head><script type="text/javascript">parent.window.location.href=\''.$url.'\';</script></head><body></body></html>';
							exit;
						}
					}

				}else{
					$app = JFactory::getApplication();
					$app->enqueueMessage('No affiliate money accumulated');
					return false;
				}
			}
		}
		$url=hikashop_completeLink('user&task=edit&user_id='.$user_id,false,true);
		echo '<html><head><script type="text/javascript">parent.window.location.href=\''.$url.'\';</script></head><body></body></html>';
		exit;
	}

	function pay_process(){
		$order_id = hikashop_getCID('order_id');
		if(empty($order_id)){
			return false;
		}

		$orderClass = hikashop_get('class.order');
		$order = $orderClass->get($order_id);
		$userClass = hikashop_get('class.user');
		$user = $userClass->get($order->order_user_id);
		$orderClass->loadProducts($order);
		$order->cart->products =& $order->products;
		$pluginClass = hikashop_get('class.plugins');
		$methods = $pluginClass->getMethods('payment');
		$methods[$order->order_payment_id]->payment_params->address_type='';
		$methods[$order->order_payment_id]->payment_params->cancel_url=HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=user&task=edit&user_id='.$user->user_id;
		$methods[$order->order_payment_id]->payment_params->return_url=HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=user&task=edit&user_id='.$user->user_id;
		$methods[$order->order_payment_id]->payment_params->email=$user->user_partner_email;
		$data = hikashop_import('hikashoppayment',$order->order_payment_method);
		$data->onAfterOrderConfirm($order,$methods,$order->order_payment_id);
	}

}
