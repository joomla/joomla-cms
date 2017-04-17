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
class hikashopOrder_productClass extends hikashopClass{
	var $tables = array('order_product');
	var $pkeys = array('order_product_id');

	function save(&$products){
		if(empty($products))
			return true;

		$items = array();
		$updates = array();
		$productsQuantity = array();
		$discounts = array();
		$fields = array(
			'order_id,product_id',
			'order_product_quantity',
			'order_product_name',
			'order_product_code',
			'order_product_price',
			'order_product_tax',
			'order_product_options',
			'order_product_option_parent_id',
			'order_product_tax_info',
			'order_product_wishlist_id',
			'order_product_shipping_id',
			'order_product_shipping_method',
			'order_product_shipping_price',
			'order_product_shipping_tax',
			'order_product_shipping_params'
		);

		if(hikashop_level(2)){
			$element=null;
			$fieldsClass = hikashop_get('class.field');
			$itemFields = $fieldsClass->getFields('frontcomp',$element,'item');
			if(!empty($itemFields)){
				foreach($itemFields as $field){
					if($field->field_type=='customtext') continue;
					$fields[]=$field->field_namekey;
				}
			}
		}
		$order_id = 0;
		$class = hikashop_get('class.product');
		foreach($products as $product){
			if(isset($product->order_product_tax_info) && !is_string($product->order_product_tax_info)){
				$product->order_product_tax_info = serialize($product->order_product_tax_info);
			}
			$order_id = (int)$product->order_id;
			if(!empty($product->order_product_options) && !is_string($product->order_product_options)){
				$product->order_product_options = serialize($product->order_product_options);
			}
			$line = array(
				$order_id,
				(int)$product->product_id,
				(int)$product->order_product_quantity,
				$this->database->Quote($product->order_product_name),
				$this->database->Quote($product->order_product_code),
				$this->database->Quote(@$product->order_product_price),
				$this->database->Quote(@$product->order_product_tax),
				$this->database->Quote(@$product->order_product_options),
				(int)@$product->cart_product_id,
				$this->database->Quote(@$product->order_product_tax_info),
				(int)@$product->order_product_wishlist_id,
				$this->database->Quote(@$product->order_product_shipping_id),
				$this->database->Quote(@$product->order_product_shipping_method),
				(float)@$product->order_product_shipping_price,
				(float)@$product->order_product_shipping_tax,
				$this->database->Quote(@$product->order_product_shipping_params)
			);
			if(!empty($itemFields)){
				foreach($itemFields as $field){
					$namekey=$field->field_namekey;
					if($field->field_type=='customtext') continue;
					$line[] = $this->database->Quote(@$product->$namekey);
				}
			}
			$items[] = '('.implode(',',$line).')';
			if(!empty($product->product_id)) {
				if(empty($product->no_update_qty)) {
					if(empty($productsQuantity[(int)$product->product_id])){
						$productsQuantity[(int)$product->product_id] = $product->order_product_quantity;
					}else{
						$productsQuantity[(int)$product->product_id] += $product->order_product_quantity;
					}

					$prod = $class->get((int)$product->product_id);
					if($prod->product_type=='variant' && !empty($prod->product_parent_id)){
						if(empty($productsQuantity[(int)$prod->product_parent_id])){
							$productsQuantity[(int)$prod->product_parent_id] = $product->order_product_quantity;
						}else{
							$productsQuantity[(int)$prod->product_parent_id] += $product->order_product_quantity;
						}
					}
				}
			}

			if(!empty($product->discount)){
				if(empty($discounts[$product->discount->discount_code])){
					$discounts[$product->discount->discount_code] = 0;
				}
				$discounts[$product->discount->discount_code] += (int)$product->order_product_quantity;
			}
		}

		if(!empty($productsQuantity)){
			foreach($productsQuantity as $id => $qty){
				if(empty($updates[$qty])){
					$updates[$qty] = array();
				}
				$updates[$qty][]=$id;
			}
		}

		if(!empty($updates)){
			foreach($updates as $k => $update){
				$query = 'UPDATE '.hikashop_table('product').' SET product_quantity = product_quantity - '.(int)$k.' WHERE product_id IN ('.implode(',',$update).') AND product_quantity >= 0 AND product_quantity > '.(int)($k-1);
				$this->database->setQuery($query);
				$this->database->query();
				$query = 'UPDATE '.hikashop_table('product').' SET product_sales = product_sales + '.(int)$k.' WHERE product_id IN ('.implode(',',$update).')';
				$this->database->setQuery($query);
				$this->database->query();
			}
		}
		$query = 'INSERT IGNORE INTO '.hikashop_table('order_product').' ('.implode(',',$fields).') VALUES '.implode(',',$items);
		$this->database->setQuery($query);
		$this->database->query();

		$this->database->setQuery('SELECT * FROM '.hikashop_table('order_product').' WHERE order_id='.$order_id);
		$newProducts = $this->database->loadObjectList('order_product_option_parent_id');
		$mainProducts = array();
		foreach($products as &$product) {
			if(!empty($product->cart_product_option_parent_id)) {
				$mainProducts[$product->cart_product_option_parent_id][] = $product->cart_product_id;
			}
			if(!empty($product->cart_product_id) && isset($newProducts[$product->cart_product_id]))
				$product->order_product_id = (int)$newProducts[$product->cart_product_id]->order_product_id;
		}
		unset($product);

		$keep = array();
		if(!empty($mainProducts)) {
			foreach($mainProducts as $k => $v) {
				$keep[]=(int)@$newProducts[$k]->order_product_id;
				$this->database->setQuery('UPDATE '.hikashop_table('order_product').' SET order_product_option_parent_id='.(int)@$newProducts[$k]->order_product_id.' WHERE order_product_option_parent_id IN ('.implode(',',$v).') AND order_id='.$order_id);
				$this->database->query();
			}
		}
		if(!empty($keep)) {
			$keep = ' AND order_product_option_parent_id NOT IN ('.implode(',',$keep).')';
		} else {
			$keep = '';
		}
		$this->database->setQuery('UPDATE '.hikashop_table('order_product').' SET order_product_option_parent_id=0 WHERE order_id='.$order_id.$keep);
		$this->database->query();

		if(!empty($discounts)) {
			$discountUpdates = array();
			foreach($discounts as $code => $qty) {
				$discountUpdates[$qty][]=$this->database->Quote($code);
			}
			foreach($discountUpdates as $k => $update) {
				$query = 'UPDATE '.hikashop_table('discount').' SET discount_used_times = discount_used_times + '.(int)$k.' WHERE discount_code IN ('.implode(',',$update).')';
				$this->database->setQuery($query);
				$this->database->query();
			}
		}
		return true;
	}

