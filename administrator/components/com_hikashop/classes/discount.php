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
class hikashopDiscountClass extends hikashopClass{
	var $tables = array('discount');
	var $pkeys = array('discount_id');
	var $namekeys = array('');
	var $toggle = array('discount_published'=>'discount_id');

	function saveForm(){
		$discount = new stdClass();
		$discount->discount_id = hikashop_getCID('discount_id');
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = & JFilterInput::getInstance(null, null, 1, 1);
		$nameboxes = array('discount_product_id','discount_category_id','discount_zone_id');
		foreach($formData['discount'] as $column => $value){
			hikashop_secureField($column);
			if(in_array($column,$nameboxes)){
				if($column=='discount_zone_id'){
					$discount->$column = array();
					foreach($value as $i => $v){
						$discount->{$column}[] = $safeHtmlFilter->clean(strip_tags($v), 'string');
					}
				}else{
					JArrayHelper::toInteger($value);
				}
				$discount->$column = $value;
			}else{
				$discount->$column = $safeHtmlFilter->clean(strip_tags($value), 'string');
			}
		}

		foreach($nameboxes as $namebox){
			if(!isset($discount->$namebox)){
				$discount->$namebox = '';
			}
		}
		if(!empty($discount->discount_start)){
			$discount->discount_start=hikashop_getTime($discount->discount_start);
		}
		if(!empty($discount->discount_end)){
			$discount->discount_end=hikashop_getTime($discount->discount_end);
		}
		if(!empty($discount->discount_id) && !empty($discount->discount_code)){
			$query = 'SELECT discount_id FROM '.hikashop_table('discount').' WHERE discount_code  = '.$this->database->Quote($discount->discount_code).' LIMIT 1';
			$this->database->setQuery($query);
			$res = $this->database->loadResult();
			if(!empty($res) && $res != $discount->discount_id){
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_( 'DISCOUNT_CODE_ALREADY_USED' ), 'error');
				JRequest::setVar( 'fail', $discount  );
				return false;
			}
		}

