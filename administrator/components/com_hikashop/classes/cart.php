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
class hikashopCartClass extends hikashopClass {
	var $tables = array('cart_product','cart');
	var $pkeys = array('cart_id','cart_id');
	var $options = array();
	var $new_quantities = array();
	var $insertedIds = array();
	var $cart_type = 'cart';

	function hasCart($cart_id = 0) {
		$this->loadCart($cart_id);
		return (!empty($this->cart->cart_id));
	}

	function loadCart($cart_id = 0, $reset = false) {
		static $carts = array();
		if($reset){
			$carts = array();
			$this->cart_id = 0;
			$this->cart = null;
			return true;
		}

		$this->filters = array();
		$app = JFactory::getApplication();
		if(empty($cart_id) || $cart_id == 0){
			$this->cart_id = $app->getUserState( HIKASHOP_COMPONENT.'.'.$this->cart_type.'_id', 0, 'int' );
		} else {
			$this->cart_id = $cart_id;
		}
		if(!isset($this->cart) || is_null($this->cart))
			$this->cart = new stdClass();
		$this->cart->cart_id = $this->cart_id;

		if(!empty($this->cart_id)){
			$this->filters[]='a.cart_id = '.(int)$this->cart_id;
		}else{
			$user = JFactory::getUser();
			if(!empty($user->id)) {
				$this->filters[] = 'a.user_id = '.(int)$user->id;
			}
			$session = JFactory::getSession();
			if($session->getId()) {
				$this->filters[] = 'a.session_id = '.$this->database->Quote($session->getId());
			}
		}
		$filter='';
		if(!empty($this->filters)) $filter = "(".implode(' OR ',$this->filters).")";

		if($this->cart_type){
			if(!empty($filter)) $filter .= " AND ";
			$filter .= 'a.cart_type = '.$this->database->Quote($this->cart_type);
		}
		if(!empty($carts[$filter])){
			$this->cart =& $carts[$filter];
		}else{

			if(!empty($filter)){
				$query='SELECT a.* FROM '.hikashop_table('cart').' AS a WHERE '.$filter.' ORDER BY a.cart_modified DESC LIMIT 1';
				$this->database->setQuery($query);
				$this->cart = $this->database->loadObject();

				if(!empty($this->cart->cart_params)) {
					$this->cart->cart_params = json_decode($this->cart->cart_params);
				}
				if(empty($cart_id) && empty($this->cart_id) && !empty($this->cart)){
					$app->setUserState( HIKASHOP_COMPONENT.'.'.$this->cart_type.'_id', $this->cart->cart_id );
				}
				$this->cart_id = @$this->cart->cart_id;
				if(empty($cart_id) && empty($this->cart)){
					$app->setUserState( HIKASHOP_COMPONENT.'.'.$this->cart_type.'_id', 0 );
				}
			} else {
				$this->cart = null;
			}
			$carts[$filter] =& $this->cart;
		}
		return $this->cart;
	}

	function get($cart_id = 0, $keepEmptyCart = false, $cart_type = 'cart') {
		$result = false;
		if(!isset($this->cart_type) || ($this->cart_type == 'cart' && $cart_type == 'wishlist'))
			$this->cart_type = $cart_type;

		if(!$this->hasCart($cart_id))
			return $result;

		$app = JFactory::getApplication();
		$filters = array(
			'b.cart_id = '.(int)$this->cart->cart_id,
			'b.product_id > 0'
		);
		hikashop_addACLFilters($filters, 'product_access', 'c');

		$query = 'SELECT a.*,b.*,c.* FROM '.hikashop_table('cart').' AS a '.
			' LEFT JOIN '.hikashop_table('cart_product').' AS b ON a.cart_id = b.cart_id '.
			' LEFT JOIN '.hikashop_table('product').' AS c ON b.product_id = c.product_id '.
			' WHERE (' . implode(') AND (', $filters) . ') '.
			' ORDER BY b.cart_product_modified ASC';
		$this->database->setQuery($query);
		$products = $this->database->loadObjectList('cart_product_id');
		if(empty($products) && !$keepEmptyCart && !$app->getUserState(HIKASHOP_COMPONENT.'.'.$cart_type.'_new', '0')) {
			$this->delete($this->cart->cart_id);
			$app->setUserState(HIKASHOP_COMPONENT.'.'.$cart_type.'_id', 0);
			$app->setUserState(HIKASHOP_COMPONENT.'.coupon_code', '');
			$this->cart = null;
		}
		return $products;
	}

	function addToCartFromFields(&$entriesData,&$fields){
		$this->resetCart(false);
		$app = JFactory::getApplication();

		$productsToAdd = array();
		$coupons = array();
		foreach($entriesData as $entryData){
			foreach(get_object_vars($entryData) as $namekey=>$value){
				foreach($fields as $field){
					if($field->field_namekey==$namekey){
						$ok = false;
						if(!empty($field->field_options) && !is_array($field->field_options)) $field->field_options = unserialize($field->field_options);
						if(!empty($field->field_options['product_id'])){
							if(is_numeric($value) && is_numeric($field->field_options['product_value'])){
								if( $value === $field->field_options['product_value'] ){
									$ok = true;
								}
							}elseif(is_string($value) && !empty($field->field_options['product_value']) && is_array($field->field_options['product_value']) && in_array($value,$field->field_options['product_value'])){
								$ok = true;
							}elseif($value == $field->field_options['product_value']){
								$ok = true;
							}

							if($ok){
								$id = $field->field_options['product_id'];
								if(empty($productsToAdd[$id])){
									$productsToAdd[$id]=1;
								}else{
									$productsToAdd[$id]++;
								}
							}
						}

						if($field->field_type=='coupon' && !empty($field->coupon[$value])){
							$coupons[] = $field->coupon[$value];
						}
						break;
					}
				}
			}
		}
		if(!empty($productsToAdd)){
			$array = array();
			foreach($productsToAdd as $id => $qty){
				$this->updateEntry($qty,$array,$id,0,false);
			}
		}
		if(count($coupons)>1){
			$total = 0.0;
			$currency = hikashop_getCurrency();
			$currencyClass = hikashop_get('class.currency');
			$discountClass = hikashop_get('class.discount');
			foreach($coupons as $item){
				$currencyClass->convertCoupon($item,$currency);
				$total = $total + $item->discount_flat_amount;
				$this->database->setQuery('UPDATE '.hikashop_table('discount').' SET discount_used_times=discount_used_times+1 WHERE discount_id = '.$item->discount_id);
				$this->database->query();
			}
			$newCoupon = new stdClass();
			$newCoupon->discount_type='coupon';
			$newCoupon->discount_currency_id = $currency;
			$newCoupon->discount_flat_amount = $total;
			$newCoupon->discount_quota = 1;
			jimport('joomla.user.helper');
			$newCoupon->discount_code = JUserHelper::genRandomPassword(30);
			$newCoupon->discount_published = 1;
			$discountClass->save($newCoupon);
			$coupon = $newCoupon;
		}elseif(count($coupons)==1){
			$coupon = reset($coupons);
		}

		if(!empty($coupon)){
			$this->update($coupon->discount_code,1,0,'coupon',false);
		}
		$this->loadCart(0,true);
		$app->setUserState( HIKASHOP_COMPONENT.'.shipping_method',null);
		$app->setUserState( HIKASHOP_COMPONENT.'.shipping_id',null);
		$app->setUserState( HIKASHOP_COMPONENT.'.shipping_data',null);
	}

	function save(&$cart) {
		$app = JFactory::getApplication();
		$currUser = hikashop_loadUser(true);
		$user = JFactory::getUser();
		$session = JFactory::getSession();
		if(!isset($cart->user_id) || !$app->isAdmin() || $currUser->user_cms_id == $cart->user_id || (isset($cart->session_id) && $session->getId() == $cart->session_id)) {
			if(isset($cart->cart_name))
				$cart->cart_name = strip_tags($cart->cart_name);
			if(isset($cart->cart_share))
				$cart->cart_share = strip_tags($cart->cart_share);
			if(isset($cart->cart_type))
				$cart->cart_type = strip_tags($cart->cart_type);
			if(!empty($cart->cart_params) && !is_string($cart->cart_params))
				$cart->cart_params = json_encode($cart->cart_params);
			if(!isset($cart->user_id) && empty($cart->cart_name))
				$cart->cart_name = '';
			if(!isset($cart->cart_id) && empty($cart->cart_coupon))
				$cart->cart_coupon = '';
			$cart_id = parent::save($cart);
		} elseif($app->isAdmin()) {
			if(isset($cart->cart_name))
				$cart->cart_name = strip_tags($cart->cart_name);
			if(isset($cart->user_id))
				$cart->user_id = (int)$cart->user_id;
			if(isset($cart->cart_type))
				$cart->cart_type = strip_tags($cart->cart_type);
			$cart_id = parent::save($cart);
		} else {
			$cart_id = $cart->cart_id;
		}
		return $cart_id;
	}

	function addAlias($name){
		$alias = strip_tags($name);
		$jConfig = JFactory::getConfig();
		if(!$jConfig->get('unicodeslugs')){
			$lang = JFactory::getLanguage();
			$alias = $lang->transliterate($alias);
		}
		$app = JFactory::getApplication();
		if(method_exists($app,'stringURLSafe')){
			$alias = $app->stringURLSafe($alias);
		}else{
			$alias = JFilterOutput::stringURLSafe($alias);
		}
		return $alias;
	}