	function cancelProductReservation($order_id) {
		$query = 'SELECT * FROM '.hikashop_table('order_product').' WHERE order_id='.(int)$order_id;
		$this->database->setQuery($query);
		$items = $this->database->loadObjectList();

		if(!empty($items)){
			$updates = array();
			$productsQuantity = array();
			$class = hikashop_get('class.product');
			foreach($items as $item){
				if(empty($productsQuantity[(int)$item->product_id])){
					$productsQuantity[(int)$item->product_id] = $item->order_product_quantity;
				}else{
					$productsQuantity[(int)$item->product_id] += $item->order_product_quantity;
				}
				$prod=$class->get((int)$item->product_id);
				if(!empty($prod->product_parent_id) && $prod->product_type=='variant'){
					if(empty($productsQuantity[(int)$prod->product_parent_id])){
						$productsQuantity[(int)$prod->product_parent_id] = $item->order_product_quantity;
					}else{
						$productsQuantity[(int)$prod->product_parent_id] += $item->order_product_quantity;
					}
				}
			}
			if(!empty($productsQuantity)){
				foreach($productsQuantity as $id => $qty){
					if(empty($updates[$qty])){
						$updates[$qty] = array();
					}
					$updates[$qty][]=$id;
				}
			}
			foreach($updates as $k => $update){
				$query = 'UPDATE '.hikashop_table('product').' SET product_quantity = product_quantity + '.(int)$k.' WHERE product_id IN ('.implode(',',$update).') AND product_quantity > -1';
				$this->database->setQuery($query);
				$this->database->query();
				$query = 'UPDATE '.hikashop_table('product').' SET product_sales = product_sales - '.(int)$k.' WHERE product_id IN ('.implode(',',$update).') AND product_sales > 0';
				$this->database->setQuery($query);
				$this->database->query();
			}
		}
	}