		$status = $this->save($discount);
		if(!$status){
			JRequest::setVar( 'fail', $discount  );
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('DISCOUNT_CODE_ALREADY_USED'));
		}
		return $status;
	}

	function save(&$discount){
		if(empty($discount->discount_id)){
			if(empty($discount->discount_type) || ($discount->discount_type=='coupon' && empty($discount->discount_code))){
				return false;
			}
			$new = true;
		}

		if(!empty($discount->discount_code)) $discount->discount_code = trim($discount->discount_code);

		if(!empty($discount->discount_product_id) && is_array($discount->discount_product_id)){
			$discount->discount_product_id = ','.implode(',',$discount->discount_product_id).',';
		}
		if(!empty($discount->discount_category_id) && is_array($discount->discount_category_id)){
			$discount->discount_category_id = ','.implode(',',$discount->discount_category_id).',';
		}
		if(!empty($discount->discount_zone_id) && is_array($discount->discount_zone_id)){
			$discount->discount_zone_id = ','.implode(',',$discount->discount_zone_id).',';
		}


		$do=true;
		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher = JDispatcher::getInstance();
		if(!empty($new)){
			$dispatcher->trigger( 'onBeforeDiscountCreate', array( &$discount, & $do) );
		}else{
			$dispatcher->trigger( 'onBeforeDiscountUpdate', array( &$discount, & $do) );
		}
		if(!$do) return false;

		$status = parent::save($discount);
		if($status){
			if(!empty($new)){
				$dispatcher->trigger( 'onAfterDiscountCreate', array( &$discount) );
			}else{
				$dispatcher->trigger( 'onAfterDiscountUpdate', array( &$discount) );
			}
		}
		return $status;
	}

	function delete(&$elements){
		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher = JDispatcher::getInstance();
		$do = true;
		$dispatcher->trigger('onBeforeDiscountDelete', array(&$elements, &$do));
		if(!$do)
			return false;

		$status = parent::delete($elements);
		if($status) {
			$dispatcher->trigger('onAfterDiscountDelete', array(&$elements));
		}
		return $status;
	}

	function load($coupon) {
		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$item = $dispatcher->trigger('onBeforeCouponLoad', array(&$coupon, &$do));
		if(!$do)
			return current($item);

		$coupon = trim($coupon);

		static $coupons = array();
		if(!isset($coupons[$coupon])) {
			$filters = array('discount_code='.$this->database->Quote($coupon),'discount_type=\'coupon\'','discount_published=1');
			$query = 'SELECT * FROM '.hikashop_table('discount').' WHERE ('.implode(') AND (',$filters).')';
			$this->database->setQuery($query);
			$coupons[$coupon] = $this->database->loadObject();
		}
		return $coupons[$coupon];
	}

	function loadAndCheck($coupon_code, &$total, $zones, &$products, $display_error = true) {
		$coupon = $this->load($coupon_code);
		return $this->check($coupon, $total, $zones, $products, $display_error);
	}

	function check(&$coupon, &$total, $zones, &$products, $display_error = true) {
		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher = JDispatcher::getInstance();
		$error_message = '';
		$do = true;

		if(isset($coupon->discount_value)) {
			$coupon = $this->get($coupon->discount_id);
		}

		$dispatcher->trigger( 'onBeforeCouponCheck', array( &$coupon, &$total, &$zones, &$products, &$display_error, &$error_message, &$do) );
		if($do){
			$user = hikashop_get('class.user');
			$currency = hikashop_get('class.currency');
			if(empty($coupon)) {
				$error_message = JText::_('COUPON_NOT_VALID');
			} elseif($coupon->discount_start > time()) {
				$error_message = JText::_('COUPON_NOT_YET_USABLE');
			} elseif($coupon->discount_end && $coupon->discount_end < time()) {
				$error_message = JText::_('COUPON_EXPIRED');
			} elseif(hikashop_level(2) && !empty($coupon->discount_access) && $coupon->discount_access != 'all' && ($coupon->discount_access == 'none' || !hikashop_isAllowed($coupon->discount_access))){
				$error_message = JText::_('COUPON_NOT_FOR_YOU');
			} elseif(empty($error_message) && hikashop_level(1) && !empty($coupon->discount_quota) && $coupon->discount_quota <= $coupon->discount_used_times) {
				$error_message = JText::_('QUOTA_REACHED_FOR_COUPON');
			} elseif(empty($error_message) && hikashop_level(1)) {
				if(!empty($coupon->discount_quota_per_user)) {
					$user_id = hikashop_loadUser();
					if($user_id){
						$db = JFactory::getDBO();
						$config =& hikashop_config();
						$cancelled_order_status = explode(',', $config->get('cancelled_order_status'));
						$cancelled_order_status = "'".implode("','",$cancelled_order_status)."'";
						$query = 'SELECT COUNT(order_id) AS already_used FROM '.hikashop_table('order').' WHERE order_user_id='.(int)$user_id.' AND order_status NOT IN ('.$cancelled_order_status.') AND order_discount_code='.$db->Quote($coupon->discount_code).' GROUP BY order_id';
						$db->setQuery($query);
						$already_used = $db->loadResult();
						if($coupon->discount_quota_per_user<=$already_used){
							$error_message = JText::_('QUOTA_REACHED_FOR_COUPON');
						}
					}
				}
				if(empty($error_message) && $coupon->discount_zone_id) {
					if(!is_array($coupon->discount_zone_id))
						$coupon->discount_zone_id = explode(',',$coupon->discount_zone_id);
					$class = hikashop_get('class.zone');
					$zone = $class->getZones($coupon->discount_zone_id,'zone_namekey','zone_namekey',true);
					if($zone && !count(array_intersect($zone,$zones))){
						$error_message = JText::_('COUPON_NOT_AVAILABLE_IN_YOUR_ZONE');
					}
				}
				$ids = array();
				$qty = 0;
				foreach($products as $prod) {
					$qty += $prod->cart_product_quantity;
					if(!empty($prod->product_parent_id))
						$ids[$prod->product_parent_id] = (int)$prod->product_parent_id;
					else
						$ids[$prod->product_id] = (int)$prod->product_id;
				}

				if(!empty($coupon->discount_product_id) && is_string($coupon->discount_product_id))
					$coupon->discount_product_id = explode(',',$coupon->discount_product_id);

				if(empty($error_message) && !empty($coupon->discount_product_id) && count(array_intersect($ids, $coupon->discount_product_id)) == 0)
					$error_message = JText::_('COUPON_NOT_FOR_THOSE_PRODUCTS');

				if(empty($error_message) && $coupon->discount_category_id) {
					$db = JFactory::getDBO();
					if(!is_array($coupon->discount_category_id))
						$coupon->discount_category_id = explode(',', trim($coupon->discount_category_id, ','));
					if($coupon->discount_category_childs) {
						$filters = array('b.category_type=\'product\'','a.product_id IN ('.implode(',',$ids).')');

						$categoryClass = hikashop_get('class.category');
						$categories = $categoryClass->getCategories($coupon->discount_category_id,'category_left, category_right');

						if(!empty($categories)) {
							$categoriesFilters = array();
							foreach($categories as $category) {
								$categoriesFilters[] = 'b.category_left >= '.$category->category_left.' AND b.category_right <= '.$category->category_right;
							}
							if(count($categoriesFilters)) {
								$filters[] = '(('.implode(') OR (',$categoriesFilters).'))';
								hikashop_addACLFilters($filters,'category_access','b');
								$select = 'SELECT a.product_id FROM '.hikashop_table('category').' AS b LEFT JOIN '.hikashop_table('product_category').' AS a ON b.category_id=a.category_id WHERE '.implode(' AND ',$filters);
								$db->setQuery($select);
								$id = $db->loadRowList();
								if(empty($id)) {
									$error_message = JText::_('COUPON_NOT_FOR_PRODUCTS_IN_THOSE_CATEGORIES');
								}
							}
						}
					} else {
						JArrayHelper::toInteger($coupon->discount_category_id);
						$filters = array('b.category_id IN ('.implode(',',$coupon->discount_category_id).')' ,'a.product_id IN ('.implode(',',$ids).')');
						hikashop_addACLFilters($filters,'category_access','b');
						$select = 'SELECT a.product_id FROM '.hikashop_table('category').' AS b LEFT JOIN '.hikashop_table('product_category').' AS a ON b.category_id=a.category_id WHERE '.implode(' AND ',$filters);
						$db->setQuery($select);
						$id = $db->loadRowList();
						if(empty($id)) {
							$error_message = JText::_('COUPON_NOT_FOR_PRODUCTS_IN_THOSE_CATEGORIES');
						}
					}
				}

				$coupon->products = array();
				if(!empty($coupon->discount_product_id)) {
					foreach ($products as $product) {
						if(!in_array($product->product_id,$coupon->discount_product_id)) {
							foreach ($products as $product2) {
								if($product2->cart_product_id == $product->cart_product_parent_id && in_array($product2->product_id, $coupon->discount_product_id)){
									$coupon->products[] = $product;
								}
							}
						} else {
							$coupon->products[] = $product;
						}
					}
				} else if(!empty($id)) {
					foreach ($products as $product) {
						foreach ($id as $productid) {
							if($product->product_id !== $productid[0]) {
								foreach ($products as $product2) {
									if($product2->cart_product_id == $product->cart_product_parent_id && $product2->product_id == $productid[0]) {
										$coupon->products[] = $product;
									}
								}
							} else {
								$coupon->products[] = $product;
							}
						}
					}
				} else {
					foreach($products as $product) {
						$coupon->products[] = $product;
					}
					$coupon->all_products = true;
				}

				if(empty($error_message) && bccomp($coupon->discount_minimum_order, 0, 5)) {

					$currency->convertCoupon($coupon,$total->prices[0]->price_currency_id);
					$config =& hikashop_config();
					$discount_before_tax = $config->get('discount_before_tax');
					$var = 'price_value_with_tax';
					if($discount_before_tax) {
						$var = 'price_value';
					}

					$total_amount = 0;
					if(!empty($coupon->products)) {
						foreach($coupon->products as $product) {
							if($product->cart_product_quantity > 0)
								$total_amount += @$product->prices[0]->$var;
						}
					}

					if($coupon->discount_minimum_order > $total_amount) {
						$error_message = JText::sprintf('ORDER_NOT_EXPENSIVE_ENOUGH_FOR_COUPON',$currency->format($coupon->discount_minimum_order,$coupon->discount_currency_id));
					}
				}

				if(empty($error_message) && (int)$coupon->discount_minimum_products > 0) {
					$qty = 0;
					if(!empty($coupon->products)) {
						foreach($coupon->products as $product) {
							$qty += $product->cart_product_quantity;
						}
					}

					if((int)$coupon->discount_minimum_products > $qty) {
						$error_message = JText::sprintf('NOT_ENOUGH_PRODUCTS_FOR_COUPON', (int)$coupon->discount_minimum_products);
					}
				}
			}
		}

		$dispatcher->trigger('onAfterCouponCheck', array(&$coupon, &$total, &$zones, &$products, &$display_error, &$error_message, &$do));

		if(!empty($error_message)) {
			$class = hikashop_get('class.cart');
			$class->update('',0,0,'coupon');
			if($display_error) {
				JRequest::setVar('coupon_error_message',$error_message);
			}
			return null;
		}

		JRequest::setVar('coupon_error_message','');

		if($do) {
			$currency->convertCoupon($coupon,$total->prices[0]->price_currency_id);

			if(!HIKASHOP_PHP5){
				$coupon->total = $total;
			}else{
				if(!isset($coupon->total))
					$coupon->total = new stdClass();
				$coupon->total->prices = array(clone(reset($total->prices)));
			}


			$this->recalculateDiscountValue($coupon, $products, $id);

			switch (@$coupon->discount_coupon_nodoubling) {
				case 1:
				case 2:
					$coupon = $this->addCoupon($coupon, $products, $currency, $coupon->discount_coupon_nodoubling);
					break;
				default:
					$currency->addCoupon($coupon->total,$coupon, $products, $id);
					break;
			}
		}
		return $coupon;

	}


	function recalculateDiscountValue(&$coupon, &$products, &$id) {
		if(bccomp($coupon->discount_percent_amount, 0, 5) === 0 || (empty($coupon->discount_coupon_product_only) && (empty($coupon->products) || !empty($coupon->all_products))))
			return;

		$coupon->discount_flat_amount = 0;

		if(!empty($coupon->discount_product_id)) {
			if(!is_array($coupon->discount_product_id)){
				$coupon->discount_product_id = explode(',',$coupon->discount_product_id);
			}
			$variantsFirst = array_reverse($products);
			foreach ($variantsFirst as $product) {
				if(!isset($product->prices[0])) continue;
				if(empty($product->cart_product_quantity)) continue;
				if(in_array($product->product_id,$coupon->discount_product_id)  || in_array($product->product_parent_id,$coupon->discount_product_id)) {
					switch($coupon->discount_coupon_nodoubling){
						case 2:
							if(isset($product->prices[0]->price_value_without_discount)) {
								$coupon->discount_flat_amount += ($coupon->discount_percent_amount * $product->prices[0]->price_value) / 100;
								$coupon->discount_flat_amount -= $product->prices[0]->price_value_without_discount - $product->prices[0]->price_value;
								if($coupon->discount_flat_amount<0)$coupon->discount_flat_amount=0;
								continue;
							}
						case 1:
							if(isset($product->prices[0]->price_value_without_discount)){
								continue;
							}
						default:
							$coupon->discount_flat_amount += ($coupon->discount_percent_amount * $product->prices[0]->price_value) / 100;
							break;
					}
				}
			}
		} else if(!empty($coupon->products)) {

			$variantsFirst = array_reverse($products);
			foreach ($variantsFirst as $product) {
				if(!isset($product->prices[0]) || empty($product->cart_product_quantity))
					continue;

				foreach ($coupon->products as $prod) {
					if(!empty($prod->product_parent_id))
						$productid = $prod->product_parent_id;
					else
						$productid = $prod->product_id;

					if($product->product_id == $productid && empty($product->variants) || $product->product_parent_id == $productid) {
						switch($coupon->discount_coupon_nodoubling) {
							case 2:
								if(isset($product->prices[0]->price_value_without_discount)){
									$coupon->discount_flat_amount += ($coupon->discount_percent_amount * $product->prices[0]->price_value) / 100;
									$coupon->discount_flat_amount -= $product->prices[0]->price_value_without_discount - $product->prices[0]->price_value;
									if($coupon->discount_flat_amount < 0)
										$coupon->discount_flat_amount = 0;
									continue;
								}
							case 1:
								if(isset($product->prices[0]->price_value_without_discount))
									continue;

							default:
								if(isset($product->prices[0]->price_value))
									$coupon->discount_flat_amount += ($coupon->discount_percent_amount * $product->prices[0]->price_value) / 100;
								break;
						}
						break;
					}
				}
			}
		}

		if (bccomp($coupon->discount_flat_amount, 0, 5)) {
			$coupon->discount_percent_amount = 0;
			$coupon->discount_coupon_nodoubling = null;
		}

		$currencyHelper = hikashop_get('class.currency');
		$coupon->discount_flat_amount = $currencyHelper->round($coupon->discount_flat_amount, $currencyHelper->getRounding($coupon->discount_currency_id, true));
	}


	function addCoupon(&$coupon1, &$products, &$currency, $discountmode) {
		$totaldiscount=0.0;
		$totaldiscount_with_tax=0.0;
		$totalprice=0.0;
		$totalprice_with_tax=0.0;
		$totalnondiscount=0.0;
		$totalnondiscount_with_tax=0.0;
		foreach($products as $k => $product){
			if(!empty($product->prices)&&$product->cart_product_quantity>0){
				$price = reset($product->prices);
				if (isset($price->price_value)) {
					$totalprice += $price->price_value;
					if (isset($price->price_value_without_discount)){
						$totaldiscount += $price->price_value_without_discount - $price->price_value;
					}
					else {
						$totalnondiscount += $price->price_value;
					}
				}
				if (isset($price->price_value_with_tax)) {
					$totalprice_with_tax += $price->price_value_with_tax;
					if (isset($price->price_value_without_discount_with_tax)){
						$totaldiscount_with_tax += $price->price_value_without_discount_with_tax - $price->price_value_with_tax;
					}
					else {
						$totalnondiscount_with_tax += $price->price_value_with_tax;
					}
				}
			}
		}

		if (!bccomp($totaldiscount_with_tax, 0, 5) || !bccomp($totaldiscount, 0, 5)) {
			$currency->addCoupon($coupon1->total,$coupon1);
			return $coupon1;
		}

		if (bccomp($coupon1->discount_flat_amount, 0, 5) && $totalnondiscount >= $coupon1->discount_flat_amount) {
			$currency->addCoupon($coupon1->total,$coupon1);
			return $coupon1;
		}

		$totalprice += $totaldiscount;
		$totalprice_with_tax += $totaldiscount_with_tax;

		$coupon2 = clone($coupon1);
		$coupon2->total = clone($coupon1->total);
		$coupon2->total->prices = $this->copyStandardPrices($coupon1->total->prices);
		switch ($discountmode) {
			case 2:
				$coupon2->total->prices[0]->price_value_with_tax = $totalprice_with_tax;
				$coupon2->total->prices[0]->price_value = $totalprice;

				$currency->addCoupon($coupon2->total,$coupon2);

				$coupon2->total->prices[0]->price_value_without_discount_with_tax -= $totaldiscount_with_tax;
				$coupon2->total->prices[0]->price_value_without_discount -= $totaldiscount;
				$coupon2->discount_percent_amount_calculated_without_tax -= $totaldiscount;
				$coupon2->discount_value_without_tax -= $totaldiscount;
				$coupon2->discount_percent_amount_calculated -= $totaldiscount;
				$coupon2->discount_value -= $totaldiscount;
				$coupon2->discount_flat_amount = $coupon2->discount_value;
				break;
			default:
				if($coupon2->discount_flat_amount > $totalnondiscount_with_tax) {
					$coupon2->discount_flat_amount=0;
				}
				$total = new stdClass();
				$obj = new stdClass();
				$total->prices = array($obj);
				$total->prices[0]->price_value_with_tax = $totalnondiscount_with_tax;
				$total->prices[0]->price_value = $totalnondiscount;
				$total->prices[0]->taxes = array();
				$currency->addCoupon($total,$coupon2);
				break;
		}

		if (isset($coupon2->discount_percent_amount_calculated) && $discountmode < 2) {
			$price_diff = $coupon2->discount_percent_amount_calculated - $totaldiscount_with_tax;
		}
		elseif(isset($coupon2->discount_percent_amount_calculated) && $discountmode == 2){
			$price_diff = $coupon2->discount_value;
		}else if(isset($coupon2->discount_percent_amount_calculated) ){
			$price_diff = $coupon2->discount_percent_amount_calculated - $totaldiscount_with_tax;
			$price_diff += $totaldiscount;
		}

		if (@$price_diff <= 0) {
			$couponErrorMessage = JText::_('COUPON_NO_VALUE_WHEN_DISCOUNT');
			JRequest::setVar('coupon_error_message',$couponErrorMessage);
			return $coupon1;
		}
		if(!(isset($coupon2->discount_percent_amount_calculated) && $discountmode == 2)){
			$coupon2->discount_percent_amount_calculated_without_tax = $price_diff + $totaldiscount;
			$coupon2->discount_value_without_tax = $price_diff + $totaldiscount;
			$coupon2->discount_percent_amount_calculated = $price_diff + $totaldiscount_with_tax;
			$coupon2->discount_value = $price_diff + $totaldiscount_with_tax;
		}
		if ($discountmode == 1) {
			$coupon2->total->prices[0]->price_value_without_discount_with_tax = $totalprice_with_tax;
			$coupon2->total->prices[0]->price_value_without_discount = $totalprice;
			$coupon2->total->prices[0]->price_value_with_tax -= $coupon2->discount_value;
			$coupon2->total->prices[0]->price_value -= $coupon2->discount_value_without_tax;
		}

		if ($coupon2->discount_flat_amount != $coupon2->discount_value_without_tax) {
			$couponErrorMessage = JText::_('COUPON_LIMITED_VALUE_WHEN_DISCOUNT');
			JRequest::setVar('coupon_error_message',$couponErrorMessage);
		}
		return $coupon2;
	}

	function copyStandardPrices($prices) {
		$copiedPrices = array();
		for ($i=0; $i<count($prices); $i++) $copiedPrices[$i] = clone($prices{$i});
		return $copiedPrices;
	}


	public function &getNameboxData($typeConfig, &$fullLoad, $mode, $value, $search, $options) {
		$ret = array(
			0 => array(),
			1 => array()
		);

		static $multiTranslation = null;
		if($multiTranslation === null) {
			$translationHelper = hikashop_get('helper.translation');
			$multiTranslation = $translationHelper->isMulti(true);
		}

		$db = JFactory::getDBO();
		$app = JFactory::getApplication();

		$category_type = array('discount','coupon');
		$fullLoad = false;

		$depth = (int)@$options['depth'];
		$start = (int)@$options['start'];
		$limit = (int)@$options['limit'];


		if($depth <= 0)
			$depth = 1;
		if($limit <= 0)
			$limit = 200;

		if(@$options['type'])
			$category_type = explode(',',$options['type']);

		$category_types = array();
		foreach($category_type as $t) {
			$category_types[] = $db->Quote($t);
		}

		$select = array('d.*');
		$table = array(hikashop_table('discount').' AS d');
		$where = array('d.discount_type IN ('.implode(',', $category_types).')');
		$order = ' ORDER BY d.discount_type ASC, d.discount_code ASC';

		$query = 'SELECT '.implode(', ', $select) . ' FROM ' . implode(' ', $table) . ' WHERE ' . implode(' AND ', $where);
		$db->setQuery($query, 0, $limit);

		if(!$app->isAdmin() && $multiTranslation && class_exists('JFalangDatabase')) {
			$discounts = $db->loadObjectList('discount_id', 'stdClass', false);
		} elseif(!$app->isAdmin() && $multiTranslation && (class_exists('JFDatabase') || class_exists('JDatabaseMySQLx'))) {
			$discounts = $db->loadObjectList('discount_id', false);
		} else {
			$discounts = $db->loadObjectList('discount_id');
		}
		if(count($discounts) < 200)
			$fullLoad = true;

		foreach($discounts as $discount) {
			$ret[0][$discount->discount_id] = (!empty($discount->translation)) ? $discount->translation : $discount->discount_code;
		}

		if(!empty($value)) {
			if(!is_array($value))
				$value = array($value);

			$search = array();
			foreach($value as $v) {
				$search[] = (int)$v;
			}
			$query = 'SELECT d.* '.
					' FROM ' . hikashop_table('discount') . ' AS d '.
					' WHERE d.discount_id IN ('.implode(',', $search).')';
			$db->setQuery($query);

			if(!$app->isAdmin() && $multiTranslation && class_exists('JFalangDatabase')) {
				$discounts = $db->loadObjectList('discount_id', 'stdClass', false);
			} elseif(!$app->isAdmin() && $multiTranslation && (class_exists('JFDatabase') || class_exists('JDatabaseMySQLx'))) {
				$discounts = $db->loadObjectList('discount_id', false);
			} else {
				$discounts = $db->loadObjectList('discount_id');
			}

			if(!empty($discounts)) {
				foreach($discounts as $discount) {
					$discount->name = (!empty($discount->translation)) ? $discount->translation :  JText::_($discount->discount_code);
					$ret[1][$discount->discount_id] = $discount;
				}
			}
			unset($discounts);

			if($mode == hikashopNameboxType::NAMEBOX_SINGLE)
				$ret[1] = reset($ret[1]);
		}

		return $ret;
	}
}