	function initCart() {
		$cart = new stdClass();
		$cart->cart_type = JRequest::getString('cart_type','cart');
		$cart->cart_id = JRequest::getString($cart->cart_type.'_id','0');
		$cart->cart_modified = time();

		if(!empty($this->cart->cart_id))
			$cart->cart_id = $this->cart->cart_id;

		$app = JFactory::getApplication();
		if(!$app->isAdmin()) {
			$user = JFactory::getUser();
			$session = JFactory::getSession();
			if(!empty($user->id))
				$cart->user_id = $user->id;
			if($session->getId())
				$cart->session_id = $session->getId();

			if(!empty($this->cart->cart_id) && $this->cart->cart_id != $cart->cart_id) {
				$this->setCurrent($this->cart->cart_id,$cart->cart_type);
			}
		}

		if(empty($this->cart))
			$this->cart = new stdClass();
		$this->cart->cart_id = (int)$this->save($cart);
		$cart->cart_id = $this->cart->cart_id;

		return $cart;
	}

	function resetCart($reset = true) {
		$cartContent =& $this->get();
		$cart = $this->initCart();
		$app = JFactory::getApplication();
		$app->setUserState(HIKASHOP_COMPONENT.'.entries_fields', null);

		if(!empty($cartContent)) {
			$query = 'DELETE FROM '.hikashop_table('cart_product').' WHERE cart_id = '.$cart->cart_id;
			$this->database->setQuery($query);
			$this->database->query();
		}

		if(!empty($this->cart->cart_coupon)) {
			$app->setUserState(HIKASHOP_COMPONENT.'.coupon_code', $this->cart->cart_coupon);
			$this->update('', 0, 0, 'coupon', false);
			$app->setUserState(HIKASHOP_COMPONENT.'.coupon_code', '');
		}

		$app->setUserState(HIKASHOP_COMPONENT.'.shipping_method', null);
		$app->setUserState(HIKASHOP_COMPONENT.'.shipping_id', null);
		$app->setUserState(HIKASHOP_COMPONENT.'.shipping_data', null);

		if($reset)
			$this->loadCart(0,true);
	}

	function update($product_id, $quantity = 1, $add = 0, $type = 'product', $resetCartWhenUpdate = true, $force = false) {
		if($type == 'product' && empty($product_id))
			return false;

		$app = JFactory::getApplication();

		$this->cart_type = JRequest::getString('cart_type', 'cart');

		$cart_id = $this->cart_type.'_id';
		$this->$cart_id = JRequest::getInt($cart_id, '0');

		$cartInfo = $this->loadCart($this->$cart_id);

		$currUser = hikashop_loadUser(true);
		$session = JFactory::getSession();
		if(!$app->isAdmin() && !empty($cartInfo->cart_id) && @$currUser->user_cms_id != @$cartInfo->user_id && $session->getId() != @$cartInfo->session_id)
			return false;

		$this->from_id = JRequest::getString('from_id', '0');
		$keepEmptyCart = false;
		if($type == 'cart') {
			$type = 'product';
			$keepEmptyCart = true;
			if(!isset($this->cart))
				$this->cart = new stdClass();
			$this->cart->cart_id = $this->$cart_id;
		}

		if($this->cart_type != 'cart')
			$resetCartWhenUpdate = false;

		$cartContent = $this->get($this->$cart_id,$keepEmptyCart,$this->cart_type);

		$entries = $app->getUserState(HIKASHOP_COMPONENT.'.entries_fields');
		if(!empty($entries) && in_array($type, array('product', 'item')))
			return false;

		$cart = $this->initCart();

		JRequest::setVar('new_'.$cart_id, $cart->cart_id);
		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$do = true;
		$dispatcher->trigger('onBeforeCartUpdate', array(&$this, &$cart, &$product_id, &$quantity, &$add, &$type, &$resetCartWhenUpdate, &$force, &$do ));
		if(!$do)
			return false;

		if(!$app->isAdmin() && $cart->cart_id) {
			$app->setUserState(HIKASHOP_COMPONENT.'.'.$cart_id, $cart->cart_id);
		}

		if(in_array($type, array('product', 'item'))) {
			$severalMainProducts = false;
			if(!is_array($product_id)) {
				$pid =$product_id;
				$this->mainProduct = $product_id;
				$product_id = array($product_id => $quantity);

				$options = JRequest::getVar('hikashop_product_option', array(), '', 'array');
				if(!empty($options)&& is_array($options)) {
					foreach($options as $optionElement) {
						$this->options[$optionElement] = $pid;
						$product_id[$optionElement] = $quantity;
					}
				}
			} else {
				$severalMainProducts = true;
			}

			$updated = false;

			foreach($product_id as $id => $infos) {
				if($severalMainProducts)
					$this->mainProduct = $id;
				$res = $this->updateEntry($infos, $cartContent, (int)$id, $add, false, $type, $force);

				if(is_numeric($id) && $res)
					$updated = true;
			}

			if($updated && $resetCartWhenUpdate) {
				$this->loadCart(0,true);
				$app->setUserState(HIKASHOP_COMPONENT.'.shipping_method', null);
				$app->setUserState(HIKASHOP_COMPONENT.'.shipping_id', null);
				$app->setUserState(HIKASHOP_COMPONENT.'.shipping_data', null);
			}

			$dispatcher->trigger('onAfterCartUpdate',array( &$this, &$cart, &$product_id, &$quantity, &$add, &$type, &$resetCartWhenUpdate, &$force, &$updated ));
			return $updated;
		}

		$new_coupon = '';
		if($quantity)
			$new_coupon = $product_id;

		$old_coupon = $app->getUserState(HIKASHOP_COMPONENT.'.coupon_code', '');
		if(!empty($old_coupon) && $old_coupon == $new_coupon)
			return false;

		$cart->cart_coupon = $new_coupon;
		$this->cart->cart_coupon = $new_coupon;
		if($this->save($cart)) {
			if(!$quantity && !empty($product_id)) {
				$message = JText::_('COUPON_REMOVED');
				$app->enqueueMessage( $message );
			}
			if($resetCartWhenUpdate) {
				$this->loadCart(0,true);
				$app->setUserState(HIKASHOP_COMPONENT.'.coupon_code', $new_coupon);
			}
			$return = true;
		} else {
			$cart->cart_coupon = '';
			$this->cart->cart_coupon = '';
			$return = false;
		}

		$dispatcher->trigger('onAfterCartUpdate', array( &$this, &$cart, &$product_id, &$quantity, &$add, &$type, &$resetCartWhenUpdate, &$force, &$return ));
		return $return;
	}