	function resetProductReservation($order_id){
		$query = 'SELECT * FROM '.hikashop_table('order_product').' WHERE order_id='.(int)$order_id;
		$this->database->setQuery($query);
		$items = $this->database->loadObjectList();

		if(!empty($items)){
			$updates = array();
			$productsQuantity = array();
			$class = hikashop_get('class.product');
			foreach($items as $item){
				if(empty($productsQuantity[(int)$item->product_id])){
					$productsQuantity[(int)$item->product_id] = $item->order_product_quantity;
				}else{
					$productsQuantity[(int)$item->product_id] += $item->order_product_quantity;
				}
				$prod=$class->get((int)$item->product_id);
				if(!empty($prod->product_parent_id) && $prod->product_type=='variant'){
					if(empty($productsQuantity[(int)$prod->product_parent_id])){
						$productsQuantity[(int)$prod->product_parent_id] = $item->order_product_quantity;
					}else{
						$productsQuantity[(int)$prod->product_parent_id] += $item->order_product_quantity;
					}
				}
			}
			if(!empty($productsQuantity)){
				foreach($productsQuantity as $id => $qty){
					if(empty($updates[$qty])){
						$updates[$qty] = array();
					}
					$updates[$qty][]=$id;
				}
			}
			foreach($updates as $k => $update){
				$query = 'UPDATE '.hikashop_table('product').' SET product_quantity = product_quantity - '.(int)$k.' WHERE product_id IN ('.implode(',',$update).') AND product_quantity >= 0 AND product_quantity > '.(int)($k-1);
				$this->database->setQuery($query);
				$this->database->query();
				$query = 'UPDATE '.hikashop_table('product').' SET product_sales = product_sales + '.(int)$k.' WHERE product_id IN ('.implode(',',$update).')';
				$this->database->setQuery($query);
				$this->database->query();
			}
		}
	}

	function get($order_product_id,$default=null){
		$result = parent::get($order_product_id);
		if(!empty($result->order_product_tax_info)){
			$result->order_product_tax_info = unserialize($result->order_product_tax_info);
		}
		return $result;
	}

	function update(&$product){
		$old = null;
		if(!empty($product->order_product_id))
			$old = $this->get($product->order_product_id);

		$update_quantities = true;
		if(!empty($product->no_update_qty)) {
			unset($product->no_update_qty);
			$update_quantities = false;
		}
		if($update_quantities && (isset($product->change) || ((empty($old) && !empty($product->product_id)) || (!empty($old->product_id) && $old->order_product_quantity != $product->order_product_quantity)))) {
			$k = $product->order_product_quantity;
			if(!empty($old)){
				if(isset($product->change)){
					if($product->change == 'plus')
						$k = -(int)$product->order_product_quantity;
					elseif($product->change == 'minus')
						$k = (int)$product->order_product_quantity;
					unset($product->change);
				}else{
					$k = $product->order_product_quantity - $old->order_product_quantity;
				}
			}
			if(!empty($product->product_id))
				$product_id = (int)$product->product_id;
			else
				$product_id = (int)$old->product_id;

			$filters = array('product_id='.(int)$product_id);
			$productClass = hikashop_get('class.product');
			$prod = $productClass->get($product_id);
			if(!empty($prod) && $prod->product_type=='variant' && !empty($prod->product_parent_id)){
				$filters[] = 'product_id='.(int)$prod->product_parent_id;
			}
			$query = 'UPDATE '.hikashop_table('product').' SET product_quantity = product_quantity - '.$k.' WHERE ('.implode(' OR ',$filters).') AND product_quantity >= 0 AND product_quantity > '.(int)($k - 1);
			$this->database->setQuery($query);
			$this->database->query();
			$query = 'UPDATE '.hikashop_table('product').' SET product_sales = product_sales + '.$k.' WHERE product_sales >= '.(-$k).' AND ('.implode(' OR ',$filters).')';
			$this->database->setQuery($query);
			$this->database->query();
		}

		if(!empty($product->tax_namekey)){
			$tax = new stdClass();
			if(!empty($product->product_id) && !empty($old)){
				if(is_string($old->order_product_tax_info))
					$old->order_product_tax_info = unserialize($old->order_product_tax_info);
				$tax = reset($old->order_product_tax_info);
			}
			$tax->tax_namekey = $product->tax_namekey;
			$tax->tax_amount = $product->order_product_tax;
			$product->order_product_tax_info = array($tax);
		}

		if(isset($product->order_product_tax_info) && !is_string($product->order_product_tax_info)){
			$product->order_product_tax_info = serialize($product->order_product_tax_info);
		}

		if(empty($product->order_product_quantity)){
			return $this->delete($product->order_product_id);
		}
		if(isset($product->change)) unset($product->change);
		if(isset($product->tax_namekey)) unset($product->tax_namekey);
		$product->order_product_id = parent::save($product);
		return $product->order_product_id;
	}
}
