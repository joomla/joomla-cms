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
class CartController extends hikashopController {
	var $modify_views = array();
	var $add = array();
	var $modify = array();
	var $delete = array();

	function __construct($config = array(),$skip=false){
		parent::__construct($config,$skip);
		if(!$skip){
			$this->registerDefaultTask('display');
		}
		$this->display[]='display';
		$this->display[]='convert';
		$this->display[]='newcart';
		$this->display[]='showcarts';
		$this->display[]='showcart';
		$this->display[]='setcurrent';
		$this->display[]='delete';
		$this->display[]='savecart';
		$this->display[]='addtocart';
	}

	function display($cachable = false, $urlparams = array()){
		$cart_type = JRequest::getString('cart_type','cart');
		$empty='';
		jimport('joomla.html.parameter');
		$params = new HikaParameter($empty);
		$js = '';
		$params->set('cart_type',$cart_type);
		$html = trim(hikashop_getLayout('product','cart',$params,$js));
		if(!empty($html)){
			JRequest::setVar('savecart','1');
			echo '<div class="hikashop_cart_display" id="hikashop_cart_display">'.$html.'</div>';
		}
	}

	function convert(){
		$app = JFactory::getApplication();
		$cart_type = JRequest::getString('cart_type','cart');
		$cart_id = JRequest::getInt('cart_id','0');
		$cartClass = hikashop_get('class.cart');
		$cartInfo = $cartClass->loadCart($cart_id);
		$currUser = hikashop_loadUser(true);
		if($cartInfo != null && $currUser->user_cms_id == $cartInfo->user_id){
			$app->setUserState(HIKASHOP_COMPONENT.'.'.$cart_type.'_id', 0);
			$cartClass->convert($cart_id, $cart_type);
			if($cart_type != 'wishlist'){
				JRequest::setVar('cart_type','wishlist');
			}
		}
		JRequest::setVar('cart_id',$cart_id);
		JRequest::setVar('layout', 'showcart');
		return parent::display();
	}

	function newcart(){
		$app = JFactory::getApplication();
		$cartClass = hikashop_get('class.cart');
		$cart_type = JRequest::getString('cart_type','cart');

		$result = $cartClass->setCurrent('0',$cart_type);
		if($result){
			$session = JFactory::getSession();
			$curUser = hikashop_loadUser(true);
			$newCart = new stdClass();
			if($curUser == null)
				$newCart->user_id = 0;
			else
				$newCart->user_id = $curUser->user_cms_id;
			$newCart->session_id = $session->getId();
			$newCart->cart_modified = time();
			$newCart->cart_type = $cart_type;
			$newCart->cart_current = 1;
			$newCart->cart_share = 'nobody';
			$cartClass->save($newCart);

			$app->setUserState(HIKASHOP_COMPONENT.'.'.$cart_type.'_id', '0');
			$app->setUserState(HIKASHOP_COMPONENT.'.'.$cart_type.'_new', '1');

			if($cart_type == 'cart')
				$app->enqueueMessage(JText::sprintf( 'HIKASHOP_CART_CREATED'), 'notice');
			else
				$app->enqueueMessage(JText::sprintf( 'HIKASHOP_WISHLIST_CREATED'), 'notice');
		}
		$this->showcarts();
	}

	function showcarts(){
		JRequest::setVar('layout', 'showcarts');
		return parent::display();
	}

	function showcart(){
		JRequest::setVar('layout', 'showcart');
		return parent::display();
	}

	function addtocart(){
		global $Itemid;
		$app = JFactory::getApplication();
		$from_id = JRequest::getInt('cart_id',0);
		$cart_type = JRequest::getString('cart_type','cart');
		$action = JRequest::getString('action','');
		if($action != 'compare'){
			$cart_type_id = $cart_type.'_id';
			if($cart_type == 'cart') $addTo = 'wishlist';
			else $addTo = 'cart';
			JRequest::setVar('from_id',$from_id);
			if($addTo == 'wishlist' && hikashop_loadUser() == null){
				$app->enqueueMessage(JText::_('LOGIN_REQUIRED_FOR_WISHLISTS'));
			}else{
				$cart_type_id = $addTo.'_id';
				$cart_id = $app->getUserState(HIKASHOP_COMPONENT.'.'.$cart_type_id,'0');
				$app->setUserState(HIKASHOP_COMPONENT.'.'.$cart_type.'_new', '0');
				if(empty($cart_id))$cart_id=0;
				JRequest::setVar('cart_type', $addTo);
				JRequest::setVar($cart_type_id, $cart_id);

				$cartClass = hikashop_get('class.cart');
				$formData = JRequest::getVar('data', array(), '', 'array');
				$i = 0;

				if(isset($formData['products'])){
					$cart_product_id = 0;
					$fromProducts = $cartClass->get(0,true,$cart_type);
					foreach($formData['products'] as $product_id => $product){
						if(!empty($product['checked'])) {
							$i++;
							if(!isset($product['quantity'])) $product['quantity'] = 1;
							$options = array();
							foreach($fromProducts as $fromProduct){
								if($fromProduct->product_id == $product_id){
									$cart_product_id = $fromProduct->cart_product_id;
								}
							}
							foreach($fromProducts as $fromProduct){
								if($fromProduct->cart_product_option_parent_id == $cart_product_id){
									$options[] = $fromProduct->product_id;
								}
							}
							JRequest::setVar('hikashop_product_option',$options);
							$cartClass->update((int)$product_id, (int)$product['quantity'],1);
						}
					}
				}
				if($i == 0){
					$app->enqueueMessage(JText::_('PLEASE_SELECT_A_PRODUCT_FIRST'));
				}
			}

			if($action != '')
				$url = $action;
			else{
				$url = 'cart&task=showcart&cart_type='.$cart_type.'&cart_id='.$from_id.'&Itemid='.$Itemid;
				$url = hikashop_completeLink($url,false,true);
			}
		}
		else{
			$formData = JRequest::getVar('data', array(), '', 'array');
			if(isset($formData['products'])){
				$cidList = '';
				foreach($formData['products'] as $product_id => $product){
					if(!empty($product['checked'])) {
						$cidList .= "&cid[]=".$product_id;
					}
				}
				$url = hikashop_completeLink('product&task=compare'.$cidList.'&Itemid='.$Itemid,false,true);
			}else{
				$url = 'cart&task=showcart&cart_type='.$cart_type.'&cart_id='.$from_id.'&Itemid='.$Itemid;
				$url = hikashop_completeLink($url,false,true);
			}
		}

		$this->setRedirect($url);
	}