	function updateEntry($quantity, &$cartContent, $product_id, $add, $resetCartWhenUpdate = true, $type = 'product', $force = false) {
		if(empty($product_id))
			return false;
		if($type=='product'){
			$id = 0;
			if(!empty($cartContent)){
				$do = true;
				static $already_done = false;
				if((!$already_done || $force) && hikashop_level(2)){
					$already_done = true;

					if($force>=2){
						$formData = @$_REQUEST['data'];
					}else{
						$formData = JRequest::getVar( 'data', array(), '', 'array' );
					}
					if(!empty($formData['item']) || !empty($_FILES)){
						$fieldClass = hikashop_get('class.field');
						$element = new stdClass();
						$element->product_id = $product_id;
						$data = $fieldClass->getInput('item',$element,true,'data',(bool)$force);

						if($data===false){
							$this->errors = true;
							return false;
						}
						if(!empty($data)){
								$doCartID = null;
							$do = false;
							foreach($cartContent as $cart_product_id => $prod){
								if($prod->product_id == $product_id ){
									$same = true;
									foreach(get_object_vars($data) as $field => $var){
										if($prod->$field!=$var){
											$same = false;
										}
									}
									if($same){
										$do = true;
										$doCartID = $cart_product_id;
									}
								}
							}
						}
					}
				}

				if($do){
					foreach($cartContent as $cart_product_id => $prod){
						if (!empty($doCartID)) {
							if ($doCartID != $cart_product_id) continue;
						}
						if($prod->product_id==$product_id){
							if((@$this->mainProduct == $product_id && $prod->cart_product_option_parent_id == 0) || isset($this->mainProductCartId) && $this->mainProductCartId==@$prod->cart_product_option_parent_id && @$this->mainProduct == @$cartContent[@$prod->cart_product_option_parent_id]->product_id){
								$already = array();
								foreach($cartContent as $optionElement){
									if($this->mainProduct==$product_id && $optionElement->cart_product_option_parent_id==$cart_product_id){
										$already[]=$optionElement->product_id;
										continue;
									}
									if(@$this->mainProduct==@$cartContent[@$prod->cart_product_option_parent_id]->product_id && $optionElement->cart_product_option_parent_id==@$prod->cart_product_option_parent_id){
										$already[]=$optionElement->product_id;
										continue;
									}
								}
								$ok = true;
								if(!empty($already)){
									foreach($already as $a){
										if(!isset($this->options[$a])){
											$ok = false;
										}
									}
									foreach($this->options as $o=>$a){
										if(!in_array($o,$already)){
											$ok = false;
										}
									}
								}elseif(count($this->options)){
									$ok = false;
								}

								if($ok){
									$id = $cart_product_id;
									if(@$this->mainProduct==$product_id){
										$this->mainProductCartId = $cart_product_id;
									}
								}
								break;
							}
						}
					}
				}
			}
			$quantity=(int)$quantity;
		}else{
			$id = $product_id;
			$product_id = (int)@$cartContent[$id]->product_id;
			if(is_array($quantity)){
				$quantity=(int)@$quantity['cart_product_quantity'];
			}else{
				$quantity=(int)@$quantity;
			}
		}

		if($quantity){
			if(!empty($cartContent) && in_array($id,array_keys($cartContent))){
				if($add){
					$quantity+=$cartContent[$id]->cart_product_quantity;
					$add=0;
				}elseif($quantity==$cartContent[$id]->cart_product_quantity){
					return false;
				}
				if($this->cart_type != 'wishlist')
					$this->_checkQuantity($cartContent[$id],$quantity,$cartContent,(int)$cartContent[$id]->cart_product_id);

				if($quantity){
					$query = 'UPDATE '.hikashop_table('cart_product').' SET cart_product_quantity='.(int)$quantity.' WHERE cart_product_id='.(int)$cartContent[$id]->cart_product_id;
					$this->database->setQuery($query);
					$this->database->query();
					if($resetCartWhenUpdate){
						$this->loadCart(0,true);
						$app = JFactory::getApplication();
						$app->setUserState( HIKASHOP_COMPONENT.'.shipping_method',null);
						$app->setUserState( HIKASHOP_COMPONENT.'.shipping_id',null);
						$app->setUserState( HIKASHOP_COMPONENT.'.shipping_data',null);
					}
					return true;
				}else{
					$this->errors = true;
					return false;
				}
			}elseif(!empty($this->cart->cart_id) && !empty($product_id)){
				$class = hikashop_get('class.product');
				$product = $class->get($product_id);
				$parent = 0;
				if($this->cart_type != 'wishlist')
					$this->_checkQuantity($product,$quantity,$cartContent,-1);

				if(!isset($this->from_id))
					$this->from_id = 0;
				if(!$quantity) {
					$this->errors = true;
					return false;
				}

				static $delay = 0;
				static $already_done2 = false;

				if($product->product_type == 'variant') {
					$query = 'INSERT INTO '.hikashop_table('cart_product').' (cart_id,cart_product_modified,product_id,cart_product_parent_id,cart_product_quantity,cart_product_wishlist_id) VALUES ( '.(int)$this->cart->cart_id.','.(time()+$delay).','.(int)$product->product_parent_id.',0,0,'.(int)$this->from_id.')';
					$this->database->setQuery($query);
					$this->database->query();
					$parent = (int)$this->database->insertid();
					$this->insertedIds[(int)$product->product_parent_id] = $parent;
					$delay++;
				}

				$optionElement = 0;
				if(!empty($this->insertedIds[(int)@$this->mainProduct])) {
					$optionElement = (int)$this->insertedIds[$this->mainProduct];
				}

				$fields = array('cart_id','cart_product_modified','product_id','cart_product_parent_id','cart_product_quantity','cart_product_option_parent_id','cart_product_wishlist_id');
				$values = array((int)$this->cart->cart_id,(time()+$delay),(int)$product_id,$parent,(int)$quantity,$optionElement,(int)$this->from_id);
				if((!$already_done2 || $force) && hikashop_level(2)) {
					$already_done2 = true;

					if($force >= 2)
						$formData = @$_REQUEST['data'];
					else
						$formData = JRequest::getVar( 'data', array(), '', 'array' );

					if(!empty($formData['item']) || !empty($_FILES)) {
						if(empty($data)) {
							$fieldClass = hikashop_get('class.field');
							$element = new stdClass();
							$element->product_id = $product_id;
							$data = $fieldClass->getInput('item',$element,true,'data',(bool)$force);
							if($data === false) {
								$this->errors = true;
								return false;
							}
						}

						if(!empty($data)) {
							foreach(get_object_vars($data) as $field => $var) {
								$fields[] = '`'.$field.'`';
								$values[] = $this->database->Quote($var);
							}
						}
					}
				}

				$query = 'INSERT INTO '.hikashop_table('cart_product').' ('.implode(',', $fields).') VALUES ('.implode(',', $values).')';
				$this->database->setQuery($query);
				$this->database->query();
				$cartId = (int)$this->database->insertid();
				$this->insertedIds[(int)$product_id] = $cartId;
				$delay++;
				if($resetCartWhenUpdate) {
					$this->loadCart(0,true);
					$app = JFactory::getApplication();
					$app->setUserState(HIKASHOP_COMPONENT.'.shipping_method', null);
					$app->setUserState(HIKASHOP_COMPONENT.'.shipping_id', null);
					$app->setUserState(HIKASHOP_COMPONENT.'.shipping_data', null);
				}
				return true;
			}
		} else if ($this->cart_type == 'wishlist' && !JRequest::getInt('delete','0') && !empty($cartContent[$id]->cart_product_id)) {
			$query = 'UPDATE '.hikashop_table('cart_product').' SET cart_product_quantity='.(int)$quantity.' WHERE cart_product_id='.(int)$cartContent[$id]->cart_product_id;
			$this->database->setQuery($query);
			$this->database->query();
			$add = 1;
		}

		if(!$add && !empty($cartContent) && in_array($id,array_keys($cartContent))) {
			$query = 'DELETE FROM '.hikashop_table('cart_product').' WHERE cart_product_id = '.$cartContent[$id]->cart_product_id. ' OR cart_product_parent_id = '.$id.' OR cart_product_id = '.$cartContent[$id]->cart_product_parent_id.' OR cart_product_option_parent_id='.$cartContent[$id]->cart_product_id;
			$this->database->setQuery($query);
			$this->database->query();

			if($resetCartWhenUpdate) {
				$this->loadCart(0,true);
				$app = JFactory::getApplication();
				$app->setUserState( HIKASHOP_COMPONENT.'.shipping_method',null);
				$app->setUserState( HIKASHOP_COMPONENT.'.shipping_id',null);
				$app->setUserState( HIKASHOP_COMPONENT.'.shipping_data',null);
			}
			return true;
		}
		return false;
	}

