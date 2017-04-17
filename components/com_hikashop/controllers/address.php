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
class addressController extends hikashopController{

	function __construct($config = array(), $skip = false) {
		parent::__construct($config, $skip);
		$this->modify_views = array('edit');
		$this->add = array('add');
		$this->modify = array('save', 'setdefault');
		$this->delete = array('delete');
	}

	function show() {
		$tmpl = JRequest::getCmd('tmpl', '');
		if($tmpl == 'component') {
			JRequest::setVar('hidemainmenu', 1);
			JRequest::setVar('layout', 'show');
			ob_end_clean();
			parent::display();
			exit;
		}
		parent::show();
	}

	function edit() {
		$tmpl = JRequest::getCmd('tmpl', '');
		$subtask = JRequest::getCmd('subtask', '');
		$addrtype = JRequest::getCmd('address_type', '');
		$config = hikashop_config();
		if($tmpl == 'component' && ($addrtype != '' || $subtask != '') && $config->get('checkout_address_selector', 0)) {
			JRequest::setVar('hidemainmenu', 1);
			JRequest::setVar('layout', 'show');
			JRequest::setVar('edition', true);
			ob_end_clean();
			if(HIKASHOP_J25){
				$app = JFactory::getApplication();
				$messages = $app->getMessageQueue();
				if(!empty($messages)){
					foreach($messages as $message){
						hikashop_display($message['message'],'error');
					}
				}
			}
			parent::display();
			exit;
		}
		parent::edit();
	}

	function listing(){
		$user = JFactory::getUser();
		if ($user->guest) {
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
			return false;
		}
		return parent::listing();
	}

	function delete() {
		$addressdelete = JRequest::getInt('address_id', 0);
		if($addressdelete){
			JRequest::checkToken('request') || jexit( 'Invalid Token' );
			$addressClass = hikashop_get('class.address');
			$oldData = $addressClass->get($addressdelete);
			if(!empty($oldData)){
				$user_id = hikashop_loadUser();
				if($user_id==$oldData->address_user_id){
					$addressClass->delete($addressdelete);
				}
			}
		} else {
			$cid = hikashop_getCID('cid');
			$tmpl = JRequest::getCmd('tmpl', '');
			if(!empty($cid)) {
				JRequest::checkToken('request') || jexit('Invalid Token');
				$addressClass = hikashop_get('class.address');
				$old = $addressClass->get($cid);
				if(!empty($old)) {
					$user_id = hikashop_loadUser(false);
					if($user_id == $old->address_user_id) {
						$ret = $addressClass->delete($cid);
						if($ret && $tmpl == 'component') {
							echo '1';
							exit;
						}
					}
				}
			}
			return $this->show();
		}
		$this->listing();
	}

	function setdefault(){
		$newDefaultId = JRequest::getInt('address_default', 0);
		if($newDefaultId){
			JRequest::checkToken('request') || jexit( 'Invalid Token' );
			$addressClass = hikashop_get('class.address');
			$oldData = $addressClass->get($newDefaultId);
			if(!empty($oldData)){
				$user_id = hikashop_loadUser();
				if($user_id==$oldData->address_user_id){
					$oldData->address_default = 1;
					$addressClass->save($oldData);
				}
			}
		}
		$this->listing();
	}

	function save() {
		JRequest::checkToken('request') || jexit('Invalid Token');

		$app = JFactory::getApplication();
		$addressClass = hikashop_get('class.address');
		$fieldClass = hikashop_get('class.field');
		$task = JRequest::getVar('subtask', '');

		if(!empty($task)) {
			if(substr($task, -8) != '_address')
				$task .= '_address';
			$result = $addressClass->frontSaveForm($task);
			if(!empty($result) && !empty($result[$task])) {
				JRequest::setVar('previous_cid', @$result[$task]->previous_id);
				JRequest::setVar('cid', $result[$task]->id);
				return $this->show();
			}
			return $this->edit();
		}

		$oldData = null;
		$already = @$_REQUEST['address']['address_id'];
		if(!empty($already)){
			$oldData = $class->get($already);
		}
		$addressData = $fieldClass->getInput('address',$oldData);
		$ok = true;

		if(empty($addressData)){
			$ok=false;
		}else{
			$user_id = hikashop_loadUser();
			$addressData->address_user_id=$user_id;
			JRequest::setVar( 'fail', $addressData );
			$address_id = $addressClass->save($addressData);
		}
		if(!$ok || !$address_id){
			$message = '';
			if(isset($addressClass->message)) $message='alert(\''.addslashes($addressClass->message).'\');';
			if(version_compare(JVERSION,'1.6','<')){
				$app = JFactory::getApplication();
				$session = JFactory::getSession();
				$session->set('application.queue', $app->_messageQueue);
			}

			$this->edit();
			return;
		}
		$redirect = JRequest::getWord('redirect','');
		global $Itemid;
		$url = '';
		if(!empty($Itemid)){
			$url='&Itemid='.$Itemid;
		}

		if($redirect=='checkout'){
			$makenew = JRequest::getInt('makenew');
			switch(JRequest::getVar('type')){
				case 'shipping':
					if(JRequest::getVar('action')== 'add' && $makenew){
						$app->setUserState( HIKASHOP_COMPONENT.'.billing_address',$address_id );
					}
					$app->setUserState( HIKASHOP_COMPONENT.'.shipping_address', $address_id );
					break;
				case 'billing':
					if(JRequest::getVar('action')== 'add' && $makenew){
						$app->setUserState( HIKASHOP_COMPONENT.'.shipping_address',$address_id );
					}
					$app->setUserState( HIKASHOP_COMPONENT.'.billing_address', $address_id );
					break;
				default:
					$app->setUserState( HIKASHOP_COMPONENT.'.shipping_address',$address_id );
					$app->setUserState( HIKASHOP_COMPONENT.'.billing_address',$address_id );
					break;
			}

			$app->setUserState(HIKASHOP_COMPONENT.'.shipping_data', null);
			$app->setUserState(HIKASHOP_COMPONENT.'.shipping_method', null);
			$app->setUserState(HIKASHOP_COMPONENT.'.shipping_id', null);
			$app->setUserState(HIKASHOP_COMPONENT.'.payment_data', '');
			$app->setUserState(HIKASHOP_COMPONENT.'.payment_method', '');
			$app->setUserState(HIKASHOP_COMPONENT.'.payment_id', 0);
			if(!$already){
				$controller = hikashop_get('controller.checkout');
				$cart = $controller->initCart();
				$controller->update_cart = true;
				if($cart->has_shipping){
					$controller->before_shipping(true);
				}
				$controller->before_payment(true);
			}
			$url = hikashop_completeLink('checkout&task=step&step='.JRequest::getInt('step',0).$url,false,true);
		}else{
			$url = hikashop_completeLink('address'.$url,false,true);
		}
		ob_clean();
		echo '<html><head><script type="text/javascript">window.parent.location.href=\''.$url.'\';</script></head><body></body></html>';
		exit;
	}
}