	function savecart(){
		$app = JFactory::getApplication();
		$cartClass = hikashop_get('class.cart');
		$user = JFactory::getUser();
		$session = JFactory::getSession();

		$formData = JRequest::getVar('data', array(), '', 'array');
		$cart_id = JRequest::getInt('cart_id','0');
		$cart_type = JRequest::getString('cart_type','cart');
		$cart_name = JRequest::getString('cart_name','');
		$cart_share = JRequest::getString('cart_share','nobody');
		if($cart_share == 'email'){
			$cart_share = JRequest::getString('hikashop_wishlist_token','nobody');
		}

		$cartClass->cart_type = $cart_type;
		$cartInfo = $cartClass->loadCart($cart_id);
		$currUser = hikashop_loadUser(true);

		if($cartInfo != null && ((isset($currUser->user_cms_id) && $currUser->user_cms_id == $cartInfo->user_id) || $session->getId() == $cartInfo->session_id)){
			$cart= new stdClass();
			$cart->cart_id = $cart_id;
			$cart->user_id = $user->id;
			$cart->cart_modified = time();
			$cart->session_id = $session->getId();
			$cart->cart_type = $cart_type;
			$cart->cart_name = $cart_name;
			$cart->cart_share = $cart_share;
			$status = $cartClass->save($cart);

			$formData = JRequest::getVar( 'data', array(), '', 'array' );
			if($status){
				$cartContent = $cartClass->get($cart_id,false,$cart_type);
				if(!empty($formData)){
					foreach($formData['products'] as $k => $product){
						foreach($cartContent as $l => $content){
							if($content->product_id == $k){
								$formData['products'][$content->cart_product_id] = $product;
								$formData['products'][$content->cart_product_id]['cart_product_quantity'] = $formData['products'][$content->cart_product_id]['quantity'];
								unset($formData['products'][$k]);
							}
						}
					}
					JRequest::setVar($cart_type.'_id',$cart_id);
					JRequest::setVar('cart_type',$cart_type);
					$cartClass->update($formData['products'],0,0,'item');
				}
			}
		}
		$this->showcart();
	}

	function setcurrent(){
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$cart_id = JRequest::getVar('cart_id','0');
		$cart_type = JRequest::getString('cart_type','cart');
		$cartClass = hikashop_get('class.cart');
		$cartClass->cart_type = $cart_type;
		$cartInfo = $cartClass->loadCart($cart_id);
		$currUser = hikashop_loadUser(true);
		if($cartInfo != null && $currUser->user_cms_id == $cartInfo->user_id){
			$result = $cartClass->setCurrent($cart_id, $cart_type);
			if($result)$app->setUserState(HIKASHOP_COMPONENT.'.'.$cart_type.'_id', $cart_id);
		}
		JRequest::setVar('layout', 'showcarts');
		return parent::display();
	}

	function delete(){ //delete a cart with the id given
		$cart_id = JRequest::getInt('cart_id','0');
		$cart_type = JRequest::getString('cart_type','cart');
		$cartClass = hikashop_get('class.cart');
		$cartClass->cart_type = $cart_type;
		$cartInfo = $cartClass->loadCart($cart_id);
		$currUser = hikashop_loadUser(true);
		if($cartInfo != null && $currUser->user_cms_id == $cartInfo->user_id){
			$app = JFactory::getApplication();
			if($app->getUserState(HIKASHOP_COMPONENT.'.'.$cart_type.'_id') == $cart_id){
				$app->setUserState(HIKASHOP_COMPONENT.'.'.$cart_type.'_id', '0');
			}
			$cartClass->delete($cart_id, 'old');
		}
		$this->showcarts();
	}
}