	function _checkQuantity(&$product,&$quantity,&$cartContent,$cart_product_id_for_product) {
		if($quantity < 0)
			$quantity = 0;

		$config =& hikashop_config();
		$product->wanted_quantity = $wantedQuantity = $quantity;
		if(!empty($this->options[$product->product_id])) {
			$parent = $this->options[$product->product_id];
			$group_options = (int)$config->get('group_options', 0);
			if(isset($this->new_quantities[$parent]) && $quantity > $this->new_quantities[$parent] && $group_options == 1) {
				$quantity = $this->new_quantities[$parent];
			}
		}

		$item_limit = (int)$config->get('cart_item_limit', 0);
		if(!empty($item_limit) && hikashop_level(1)) {
			$current_items = 0;
			if(!empty($cartContent)) {
				foreach($cartContent as $element) {
					if($element->product_id != $product->product_id)
						$current_items += (int)$element->cart_product_quantity;
				}
			}
			$possible_quantity = $item_limit - $current_items;

			if($quantity > $possible_quantity) {
				if($possible_quantity < 0)
					$possible_quantity = 0;
				$quantity = $possible_quantity;
			}
		}

		$database = JFactory::getDBO();
		if(hikashop_level(1)) {
			$productIds = array((int)$product->product_id);
			if( $product->product_parent_id > 0 ) {
				$productIds[] = (int)$product->product_parent_id;
			}
			$productCartIds = array((int)$product->product_id);
			if( is_array($cartContent) ) {
				foreach($cartContent as $cart_product_id => $prod){
					if( !in_array($prod->product_id, $productCartIds) ) {
						$productCartIds[] = (int)$prod->product_id;
					}
					if( $prod->product_parent_id > 0 && !in_array($prod->product_parent_id, $productCartIds) ) {
						$productCartIds[] = (int)$prod->product_parent_id;
					}
				}
			}
			$database->setQuery('SELECT category_id, product_id FROM '.hikashop_table('product_category').' WHERE product_id IN ('.implode(',',$productCartIds).');');
			$categoryIds = array();
			$cartCategoryLink = array();
			$catIds = '';
			$ret = $database->loadObjectList();
			$acceptedCategory = array();
			foreach($ret as $c) {
				if($c->product_id == $product->product_id || $c->product_id == $product->product_parent_id) {
					$acceptedCategory[$c->category_id] = $c->category_id;
				}
			}
			foreach($ret as $c) {
				if(isset($acceptedCategory[$c->category_id])) {
					$categoryIds[] = (int)$c->category_id;
					if(!isset($cartCategoryLink[$c->product_id])) {
						$cartCategoryLink[$c->product_id] = array($c->category_id);
					} else {
						$cartCategoryLink[$c->product_id][] = $c->category_id;
					}
				}
			}
			unset($acceptedCategory);
			unset($c);
			unset($ret);

			$filters = array();
			hikashop_addACLFilters($filters,'limit_access','a');

			$query = ' FROM '.hikashop_table('limit').' AS a WHERE a.limit_published = 1 AND (a.limit_start = 0 OR a.limit_start <= '.time().') AND (a.limit_end = 0 OR a.limit_end >= '.time().') AND a.limit_product_id IN ('.implode(',',$productIds).',0)';
			if( count($categoryIds) > 0 )
				$catIds = implode(',',$categoryIds).',';
			$query .= ' AND limit_category_id IN ('.$catIds.'0)';
			$filters = implode(' AND ', $filters);
			if( !empty($filters) ) {
				$query .= ' AND ' . $filters;
			}
			$database->setQuery('SELECT count(*)'.$query );
			$limiters = $database->loadResult();

			if( $limiters > 0 ) {
				$database->setQuery('SELECT a.*'.$query);
				$limiters = $database->loadObjectList();
				$periodicity = array(
					'forever' => 0,
					'yearly' => 1,
					'quarterly' => 2,
					'monthly' => 3,
					'weekly' => 4,
					'daily' => 5,
					'cart' => 6
				);
				$limiterTypes = array('price' => false, 'quantity' => false, 'weight' => false );
				$dateLimiter = 0;
				$categoryIds = array();
				$limit_statuses = array();

				foreach($limiters as $limiter) {
					if( $limiter->limit_category_id > 0 ) {
						$categoryIds[] = (int)$limiter->limit_category_id;
					}
					$limiterTypes[ $limiter->limit_type ] = true;
					$dateLimiter = ($dateLimiter > 0 && $dateLimiter < $periodicity[$limiter->limit_periodicity])?$dateLimiter:$periodicity[$limiter->limit_periodicity];
					$statuses = explode(',',$limiter->limit_status);
					foreach($statuses as $s) {
						if(!empty($s))
							$limit_statuses[$s] = $s;
					}
					unset($s);
					unset($statuses);
				}

				$d = getdate();
				$baseDates = array(
					0 => 0,
					1 => mktime(0,0,0,1,1,$d['year']),
					2 => mktime(0,0,0,$d['mon']-(($d['mon']-1)%4),1,$d['year']),
					3 => mktime(0,0,0,$d['mon'],1,$d['year']),
					4 => mktime(0,0,0,$d['mon'],$d['mday']-$d['wday'],$d['year']),
					5 => mktime(0,0,0,$d['mon'],$d['mday'],$d['year']),
					6 => -1
				);

				$user = JFactory::getUser();
				if(!empty($user->id) && $baseDates[$dateLimiter] >= 0) {
					$query = 'SELECT a.order_product_id, a.product_id, a.order_product_quantity, a.order_product_price, a.order_product_tax, b.order_currency_id, b.order_created, b.order_status, c.product_parent_id, d.category_id FROM ';
					$query .= hikashop_table('order_product').' AS a';
					$query .= ' INNER JOIN '.hikashop_table('order').' AS b ON a.order_id = b.order_id ';
					if( count($limit_statuses) > 0 ) {
						$query .= "AND b.order_status IN ('". implode("','",$limit_statuses) ."')";
					}
					$query .= ' AND b.order_user_id = ' . (int)hikashop_loadUser();
					$query .= ' AND b.order_created >= ' . $baseDates[$dateLimiter];
					$query .= ' INNER JOIN '.hikashop_table('product').' AS c ON (a.product_id = c.product_id) OR (a.product_id = c.product_parent_id)';
					$query .= ' INNER JOIN '.hikashop_table('product_category').' AS d ON (c.product_parent_id = 0 AND c.product_id = d.product_id) OR (c.product_parent_id = d.product_id) ';
					$query .= ' WHERE a.product_id IN ('.implode(',',$productIds).')';
					if( count($categoryIds) > 0 )
						$query .= 'OR category_id IN ('.implode(',',$categoryIds).')';
					$query .= ';';

					$database->setQuery($query);
					$rows = $database->loadObjectList();
					$productIds = array();
					foreach($rows as $p) {
						if(empty($p->product_parent_id))
							$productIds[$p->product_id] = $p->product_id;
						else
							$productIds[$p->product_parent_id] = $p->product_parent_id;
					}
				} else {
					$rows = array();
					$productIds = array();
				}

				if( $limiterTypes['weight'] || $limiterTypes['price'] ) {
					if(count($rows)){
						$productClass = hikashop_get('class.product');
						$productClass->getProducts( $productIds );
					}
					$fullcart = $this->loadFullCart(false, true, true);
				}
				JPluginHelper::importPlugin( 'hikashop' );
				$dispatcher = JDispatcher::getInstance();
				foreach($limiters as $limiter) {
					$baseDate = $baseDates[ $periodicity[ $limiter->limit_periodicity ] ];
					$value = 0;
					foreach($rows as $r) {
						if( $baseDate >= 0 && $r->order_created >= $baseDate && (empty($limiter->limit_status) || strpos(','.$limiter->limit_status.',', ','.$r->order_status.',') !== false) ) {
							if(
								($limiter->limit_product_id == 0 || ($limiter->limit_product_id == $r->product_id) || $limiter->limit_product_id == $r->product_parent_id)
									||
								($limiter->limit_category_id == 0 || ($limiter->limit_category_id == $r->category_id))
							) {
								switch($limiter->limit_type) {
									case 'quantity':
										$value += $r->order_product_quantity;
										break;
									case 'price':
										$dispatcher->trigger( 'onBeforeCalculateProductPriceForQuantityInOrder', array( &$r) );
										if(function_exists('hikashop_product_price_for_quantity_in_order')){
											hikashop_product_price_for_quantity_in_order($r);
										}else{
											$r->order_product_total_price_no_vat = $r->order_product_price*$r->order_product_quantity;
											$r->order_product_total_price = ($r->order_product_price+$r->order_product_tax)*$r->order_product_quantity;
										}
										$dispatcher->trigger( 'onAfterCalculateProductPriceForQuantityInOrder', array( &$r) );
										$value += $r->order_product_total_price;
									case 'weight':
										$id = ($r->product_parent_id == 0)?$r->product_id:$r->product_parent_id;
										if(!empty($productClass->products[$id])){
											$p =& $productClass->products[$id];
											if(empty($p->product_weight)&& $r->product_parent_id != 0 && !empty($productClass->products[$r->product_parent_id])){
												$p =& $productClass->products[$r->product_parent_id];
											}
											if( $p->product_weight_unit == $limiter->limit_unit ) {
												$value += $p->product_weight * $r->order_product_quantity;
											}
											unset($p);
										}
										break;
								}
							}
						}
					}

					if( isset($fullcart) ) {
						foreach($fullcart->products as $cc ) {
							if($cart_product_id_for_product>0){
								if($cc->cart_product_id == $cart_product_id_for_product){
									continue;
								}
							}

							$id = ($cc->product_parent_id == 0)?$cc->product_id:$cc->product_parent_id;
							if( ($limiter->limit_product_id == 0 || $limiter->limit_product_id == $id) && ($limiter->limit_category_id == 0 || (isset($cartCategoryLink[$id]) && is_array($cartCategoryLink[$id]) && in_array($limiter->limit_category_id, $cartCategoryLink[$id])))) {
								switch($limiter->limit_type) {
									case 'quantity':
										$value += $cc->cart_product_quantity;
										break;
									case 'price':
										if(isset($cc->prices) && is_array($cc->prices) && count($cc->prices)){
											$value += $cc->prices[0]->price_value_with_tax;
										}
										break;
									case 'weight':
										if(isset($cc->product_weight_unit) && $cc->product_weight_unit == $limiter->limit_unit ) {
											$value += $cc->product_weight * $cc->cart_product_total_quantity;
										}
										break;
								}
							}
						}
					} elseif(isset($cartContent) && is_array($cartContent)){
						foreach($cartContent as $cc ) {
							if($cart_product_id_for_product>0){
								if($cc->cart_product_id == $cart_product_id_for_product){
									continue;
								}
							}
							$id = ($cc->product_parent_id == 0)?$cc->product_id:$cc->product_parent_id;
							if( ($limiter->limit_product_id > 0 && $limiter->limit_product_id == $id) || ($limiter->limit_category_id > 0 && isset($cartCategoryLink[$id]) && is_array($cartCategoryLink[$id]) &&  in_array($limiter->limit_category_id, $cartCategoryLink[$id])) ) {
								switch($limiter->limit_type) {
									case 'quantity':
										$value += $cc->cart_product_quantity;
										break;
									case 'price':
										$value += $cc->prices[0]->price_value_with_tax*$cc->cart_product_quantity;
										break;
									case 'weight':
										$value += $cc->product_weight*$cc->cart_product_quantity;
										break;
								}
							}
						}
					}
					if($cart_product_id_for_product==-1 && $product->product_type=="variant"){
						$productClass = hikashop_get('class.product');
						$parent = $productClass->get($product->product_parent_id);
						$ids = array($parent->product_id);
						$currencyClass = hikashop_get('class.currency');
						$config =& hikashop_config();
						$main_currency = (int)$config->get('main_currency',1);
						$currency_id = hikashop_getCurrency();
						if(!in_array($currency_id,$currencyClass->publishedCurrencies())){
							$currency_id = $main_currency;
						}
						$zone_id = hikashop_getZone('shipping');
						if($config->get('tax_zone_type','shipping')=='billing'){
							$tax_zone_id=hikashop_getZone('billing');
						}else{
							$tax_zone_id=$zone_id;
						}
						$discount_before_tax = (int)$config->get('discount_before_tax',0);
						$currencyClass->getPrices($parent,$ids,$currency_id,$main_currency,$zone_id,$discount_before_tax);
						$productClass->checkVariant($product,$parent);

					}
					switch($limiter->limit_type) {
						case 'quantity':
							if( $value + $quantity > $limiter->limit_value ) {
								$quantity = $limiter->limit_value - $value;
							}
							break;
						case 'price':
							$currencyClass = hikashop_get('class.currency');

							$product->cart_product_quantity = $quantity;
							$product->cart_product_total_quantity = $quantity;

							if(!isset($product->prices)){
								$ids = array($product->product_id);
								$config =& hikashop_config();
								$main_currency = (int)$config->get('main_currency',1);
								$currency_id = hikashop_getCurrency();
								if(!in_array($currency_id,$currencyClass->publishedCurrencies())){
									$currency_id = $main_currency;
								}

								$zone_id = hikashop_getZone('shipping');
								if($config->get('tax_zone_type','shipping') == 'billing') {
									$tax_zone_id = hikashop_getZone('billing');
								} else {
									$tax_zone_id = $zone_id;
								}
								$discount_before_tax = (int)$config->get('discount_before_tax',0);
								$currencyClass->getPrices($product, $ids, $currency_id, $main_currency, $tax_zone_id, $discount_before_tax);
							}
							$currencyClass->calculateProductPriceForQuantity($product);
							if(isset($product->prices) && is_array($product->prices) && count($product->prices)){
								if( $value + $product->prices[0]->price_value_with_tax > $limiter->limit_value ) {
									while( $product->cart_product_quantity > 0 && ($value + $product->prices[0]->price_value_with_tax > $limiter->limit_value) ) {
										$product->cart_product_quantity--;
										$currencyClass->calculateProductPriceForQuantity($product);
									}
									$quantity = $product->cart_product_quantity;
								}
							}


							break;
						case 'weight':
							if( $product->product_weight > 0 && $product->product_weight_unit == $limiter->limit_unit && ($value + ($quantity * $product->product_weight) > $limiter->limit_value) ) {
								$quantity = floor(($limiter->limit_value - $value) / $product->product_weight);
							}
							break;
					}
					if( $quantity < 0 ) {
						$quantity = 0;
					}
				}
			}
		}
		if($product->product_type=='variant'){
			$class = hikashop_get('class.product');
			$parentProduct = $class->get($product->product_parent_id);
			$database->setQuery('SELECT * FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic') .' AS b ON a.variant_characteristic_id=b.characteristic_id WHERE a.variant_product_id='.(int)$product->product_id.' ORDER BY a.ordering');
			$product->characteristics = $database->loadObjectList();
			$class->checkVariant($product, $parentProduct);
			if($product->product_quantity == -1 && $parentProduct->product_quantity != -1) {
				$product->product_quantity = $parentProduct->product_quantity;
				$quantity_for_same_main_product =& $this->_getGlobalQuantityOfVariants($cartContent, $product, $cart_product_id_for_product);
				if($quantity_for_same_main_product > $product->product_quantity){
					$in_excess = $quantity_for_same_main_product - $product->product_quantity;
					if($quantity > $in_excess) {
						$quantity = $product->wanted_quantity=$quantity-$in_excess;
						$quantity_for_same_main_product = $quantity_for_same_main_product - $in_excess;
					} else {
						$quantity_for_same_main_product = $quantity_for_same_main_product - $quantity;
						$quantity = $product->wanted_quantity = 0;
					}
				}
			}

			if(!empty($parentProduct->product_min_per_order)) {
				$quantity_for_same_main_product =& $this->_getGlobalQuantityOfVariants($cartContent, $product, $cart_product_id_for_product);

				if($parentProduct->product_min_per_order > 1 && $quantity_for_same_main_product < $parentProduct->product_min_per_order - $quantity) {
					$quantity = $product->wanted_quantity = 0;
				}
			}
			if(!empty($parentProduct->product_max_per_order)) {
				$quantity_for_same_main_product =& $this->_getGlobalQuantityOfVariants($cartContent,$product,$cart_product_id_for_product);
				if($quantity_for_same_main_product > $parentProduct->product_max_per_order) {
					$in_excess = $quantity_for_same_main_product - $parentProduct->product_max_per_order;
					if($quantity > $in_excess) {
						$quantity = $product->wanted_quantity = $quantity - $in_excess;
						$quantity_for_same_main_product -= $in_excess;
					} else {
						$quantity_for_same_main_product -= $quantity;
						$quantity = $product->wanted_quantity = 0;
					}
				}
			}
			if($product->product_max_per_order == 0)
				$product->product_max_per_order = $parentProduct->product_max_per_order;
		}
		if($product->product_quantity >= 0 && $product->product_quantity < $quantity) {
			$product->wanted_quantity = $quantity = $product->product_quantity;
		}

