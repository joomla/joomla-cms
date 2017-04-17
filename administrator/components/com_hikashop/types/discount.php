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
class HikashopDiscountType{
	function load($form){
		$this->values = array();
		if(!$form){
			$this->values[] = JHTML::_('select.option', 'all',JText::_('HIKA_ALL') );
		}
		$this->values[] = JHTML::_('select.option', 'discount',JText::_('DISCOUNTS'));
		$this->values[] = JHTML::_('select.option', 'coupon',JText::_('COUPONS'));
	}
	function display($map,$value,$form=false){
		$this->load($form);
		$attribute='';
		if(!$form){
			$attribute = ' onchange="document.adminForm.submit( );"';
		}else{
			if(empty($value)){
				$value = 'discount';
			}
			$js = '
			function hikashopToggleDiscount(value){
				autoLoad = document.getElementById(\'hikashop_auto_load\');
				tax = document.getElementById(\'hikashop_tax\');
				minOrder = document.getElementById(\'hikashop_min_order\');
				hikashop_quota_per_user = document.getElementById(\'hikashop_quota_per_user\');
				hikashop_min_products = document.getElementById(\'hikashop_min_products\');
				hikashop_discount_coupon_product_only = document.getElementById(\'hikashop_discount_coupon_product_only\');
				hikashop_discount_coupon_nodoubling = document.getElementById(\'hikashop_discount_coupon_nodoubling\');
				hikashop_discount_affiliate = document.getElementById(\'hikashop_discount_affiliate\');
				if(value==\'discount\'){
					if(autoLoad) autoLoad.style.display = \'none\';
					if(tax) tax.style.display = \'none\';
					if(minOrder) minOrder.style.display = \'none\';
					if(hikashop_quota_per_user) hikashop_quota_per_user.style.display = \'none\';
					if(hikashop_min_products) hikashop_min_products.style.display = \'none\';
					if(hikashop_discount_coupon_product_only) hikashop_discount_coupon_product_only.style.display = \'none\';
					if(hikashop_discount_coupon_nodoubling) hikashop_discount_coupon_nodoubling.style.display = \'none\';
					if(hikashop_discount_affiliate) hikashop_discount_affiliate.style.display = \'none\';
				}else{
					if(autoLoad) autoLoad.style.display = \'\';
					if(tax) tax.style.display = \'\';
					if(minOrder) minOrder.style.display = \'\';
					if(hikashop_quota_per_user) hikashop_quota_per_user.style.display = \'\';
					if(hikashop_min_products) hikashop_min_products.style.display = \'\';
					if(hikashop_discount_coupon_product_only) hikashop_discount_coupon_product_only.style.display = \'\';
					if(hikashop_discount_coupon_nodoubling) hikashop_discount_coupon_nodoubling.style.display = \'\';
					if(hikashop_discount_affiliate) hikashop_discount_affiliate.style.display = \'\';
				}
			}
			window.hikashop.ready( function(){
				hikashopToggleDiscount(\''.$value.'\');
			});';
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration($js);
			$attribute = ' onchange="hikashopToggleDiscount(this.value);"';
		}
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"'.$attribute, 'value', 'text', $value );
	}

	public function displaySelector($map, $value, $delete = false, $type = 'coupon') {
		static $jsInit = null;

		$app = JFactory::getApplication();

		if($jsInit !== true) {
			$display_format = 'data.discount_code';
			if($app->isAdmin())
				$display_format = 'data.id + " - " + data.discount_code';

			$js = '
if(!window.localPage)
	window.localPage = {};
window.localPage.fieldSetDiscount = function(el, name) {
	window.hikashop.submitFct = function(data) {
		var d = document,
			elemInput = d.getElementById(name + "_input_id"),
			elemSpan = d.getElementById(name + "_span_id");
		if(elemInput) { elemInput.value = data.id; }
		if(elemSpan) { elemSpan.innerHTML = '.$display_format.'; }
	};
	window.hikashop.openBox(el,null,(el.getAttribute("rel") == null));
	return false;
}
window.localPage.fieldRemDiscount = function(el, name) {
	var d = document,
		elemInput = d.getElementById(name + "_input_id"),
		elemSpan = d.getElementById(name + "_span_id");
	if(elemInput) { elemInput.value = ""; }
	if(elemSpan) { elemSpan.innerHTML = " - "; }
}
';
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration($js);

			$jsInit = true;
		}

		$discountClass = hikashop_get('class.discount');
		$popup = hikashop_get('helper.popup');

		$name = str_replace(array('][','[',']'), '_', $map);
		$discount_id = (int)$value;
		$discount = $discountClass->get($discount_id);
		$discount_code = '';
		if(!empty($discount)) {
			$discount_code = @$discount->discount_code;
		} else {
			if(!empty($discount_id))
				$discount_code = '<em>'.JText::_('INVALID_DISCOUNT_CODE').'</em>';
			$discount_id = '';
		}

		$discount_display_name = $discount_code;
		if($app->isAdmin())
			$discount_display_name = $discount_id.' - '.$discount_code;

		if(empty($type) || !in_array($type, array('all','coupon','discount')))
			$type = 'all';

		$ret = '<span id="'.$name.'_span_id">'.$discount_display_name.'</span>' .
			'<input type="hidden" id="'.$name.'_input_id" name="'.$map.'" value="'.$discount_id.'"/> '.
			$popup->display(
				'<img src="'.HIKASHOP_IMAGES.'edit.png" style="vertical-align:middle;"/>',
				'DISCOUNT_SELECTION',
				hikashop_completeLink('discount&task=selection&filter_type='.$type.'&single=true', true),
				'hikashop_set_discount_'.$name,
				760, 480, 'onclick="return window.localPage.fieldSetDiscount(this,\''.$name.'\');"', '', 'link'
			);

		if($delete)
			$ret .= ' <a title="'.JText::_('HIKA_DELETE').'" href="#'.JText::_('HIKA_DELETE').'" onclick="return window.localPage.fieldRemDiscount(this, \''.$name.'\');"><img src="'.HIKASHOP_IMAGES.'cancel.png" style="vertical-align:middle;"/></a>';

		return $ret;
	}
}
