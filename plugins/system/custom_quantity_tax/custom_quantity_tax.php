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
jimport('joomla.plugin.plugin');
class plgSystemCustom_quantity_tax extends JPlugin{

}
class plgSystemCustom_quantity_taxHelper{

	function quantityPrices(&$prices,$quantity,$total_quantity){
		$this->pricesSelection($prices,$total_quantity);
		$unitPrice = new stdClass();
		if(!empty($prices)){
			$unitPrice = reset($prices);
			if(count($prices)>1){
				$cheapest_value=$unitPrice->price_value;
				foreach($prices as $price){
					if($cheapest_value>$price->price_value){
						$unitPrice = $price;
						$cheapest_value = $price->price_value;
					}
				}
			}

			$this->quantityPrice($unitPrice,$quantity);
			$prices = array($unitPrice);
		}
	}
	function pricesSelection(&$prices,$quantity){
		$matches=array();
		$otherCurrencies=array();
		if(!empty($prices)){
			foreach($prices as $k2 => $price){
				if($price->price_min_quantity>$quantity) continue;
				if(empty( $price->price_orig_currency_id)){
					$matches[]=$price;
				}else{
					$otherCurrencies[]=$price;
				}
			}
		}
		if(empty($matches) && !empty($otherCurrencies)){
			$config =& hikashop_config();
			$main_currency = (int)$config->get('main_currency',1);
			foreach($otherCurrencies as $price){
				if($price->price_orig_currency_id==$main_currency){
					$matches[]=$price;
				}
			}
			if(empty($matches)){
				$matches = $otherCurrencies;
			}
		}
		$prices = $matches;
	}
	function quantityPrice(&$price,$quantity){
		if($quantity>0){
			$currencyHelper = hikashop_get('class.currency');

			if(empty($price->unit_price))
				$price->unit_price = new stdClass();
			$price->unit_price->price_currency_id = $price->price_currency_id;
			$rounding = $currencyHelper->getRounding($price->price_currency_id);
			if(empty($this->product->product_tax_id)){
				$class = hikashop_get('class.product');
				$data = $class->get(@$this->product->product_id);
				if(empty($data->product_tax_id) && $data->product_type=="variant"){
					$data = $class->get(@$data->product_parent_id);
				}
				$this->product->product_tax_id = @$data->product_tax_id;
			}
			if(isset($price->price_orig_currency_id)){
				$price->unit_price->price_orig_currency_id = $price->price_orig_currency_id;
			}
			if(isset($price->price_value_without_discount)){
				$price->unit_price->price_value_without_discount=round($price->price_value_without_discount,$rounding);
				$price->price_value_without_discount=round($price->unit_price->price_value_without_discount*$quantity,$rounding);
			}
			if(isset($price->price_value)){
				$price->unit_price->price_value=round($price->price_value,$rounding);
				$price->price_value=round($price->unit_price->price_value*$quantity,$rounding);
			}
			if(isset($price->price_orig_value)){
				$price->unit_price->price_orig_value=round($price->price_orig_value,$rounding);
				$price->price_orig_value=round($price->unit_price->price_orig_value*$quantity,$rounding);
			}
			if(isset($price->price_orig_value_with_tax)){
				$price->unit_price->price_orig_value_with_tax=round($price->price_orig_value_with_tax,$rounding);
				$price_quantity = $price->unit_price->price_orig_value*$quantity;
				$price->price_orig_value_with_tax=$currencyHelper->getTaxedPrice($price_quantity,hikashop_getZone(),$this->product->product_tax_id,$rounding);
			}
			if(isset($price->price_orig_value_without_discount)){
				$price->unit_price->price_orig_value_without_discount=round($price->price_orig_value_without_discount,$rounding);
				$price->price_orig_value_without_discount=round($price->unit_price->price_orig_value_without_discount*$quantity,$rounding);
			}
			if(isset($price->price_value_without_discount_with_tax)){
				$price->unit_price->price_value_without_discount_with_tax=round($price->price_value_without_discount_with_tax,$rounding);
				$price_quantity = $price->unit_price->price_value_without_discount*$quantity;
				$price->price_value_without_discount_with_tax=$currencyHelper->getTaxedPrice($price_quantity,hikashop_getZone(),$this->product->product_tax_id,$rounding);
			}

			if(isset($price->price_value_with_tax)){
				$price->unit_price->price_value_with_tax=round($price->price_value_with_tax,$rounding);
				$price_quantity = $price->unit_price->price_value*$quantity;
				$price->price_value_with_tax=$currencyHelper->getTaxedPrice($price_quantity,hikashop_getZone(),$this->product->product_tax_id,$rounding);
			}

			if(isset($price->taxes)){
				$price->unit_price->taxes = array();
				foreach($price->taxes as $k => $tax){
					$price->unit_price->taxes[$k]=clone($tax);
					$price_quantity = $price->unit_price->price_value*$quantity;
					$price->taxes[$k]->tax_amount = $currencyHelper->getTaxedPrice($price_quantity,hikashop_getZone(),$this->product->product_tax_id,7)-$price->unit_price->price_value*$quantity;
				}
			}
			if(isset($price->taxes_without_discount)){
				$price->unit_price->taxes_without_discount = array();
				foreach($price->taxes_without_discount as $k => $tax){
					$price->unit_price->taxes_without_discount[$k]=clone($tax);
					$price_quantity = $price->unit_price->price_value_without_discount*$quantity;
					$price->taxes_without_discount[$k]->tax_amount = $currencyHelper->getTaxedPrice($price_quantity,hikashop_getZone(),$this->product->product_tax_id,7)-$price->unit_price->price_value_without_discount*$quantity;
				}
			}

		}
	}
}

if(!function_exists('hikashop_product_price_for_quantity_in_cart')) {
	function hikashop_product_price_for_quantity_in_cart(&$product){
		$quantity = @$product->cart_product_quantity;
		$custom = new plgSystemCustom_quantity_taxHelper();
		$custom->product =& $product;
		if(isset($product->prices)) $custom->quantityPrices($product->prices,$quantity,$product->cart_product_total_quantity);
	}
}