		$quantity_for_same_product =& $this->_getGlobalQuantityOfProducts($cartContent, $product, $cart_product_id_for_product);
		if($product->product_min_per_order > 1 && $product->product_min_per_order > $quantity_for_same_product) {
			$quantity = $product->product_min_per_order;
			if($product->product_quantity >= 0 && $product->product_quantity < $quantity_for_same_product) {
				$quantity = 0;
			}
		}
		if($product->product_max_per_order > 0 && $quantity_for_same_product > $product->product_max_per_order) {
			$in_excess = $quantity_for_same_product-$product->product_max_per_order;
			if($quantity > $in_excess) {
				$quantity -= $in_excess;
				$quantity_for_same_product -= $in_excess;
			} else {
				$quantity_for_same_product -= $quantity;
				$quantity = 0;
			}
		}
		if(hikashop_level(1)) {
			$config =& hikashop_config();
			$item_limit = $config->get('cart_item_limit',0);

			if(!empty($item_limit)) {
				$current_items = 0;
				if(!empty($cartContent)) {
					foreach($cartContent as $element) {
						if($element->product_id != $product->product_id)
							$current_items += (int)$element->cart_product_quantity;
					}
				}
				$possible_quantity = $item_limit - $current_items;

				if($quantity > $possible_quantity) {
					if($possible_quantity < 0) {
						$possible_quantity = 0;
					}
					$quantity = $possible_quantity;
				}
			}
		}

		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$displayErrors=true;
		$dispatcher->trigger('onAfterProductQuantityCheck', array(&$product, &$wantedQuantity,&$quantity, &$cartContent, &$cart_product_id_for_product, &$displayErrors) );

		if( $displayErrors && $wantedQuantity > $quantity ) {
			$app = JFactory::getApplication();
			if( $quantity == 0 ) {
				$app->enqueueMessage(JText::sprintf('LIMIT_REACHED_REMOVED', $product->product_name));
			} else {
				$app->enqueueMessage(JText::sprintf('LIMIT_REACHED', $product->product_name));
			}
		}

		$this->new_quantities[$product->product_id] = $quantity;
	}

	function &_getGlobalQuantityOfVariants(&$cartContent, &$product, $cart_product_id_for_product) {
		static $quantity_for_same_main_product = array();
		if(!isset($quantity_for_same_main_product[$product->product_parent_id])) {
			$quantity = $product->wanted_quantity;
			if(!empty($cartContent)) {
				foreach($cartContent as $element) {
					if($element->product_parent_id == $product->product_parent_id) {
						if($cart_product_id_for_product>0 && $element->product_id==$product->product_id && (empty($product->cart_product_id) || $element->cart_product_id==$product->cart_product_id)) {
						} else {
							$quantity+=(int)$element->cart_product_quantity;
						}
					}
				}
			}
			$quantity_for_same_main_product[$product->product_parent_id] = $quantity;
		}
		return $quantity_for_same_main_product[$product->product_parent_id];
	}

	function &_getGlobalQuantityOfProducts(&$cartContent, &$product, $cart_product_id_for_product) {
		static $quantity_for_same_main_product=array();
		if(!isset($quantity_for_same_main_product[$product->product_id])) {
			$quantity = $product->wanted_quantity;
			if(!empty($cartContent)) {
				foreach($cartContent as $element) {
					if($element->product_id == $product->product_id) {
						if($cart_product_id_for_product > 0 && (empty($product->cart_product_id) || $element->cart_product_id==$product->cart_product_id)) {
						} else {
							$quantity += (int)$element->cart_product_quantity;
						}
					}
				}
			}
			$quantity_for_same_main_product[$product->product_id] = $quantity;
		}
		return $quantity_for_same_main_product[$product->product_id];
	}


	function &loadFullCart($additionalInfos=false,$keepEmptyCart=false,$skipChecks=false){

		$app = JFactory::getApplication();
		$database	= JFactory::getDBO();
		$config =& hikashop_config();
		$currencyClass = hikashop_get('class.currency');
		$productClass = hikashop_get('class.product');
		$main_currency = (int)$config->get('main_currency',1);
		$currency_id = hikashop_getCurrency();

		if(!in_array($currency_id,$currencyClass->publishedCurrencies())){
			$currency_id = $main_currency;
		}

		$zone_id = hikashop_getZone('shipping');
		if($config->get('tax_zone_type','shipping')=='billing'){
			$tax_zone_id=hikashop_getZone('billing');
		}else{
			$tax_zone_id=$zone_id;
		}
		$discount_before_tax = (int)$config->get('discount_before_tax',0);
		$cart = new stdClass();
		$cart->products = $this->get(@$this->cart->cart_id, $keepEmptyCart, isset($this->cart->cart_type)?$this->cart->cart_type:'');
		$cart->cart_id = (int)@$this->cart->cart_id;
		$cart->cart_type = @$this->cart->cart_type;
		$cart->cart_params = @$this->cart->cart_params;
		$cart->coupon = null;
		$cart->shipping = null;
		$cart->total = null;
		$cart->additional = array();

		if(!empty($cart->products)){
			$ids = array();
			$mainIds = array();
			foreach($cart->products as $product){
				$ids[]=$product->product_id;
				if($product->product_parent_id == '0')
					$mainIds[]=(int)$product->product_id;
				else
					$mainIds[]=(int)$product->product_parent_id;
			}

			$query = 'SELECT a.*, b.* FROM '.hikashop_table('product_category').' AS a LEFT JOIN '.hikashop_table('category').' AS b ON a.category_id = b.category_id WHERE a.product_id IN('.implode(',',$mainIds).') ORDER BY a.ordering ASC';
			$database->setQuery($query);
			$categories = $database->loadObjectList();
			$quantityDisplayType = hikashop_get('type.quantitydisplay');
			foreach($cart->products as $k => $row){
				if($row->product_parent_id != 0 && $row->cart_product_parent_id != '0'){
					$row->product_quantity_layout = $cart->products[$row->cart_product_parent_id]->product_quantity_layout;
					$row->product_min_per_order = $cart->products[$row->cart_product_parent_id]->product_min_per_order;
					$row->product_max_per_order = $cart->products[$row->cart_product_parent_id]->product_max_per_order;
				}
				if(empty($row->product_quantity_layout) || $row->product_quantity_layout == 'inherit'){
					$categoryQuantityLayout = '';
					if(!empty($categories) ) {
						foreach($categories as $category) {
							if($category->product_id == $row->product_id && !empty($category->category_quantity_layout) && $quantityDisplayType->check($category->category_quantity_layout, $app->getTemplate())) {
								$categoryQuantityLayout = $category->category_quantity_layout;
								break;
							}
						}
					}
				}
				if(!empty($row->product_quantity_layout) &&  $row->product_quantity_layout != 'inherit'){
					$qLayout = $row->product_quantity_layout;
				}elseif(!empty($categoryQuantityLayout) && $categoryQuantityLayout != 'inherit'){
					$qLayout = $categoryQuantityLayout;
				}else{
					$qLayout = $config->get('product_quantity_display','show_default');
				}
				$cart->products[$k]->product_quantity_layout = $qLayout;
			}

			JArrayHelper::toInteger($ids);
			$query = 'SELECT * FROM '.hikashop_table('file').' WHERE file_ref_id IN ('.implode(',',$ids).') AND file_type IN( \'product\',\'file\') ORDER BY file_ref_id ASC, file_ordering ASC';
			$database->setQuery($query);
			$images = $database->loadObjectList();
			if(!empty($images)){
				foreach($cart->products as $k => $row){
					$productClass->addFiles($cart->products[$k],$images);
				}
			}

			foreach($cart->products as $k => $row){
				if($row->product_type=='variant'){
					foreach($cart->products as $k2 => $row2){
						if($row->product_parent_id==$row2->product_id){
							$cart->products[$k2]->variants[]=&$cart->products[$k];
							break;
						}
					}
				}
			}

			$query = 'SELECT a.*,b.* FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic').' AS b ON a.variant_characteristic_id=b.characteristic_id WHERE a.variant_product_id IN ('.implode(',',$ids).') ORDER BY a.ordering,b.characteristic_value';
			$database->setQuery($query);
			$characteristics = $database->loadObjectList();
			if(!empty($characteristics)){
				foreach($cart->products as $key => $product){
					if($product->product_type!='variant'){
						$element =& $cart->products[$key];
						$product_id=$product->product_id;
						$mainCharacteristics = array();
						foreach($characteristics as $characteristic){
							if($product_id==$characteristic->variant_product_id){
								$mainCharacteristics[$product_id][$characteristic->characteristic_parent_id][$characteristic->characteristic_id]=$characteristic;
							}
							if(!empty($element->options)){
								foreach($element->options as $k => $optionElement){
									if($optionElement->product_id==$characteristic->variant_product_id){
										$mainCharacteristics[$optionElement->product_id][$characteristic->characteristic_parent_id][$characteristic->characteristic_id]=$characteristic;
									}
								}
							}
						}

						JPluginHelper::importPlugin('hikashop');
						$dispatcher = JDispatcher::getInstance();
						$dispatcher->trigger('onAfterProductCharacteristicsLoad', array( &$element, &$mainCharacteristics, &$characteristics ) );

						if(!empty($element->variants)){
							$this->addCharacteristics($element,$mainCharacteristics,$characteristics);
						}

						if(!empty($element->options)){
							foreach($element->options as $k => $optionElement){
								if(!empty($optionElement->variants)){
									$this->addCharacteristics($element->options[$k],$mainCharacteristics,$characteristics);
								}
							}
						}
					}
				}
			}

			$product_quantities = array();
			foreach($cart->products as $row){
				if(empty($product_quantities[$row->product_id])){
					$product_quantities[$row->product_id] = (int)@$row->cart_product_quantity;
				}else{
					$product_quantities[$row->product_id]+=(int)@$row->cart_product_quantity;
				}
				if(empty($product_quantities[$row->product_parent_id])){
					$product_quantities[$row->product_parent_id] = (int)@$row->cart_product_quantity;
				}else{
					$product_quantities[$row->product_parent_id] += (int)@$row->cart_product_quantity;
				}
			}
			foreach($cart->products as $k => $row){
				$cart->products[$k]->cart_product_total_quantity = $product_quantities[$row->product_id];
				if($row->product_parent_id){
					$cart->products[$k]->cart_product_total_variants_quantity = $product_quantities[$row->product_parent_id];
				}else{
					$cart->products[$k]->cart_product_total_variants_quantity = $cart->products[$k]->cart_product_total_quantity;
				}
			}

			$currencyClass->getPrices($cart->products,$ids,$currency_id,$main_currency,$tax_zone_id,$discount_before_tax);

			if($additionalInfos){
				$queryImage = 'SELECT * FROM '.hikashop_table('file').' WHERE file_ref_id IN ('.implode(',',$ids).') AND file_type=\'product\' ORDER BY file_ref_id ASC, file_ordering ASC, file_id ASC';
				$database->setQuery($queryImage);
				$images = $database->loadObjectList();

				foreach($cart->products as $k=>$row){
					if(!empty($images)){
						foreach($images as $image){
							if($row->product_id==$image->file_ref_id){
								if(!isset($row->file_ref_id)){
									foreach(get_object_vars($image) as $key => $name){
										$cart->products[$k]->$key = $name;
									}
								}
								break;
							}
						}
					}
					if(!isset($cart->products[$k]->file_name)){
						$cart->products[$k]->file_name = $row->product_name;
					}
				}
			}

			foreach($cart->products as $k => $row){
				if(!empty($row->variants)){
					foreach($row->variants as $k2 => $variant){
						$productClass->checkVariant($cart->products[$k]->variants[$k2],$row);
					}
				}
			}

			$notUsable = array();
			$cartData = $this->loadCart($cart->cart_id);

			if(!$skipChecks){
				$cart->products = array_reverse($cart->products);
				foreach($cart->products as $k => $product){
					if(empty($product->product_id)){
						continue;
					}
					if(!empty($product->cart_product_quantity)){
						$oldQty = $product->cart_product_quantity;
						if(@$cartData->cart_type != 'wishlist')
							$this->_checkQuantity($product,$product->cart_product_quantity,$cart->products,$product->cart_product_id);
						if($oldQty!=$product->cart_product_quantity){
							$notUsable[$product->cart_product_id]=0;
							break;
						}
						if(!$config->get('display_add_to_cart_for_free_products',0) && empty($product->prices)){
							$notUsable[$product->cart_product_id]=0;
							$app->enqueueMessage(JText::sprintf('PRODUCT_NOT_AVAILABLE',$product->product_name),'notice');
							continue;
						}
						if(empty($product->product_published)){
							$notUsable[$product->cart_product_id]=0;
							$app->enqueueMessage(JText::sprintf('PRODUCT_NOT_AVAILABLE',$product->product_name),'notice');
							continue;
						}
						if($product->product_quantity!=-1 && $product->product_quantity < $product->cart_product_quantity){
							$notUsable[$product->cart_product_id]=0;
							$app->enqueueMessage(JText::sprintf('NOT_ENOUGH_STOCK_FOR_PRODUCT',$product->product_name),'notice');
							continue;
						}

						if($product->product_sale_start>time()){
							$notUsable[$product->cart_product_id]=0;
							$app->enqueueMessage(JText::sprintf('PRODUCT_NOT_YET_ON_SALE',$product->product_name),'notice');
							continue;
						}
						if(!empty($product->product_sale_end) && $product->product_sale_end<time()){
							$notUsable[$product->cart_product_id]=0;
							$app->enqueueMessage(JText::sprintf('PRODUCT_NOT_SOLD_ANYMORE',$product->product_name),'notice');
							continue;
						}
					}
				}
				$cart->products = array_reverse($cart->products);
			}

			if(!empty($notUsable)) {
				$this->update($notUsable, 1, 0, 'item');
				return $this->loadFullCart($additionalInfos);
			}

			$cart->number_of_items = 0;
			$group = $config->get('group_options',0);
			foreach($cart->products as $k => $row){
				unset($cart->products[$k]->cart_modified);
				unset($cart->products[$k]->cart_coupon);

				$currencyClass->calculateProductPriceForQuantity($cart->products[$k]);
				if(!$group || !$row->cart_product_option_parent_id)
					$cart->number_of_items += $row->cart_product_quantity;
			}

			$currencyClass->calculateTotal($cart->products, $cart->total, $currency_id);
			$cart->full_total =& $cart->total;

			JPluginHelper::importPlugin('hikashop');
			JPluginHelper::importPlugin('hikashoppayment');
			JPluginHelper::importPlugin('hikashopshipping');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAfterCartProductsLoad', array( &$cart ) );

			if(!empty($cart->additional)) {
				$currencyClass->addAdditionals($cart->additional, $cart->additional_total, $cart->full_total, $currency_id);
				$cart->full_total =& $cart->additional_total;
			}

			if(!empty($this->cart->cart_coupon) && $cart->cart_type != 'wishlist') {
				$discountClass = hikashop_get('class.discount');
				$discountData = $discountClass->load($this->cart->cart_coupon);
				if(@$discountData->discount_auto_load) {
					$current_auto_coupon_key = $this->_generateHash($cart->products, $zone_id);
					$previous_auto_coupon_key = $app->getUserState( HIKASHOP_COMPONENT.'.auto_coupon_key');

					if($current_auto_coupon_key != $previous_auto_coupon_key)
						$this->cart->cart_coupon = '';
				}
			}

			if(hikashop_level(1) && empty($this->cart->cart_coupon) && $cart->cart_type != 'wishlist'){
				$filters = array('discount_type=\'coupon\'','discount_published=1','discount_auto_load=1');
				hikashop_addACLFilters($filters,'discount_access');
				$query = 'SELECT * FROM '.hikashop_table('discount').' WHERE '.implode(' AND ',$filters).' ORDER BY discount_minimum_order DESC, discount_minimum_products DESC';
				$this->database->setQuery($query);
				$coupons = $this->database->loadObjectList();
				if(!empty($coupons)) {
					$discountClass = hikashop_get('class.discount');
					$zoneClass = hikashop_get('class.zone');
					$zones = $zoneClass->getZoneParents($zone_id);
					foreach($coupons as $coupon){
						$result = $discountClass->check($coupon,$cart->total,$zones,$cart->products,false);
						if($result){
							$auto_coupon_key = $this->_generateHash($cart->products,$zone_id);
							$app->setUserState( HIKASHOP_COMPONENT.'.auto_coupon_key',$auto_coupon_key);
							$app->setUserState( HIKASHOP_COMPONENT.'.coupon_code','');
							$this->update($coupon->discount_code,1,0,'coupon',true);
							if(empty($this->cart))
								$this->cart= new stdClass();
							$this->cart->cart_coupon = $coupon->discount_code;
							static $done = false;
							if($done == false){
								$done = true;
								return $this->loadFullCart($additionalInfos);
							}
							break;
						}
					}
				}
			}

			if(!empty($this->cart->cart_coupon) && $cart->cart_type != 'wishlist') {
				$zoneClass = hikashop_get('class.zone');
				$zones = $zoneClass->getZoneParents($zone_id);
				$cart->coupon = $discountClass->loadAndCheck($this->cart->cart_coupon, $cart->full_total, $zones, $cart->products, true);
				if(empty($cart->coupon)) {
					if(!empty($this->cart))
						$this->cart->cart_coupon = '';
				} else {
					$cart->full_total = &$cart->coupon->total;
				}
			}

			if(hikashop_toFloat($cart->full_total->prices[0]->price_value_with_tax) <= 0) {
				$cart->full_total->prices[0]->price_value_with_tax = 0;
				$cart->full_total->prices[0]->price_value = 0;
				if(isset($cart->full_total->prices[0]->taxes))
					unset($cart->full_total->prices[0]->taxes);
			}

			$shipping_id = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_id');
			if(!empty($shipping_id)) {
				$cart->shipping = $app->getUserState(HIKASHOP_COMPONENT.'.shipping_data');
				if(!empty($cart->shipping)) {
					if(!is_array($cart->shipping)){
						$cart->shipping = array($cart->shipping);
					} else {
						$shippings = array();
						foreach($cart->shipping as $method) {
							$group_key = $method->shipping_id;
							if(!empty($method->shipping_warehouse_id)){
								$group_key .= '_';
								if(is_array($method->shipping_warehouse_id)){
									foreach($method->shipping_warehouse_id as $key => $val){
										$group_key .= $key.$val;
									}
								}else{
									$group_key .= $method->shipping_warehouse_id;
								}
							}
							$shippings[$group_key]=$method;
						}
						$cart->shipping = array_values($shippings);
					}
					$currencyClass = hikashop_get('class.currency');
					$shipping =& $cart->shipping;
					$currencyClass->processShippings($shipping, $cart);
					$cart->full_total =& $currencyClass->addShipping($cart->shipping, $cart->full_total);
				}
			}

			$before_additional = !empty($cart->additional);

			$dispatcher->trigger('onAfterCartShippingLoad', array( &$cart ) );

			if(!$before_additional && !empty($cart->additional)) {
				$currencyClass->addAdditionals($cart->additional, $cart->additional_total, $cart->full_total, $currency_id);
				$cart->full_total =& $cart->additional_total;
			}

			if(hikashop_toFloat($cart->full_total->prices[0]->price_value_with_tax) <= 0) {
				$cart->full_total->prices[0]->price_value_with_tax = 0;
				$cart->full_total->prices[0]->price_value = 0;
				if(isset($cart->full_total->prices[0]->taxes)) unset($cart->full_total->prices[0]->taxes);
			}

			$payment_id = $app->getUserState( HIKASHOP_COMPONENT.'.payment_id');
			if(!empty($payment_id)){
				$cart->payment = $app->getUserState( HIKASHOP_COMPONENT.'.payment_data');
				if(!empty($cart->payment)){
					$currencyClass = hikashop_get('class.currency');
					$payment =& $cart->payment;
					$payments = array(&$payment);
					$currencyClass->processPayments($payments);
					$currencyClass->addPayment($cart->payment,$cart->full_total);
					$cart->full_total=&$cart->payment->total;
				}
			}

			if(hikashop_toFloat($cart->full_total->prices[0]->price_value_with_tax) <= 0) {
				$cart->full_total->prices[0]->price_value_with_tax = 0;
				$cart->full_total->prices[0]->price_value = 0;
				if(isset($cart->full_total->prices[0]->taxes)) unset($cart->full_total->prices[0]->taxes);
			}
		}

		if($additionalInfos){
			$app = JFactory::getApplication();
			$shipping_address=$app->getUserState( HIKASHOP_COMPONENT.'.shipping_address',0);
			if(!empty($shipping_address)){
				$this->loadAddress($cart,$shipping_address);
			}
			$billing_address=$app->getUserState( HIKASHOP_COMPONENT.'.billing_address',0);
			if($billing_address==$shipping_address){
				$cart->billing_address =& $cart->shipping_address;
			}else{
				if(!empty($billing_address)){
					$this->loadAddress($cart,$billing_address,'parent','billing');
				}
			}
			$this->calculateWeightAndVolume($cart);
		}

		return $cart;
	}

	function _generateHash(&$cart_products,$zone_id){
		$remove_columns = array('cart_modified','cart_coupon','product_hit','product_sales','product_modified','variants','characteristics','wanted_quantity');
		$products = array();
		foreach($cart_products as $cart_product){
			$product = new stdClass();
			foreach(get_object_vars($cart_product) as $k => $row){
				if(in_array($k,$remove_columns)) continue;
				$product->$k = $row;
			}
			$products[]=$product;
		}
		return sha1($zone_id.'_'.serialize($products).'_'.hikashop_loadUser());
	}

	function addCharacteristics(&$element,&$mainCharacteristics,&$characteristics){
		$element->characteristics = @$mainCharacteristics[$element->product_id][0];
		if(is_array($element->characteristics) && count($element->characteristics)){
			foreach($element->characteristics as $k => $characteristic){
				if(!empty($mainCharacteristics[$element->product_id][$k])){
					$element->characteristics[$k]->default=end($mainCharacteristics[$element->product_id][$k]);
				}
			}
		}
		if(!empty($element->variants)){
			foreach($characteristics as $characteristic){
				foreach($element->variants as $k => $variant){
					if($variant->product_id==$characteristic->variant_product_id){
						$element->variants[$k]->characteristics[$characteristic->characteristic_parent_id]=$characteristic;
						$element->characteristics[$characteristic->characteristic_parent_id]->values[$characteristic->characteristic_id]=$characteristic;
					}
				}
			}
			foreach($element->variants as $j => $variant){
				$chars = array();
				if(!empty($variant->characteristics)){
					foreach($variant->characteristics as $k => $val){
						$i = 0;
						$ordering = @$element->characteristics[$val->characteristic_parent_id]->ordering;
						while(isset($chars[$ordering])&& $i < 30){
							$i++;
							$ordering++;
						}
						$chars[$ordering] = $val;
					}
				}
				ksort($chars);
				$element->variants[$j]->characteristics=$chars;
			}
		}
	}

	function loadAddress(&$order,$address,$loading_type='parent',$address_type='shipping'){
		$addressClass=hikashop_get('class.address');
		$name = $address_type.'_address';
		if(!is_object($order)) $order = new stdClass();
		$order->$name=$addressClass->get($address);
		if(!empty($order->$name)){
			$array = array(&$order->$name);
			$addressClass->loadZone($array,$loading_type);
			if(!empty($addressClass->fields)){
				$order->fields =& $addressClass->fields;
			}
		}
	}

	function calculateWeightAndVolume(&$order) {
		$order->volume = 0;
		$order->weight = 0;
		$order->total_quantity = 0;
		if(!empty($order->products)) {

			$volumeClass = hikashop_get('helper.volume');
			$weightClass = hikashop_get('helper.weight');
			$order->weight_unit = $weightClass->getSymbol();
			$order->volume_unit = $volumeClass->getSymbol();
			foreach($order->products as $k => $product) {
				if(!empty($order->products[$k]->cart_product_quantity)){
					if((!bccomp($product->product_length,0,5)||!bccomp($product->product_width,0,5)||!bccomp($product->product_height,0,5)) && $product->cart_product_parent_id){
						foreach($order->products as $k2 => $product2){
							if($product2->cart_product_id==$product->cart_product_parent_id){
								$product->product_length = $order->products[$k]->product_length = $product2->product_length;
								$product->product_width = $order->products[$k]->product_width = $product2->product_width;
								$product->product_height = $order->products[$k]->product_height = $product2->product_height;
								$product->product_dimension_unit = $order->products[$k]->product_dimension_unit = $product2->product_dimension_unit;
								break;
							}
						}
					}
					if(bccomp($product->product_length,0,5) && bccomp($product->product_width,0,5) && bccomp($product->product_height,0,5)){
						$order->products[$k]->product_volume = $product->product_length * $product->product_width * $product->product_height;
						$order->products[$k]->product_total_volume = $order->products[$k]->product_volume * $order->products[$k]->cart_product_quantity;
						$order->products[$k]->product_total_volume_orig = $order->products[$k]->product_total_volume;
						$order->products[$k]->product_dimension_unit_orig = $order->products[$k]->product_dimension_unit;
						$order->products[$k]->product_total_volume = $volumeClass->convert($order->products[$k]->product_total_volume, $product->product_dimension_unit);
						$order->volume += $order->products[$k]->product_total_volume;
					}
				}
			}
			foreach($order->products as $k => $product){
				if(!empty($order->products[$k]->cart_product_quantity)){
					if(!bccomp($product->product_weight,0,5) && $product->cart_product_parent_id){
						foreach($order->products as $k2 => $product2){
							if($product2->cart_product_id==$product->cart_product_parent_id){
								$product->product_weight = $order->products[$k]->product_weight = $product2->product_weight;
								$product->product_weight_unit = $order->products[$k]->product_weight_unit = $product2->product_weight_unit;
								break;
							}
						}
					}
					if(bccomp($product->product_weight,0,5)){
						$order->products[$k]->product_weight_orig = $product->product_weight;
						$order->products[$k]->product_weight_unit_orig = $order->products[$k]->product_weight_unit;
						$order->products[$k]->product_weight = $weightClass->convert($product->product_weight,$product->product_weight_unit);
						$order->products[$k]->product_weight_unit = $order->weight_unit;
						$order->weight += $order->products[$k]->product_weight*$order->products[$k]->cart_product_quantity;
					}
					$order->total_quantity+=$order->products[$k]->cart_product_quantity;
				}
			}
		}
	}

	function delete(&$id, $cart_date='new'){
		$app = JFactory::getApplication();
		$result = 0;
		$cartInfo = $this->loadCart($id);
		$currUser = hikashop_loadUser(true);
		$user = JFactory::getUser();
		$session = JFactory::getSession();
		if($app->isAdmin() || (!empty($currUser) && $currUser->user_cms_id == $cartInfo->user_id) || $session->getId() == $cartInfo->session_id) {
			$result = parent::delete($id);
			if($result && $cart_date == 'new'){
				$app = JFactory::getApplication();
				$app->setUserState( HIKASHOP_COMPONENT.'.cart_id', 0);
				$this->loadCart(0,true);
			}
		}
		return $result;
	}

	function cleanCartFromSession(){
		$config =& hikashop_config();
		$app = JFactory::getApplication();
		$cart_id = $app->getUserState( HIKASHOP_COMPONENT.'.cart_id');
		if($cart_id){
			$this->delete($cart_id);
		}
		$user = JFactory::getUser();
		if($user->guest){
			$app->setUserState( HIKASHOP_COMPONENT.'.user_id', 0);
		}
		$app->setUserState( HIKASHOP_COMPONENT.'.cart_id', 0);
		$app->setUserState( HIKASHOP_COMPONENT.'.coupon_code', '');
		$app->setUserState( HIKASHOP_COMPONENT.'.cc_number', '');
		$app->setUserState( HIKASHOP_COMPONENT.'.cc_month', '');
		$app->setUserState( HIKASHOP_COMPONENT.'.cc_year', '');
		$app->setUserState( HIKASHOP_COMPONENT.'.cc_CCV', '');
		$app->setUserState( HIKASHOP_COMPONENT.'.cc_type', '');
		$app->setUserState( HIKASHOP_COMPONENT.'.cc_owner', '');
		$app->setUserState( HIKASHOP_COMPONENT.'.cc_valid', 0);
		$app->setUserState( HIKASHOP_COMPONENT.'.checkout_terms', 0);
		$app->setUserState(HIKASHOP_COMPONENT.'.display_ga', 1);

		$order_id = $app->getUserState( HIKASHOP_COMPONENT.'.order_id');
		if(empty($order_id)){
			$order_id = JRequest::getInt('order_id');
		}
		if($order_id){
			$class = hikashop_get('class.order');
			$order = $class->get($order_id);
			$db = JFactory::getDBO();
			$query = 'SELECT * FROM '.hikashop_table('payment').' WHERE payment_type='.$db->Quote($order->order_payment_method).' AND payment_id='.$db->Quote($order->order_payment_id);
			$db->setQuery($query);
			$paymentData = $db->loadObjectList();
			$pluginsClass = hikashop_get('class.plugins');
			$pluginsClass->params($paymentData,'payment');
			$paymentOptions = reset($paymentData);
			if(!empty($paymentOptions->payment_params->return_url)){
				foreach(get_object_vars($order) as $key => $val){
					if(!is_string($val)) continue;
					$paymentOptions->payment_params->return_url = str_replace('{'.$key.'}',$val,$paymentOptions->payment_params->return_url);
				}
				$app->redirect($paymentOptions->payment_params->return_url);
			}
		}
	}

	function convert($cart_id, $cart_type){
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$session = JFactory::getSession();

		$result = 0;
		if($cart_id == '0')
			$cart_id = $app->getUserState(HIKASHOP_COMPONENT.'.'.$cart_type.'_id',0,'int');
		$cartInfo = $this->loadCart($cart_id);
		$currUser = hikashop_loadUser(true);
		$session = JFactory::getSession();
		if($cart_id == 0 || $app->isAdmin() || $currUser->user_cms_id == $cartInfo->user_id || $session->getId()==$cartInfo->session_id){
			if($cart_type == 'cart'){
				$cart_type = 'wishlist';
				$query = 'UPDATE '.hikashop_table('cart').' SET cart_type = '.$db->quote($cart_type).', cart_current = 0 WHERE cart_id = '.(int)$cart_id;
				$db->setQuery($query);
				$result = $db->query();
			}else{
				$cart_type = 'cart';
				$query = 'SELECT * FROM '.hikashop_table('cart_product').' WHERE cart_id = '.(int)$cart_id;
				$db->setQuery($query);
				$cartProducts = $db->loadObjectList();


				if($cart_id == 0){
					$newCart = new stdClass();
					$newCart->user_id = $user->id;
					$newCart->session_id = $session->getId();
					$newCart->cart_modified = time();
					$newCart->cart_type = 'cart';
					$cart_id = $this->save($newCart);
					$app->setUserState(HIKASHOP_COMPONENT.'.cart_id',$cart_id);
				}
				$fields=array();
				$values=array();
				$list=array();
				foreach($cartProducts as $products){
					$products->cart_id = $cart_id;
					$products->cart_product_modified = time();
					foreach($products as $k => $data){
						$fields[] = $k;
						$values[] = $data;
					}
					$list[]='('.implode(',',$values).')';
				}
					$query = 'INSERT IGNORE INTO '.hikashop_table('cart_product').' ('.implode(',',$fields).') VALUES '.implode(',',$list);
					$db->setQuery($query);
					$result = $db->Query();
			}
			if($app->getUserState(HIKASHOP_COMPONENT.'.'.$cart_type.'_id','0') == $cart_id){
				$app->setUserState(HIKASHOP_COMPONENT.'.'.$cart_type.'_id','0');
			}
		}
		return $result;
	}

	function setCurrent($cart_id = '0', $cart_type = 'cart'){
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$session = JFactory::getSession();
		$sessionToken = $session->get('session.token','');

		$result = 0;
		$app = JFactory::getApplication();
		$cartInfo = $this->loadCart($cart_id);
		$currUser = hikashop_loadUser(true);
		$session = JFactory::getSession();
		if($cart_id == 0 || $app->isAdmin() || $currUser->user_cms_id == $cartInfo->user_id || $session->getId()==$cartInfo->session_id){
			if($cart_id != '0'){
				$query='UPDATE '.hikashop_table('cart').' SET cart_current = 1 WHERE cart_id = '.(int)$cart_id;
				$db->setQuery($query);
				$db->query();
			}
			$query='UPDATE '.hikashop_table('cart').' SET cart_current = 0 WHERE (user_id='.(int)$user->id.' OR session_id='.$db->quote($sessionToken).') AND cart_type='.$db->quote($cart_type).' AND cart_id != '.(int)$cart_id;
			$db->setQuery($query);
			$result = $db->query();
		}
		return $result;
	}

	function checkSubscription($cart){
		$plugin = new stdClass();
		$plugin->params = array();

		$pluginName = '';
		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onCheckSubscritpionPlugin', array( &$pluginName ) );
		$dispatcher->trigger('onCheckSubscriptionPlugin', array( &$pluginName ) ); // Correct on 19/06/2014

		if(!empty($pluginName)) {
			$pluginsClass = hikashop_get('class.plugins');
			$plugin = $pluginsClass->getByName('hikashop',$pluginName);
		}

		if(!isset($plugin->params['manysubscriptions']) || empty($plugin->params['manysubscriptions']))
			$plugin->params['manysubscriptions'] = 0;

		$i = 0;
		$recurring = 0;
		$noRecurring = 0;
		$subLevel = array();
		$durations = array();
		$paymentType = 'noRecurring';
		$oldProduct = null;
		$totalProducts = 0;
		if(isset($cart->products) && $cart->products != null){
			foreach($cart->products as $product){
				if(!isset($product->product_subscription_id) || $product->product_subscription_id == '0'){
					$noRecurring++;
				}else{
					$subLevel[$i] = $product->product_subscription_id;
					$recurring++;
				}
				$i++;
				$totalProducts += $product->cart_product_quantity;
				if(isset($oldProduct->product_type) &&  $oldProduct->product_type == 'main' && $product->product_type == 'variant'){
					$noRecurring--;
					$recurring--;
				}
				$oldProduct = $product;
			}
		}
		if(empty($subLevel)){
			$paymentType = 'noRecurring';
		}
		else if((int)$plugin->params['manysubscriptions'] == 0 && $totalProducts > 1 && $recurring > 1){
			$enqueueMessage	= JText::_('HIKA_RECUR_ONE_SUBS_ALLOWED');
		}
		else{
			$dispatcher->trigger('onCheckSubscription', array( &$subLevel,&$subs ) );
			$recurring = 0;
			$i = 0;
			if(isset($subs) && !empty($subs)){
				foreach($subs as $value){
					if(isset($value->recurring) && $value->recurring == '1'){
						$durations[$i] = $value->duration;
						$recurring++;
					}
					$i++;
				}
			}
			if($recurring == 0){
				$paymentType = 'noRecurring';
			} else{
				if($noRecurring == 0 && $recurring == 1 && $i == 1){
					$paymentType = 'recurring';
				}
				else if($noRecurring == 0 && $recurring == $i){
					$sameDuration = 0;
					$durations = array_unique($durations);
					$sameDuration = array_key_exists('1', $durations);

					if($sameDuration == 1){
						$enqueueMessage	= JText::_('HIKA_RECUR_DURATION_TIME');
					}
					else{
						$paymentType = 'recurring';
					}
				}
				else if(($noRecurring >= 1 && $recurring >=1) || ($recurring >= 1 && $recurring <= $i)){
					$enqueueMessage	= JText::_('HIKA_RECUR_BOTH_PRODUCT_TYPE');
				}
			}
		}
		if(!empty($enqueueMessage)) {
			static $displayReccuringMessage = false;
			if(!$displayReccuringMessage) {
				$app = JFactory::getApplication();
				$app->enqueueMessage($enqueueMessage);
				$displayReccuringMessage = true;
			}
		}
		return $paymentType;
	}
}
