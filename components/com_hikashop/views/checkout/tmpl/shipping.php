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
if(empty($this->rates)) {
	return;
}
?><div class="hikashop_shipping_methods" id="hikashop_shipping_methods">
	<fieldset>
		<legend><?php echo JText::_('HIKASHOP_SHIPPING_METHOD');?></legend>
<?php
	$this->setLayout('listing_price');
	$this->params->set('show_quantity_field', 0);
	$auto_select_default = $this->config->get('auto_select_default', 2);
	if($auto_select_default == 1 && count($this->rates) > 1)
		$auto_select_default=0;

	$several_groups = false;
	if(count($this->shipping_groups) > 1)
		$several_groups = true;

	foreach($this->shipping_groups as $shipping_group_key => $group) {
?>
	<div class="hikashop_shipping_group">
<?php if(!empty($group->name) || $several_groups) { ?>
		<p class="hikashop_shipping_group_name"><?php
			if(!empty($group->name))
				echo $group->name;
			elseif($several_groups)
				echo JText::_('SHIPPING_INFORMATION');
		?></p>
<?php
		}

		if($several_groups) {
?>
		<ul class="hikashop_shipping_products">
<?php
			foreach($group->products as $product) {
				if($product->cart_product_quantity <= 0)
					continue;

				$thumbnail = '';
				if(!empty($product->images))
					$thumbnail = $product->images[0]->file_path;
				$image = $this->imageHelper->getThumbnail($thumbnail, array(50,50), array('default' => true), true);
				$name = str_replace('"', '&quot;', strip_tags($product->product_name));
				if(!empty($image->success)) {
					echo '<li class="hikashop_shipping_product"><img src="' . $image->url . '" alt="' . $name . '" title="' . $name . '"/></li>';
				} else {
					echo '<li class="hikashop_shipping_product"><span>'.$product->product_name.'</span></li>';
				}
			}
?>
		</ul>
<?php
		}

if(!HIKASHOP_RESPONSIVE) { ?>
		</ul>
		<table>
<?php } else { ?>
		</ul>
<div class="controls">
	<div class="hika-radio">
		<table class="hikashop_shipping_methods_table table table-striped table-hover">
<?php }

		$k = 0;
		$done = false;
		$group_rates = $group->shippings;
		if(is_int($shipping_group_key))
			$shipping_group_key = ''.$shipping_group_key;

		$keys = array(); $shipping_group_struct = array();
		if(preg_match_all('#([a-zA-Z])*([0-9]+)#iu', $shipping_group_key, $keys)) {
			$shipping_group_struct = array_combine($keys[1], $keys[2]);
		}

		foreach($this->rates as $rate) {
			if(is_int($rate->shipping_warehouse_id))
				$rate->shipping_warehouse_id = ''.$rate->shipping_warehouse_id;
			if(isset($rate->shipping_warehouse_id) && $rate->shipping_warehouse_id !== $shipping_group_key) {
				$keys = array();
				$continue = true;
				$tmp = array('' => $rate->shipping_warehouse_id);
				if(is_string($rate->shipping_warehouse_id) && preg_match_all('#([a-zA-Z])*([0-9]+)#iu', $rate->shipping_warehouse_id, $keys)) {
					$tmp = array_combine($keys[1], $keys[2]);
				}
				if(is_array($rate->shipping_warehouse_id))
					$tmp = $rate->shipping_warehouse_id;

				if($tmp[''] == $shipping_group_struct['']) {
					$continue = false;
					foreach($tmp as $k => $v) {
						if(!isset($shipping_group_struct[$k]) || $shipping_group_struct[$k] != $v) {
							$continue = true;
							break;
						}
					}
				}
				if($continue)
					continue;
			}
			if(!in_array($rate->shipping_id, $group_rates))
				continue;

			$shipping_key = '';
			if($several_groups)
				$shipping_key = '_' . $shipping_group_key;

			$checked = '';
			if((in_array($rate->shipping_type.'@'.$shipping_group_key, $this->shipping_method) && in_array($rate->shipping_id.'@'.$shipping_group_key, $this->shipping_id)) || ($auto_select_default && empty($this->shipping_id) && !$done)) {
				$done = true;
				$checked = 'checked="checked"';
			}

			if($this->config->get('auto_submit_methods',1) && empty($checked))
				$checked.=' onclick="this.form.action=this.form.action+\'#hikashop_shipping_methods\';this.form.submit(); return false;"';

			if(empty($rate->shipping_price_with_tax))
				$rate->shipping_price_with_tax = $rate->shipping_price;

			if(empty($rate->shipping_price))
				$rate->shipping_price = $rate->shipping_price_with_tax;

			$taxes = round($rate->shipping_price_with_tax-$rate->shipping_price,$this->currencyHelper->getRounding($rate->shipping_currency_id));
			$prices_taxes = 1;
			if(bccomp($taxes,0,5) == 0)
				$prices_taxes = 0;

			$price_text = '';
			if(bccomp($rate->shipping_price,0,5) === 0) {
				$price_text = JText::_('FREE_SHIPPING');
			} else {
				$price_text .= JText::_('PRICE_BEGINNING');
				$price_text .= '<span class="hikashop_checkout_shipping_price">';
				if($this->params->get('price_with_tax')){
					$price_text .= $this->currencyHelper->format($rate->shipping_price_with_tax,$rate->shipping_currency_id);
				}
				if($this->params->get('price_with_tax')==2){
					$price_text .= JText::_('PRICE_BEFORE_TAX');
				}
				if($this->params->get('price_with_tax')==2||!$this->params->get('price_with_tax')){
					$price_text .= $this->currencyHelper->format($rate->shipping_price,$rate->shipping_currency_id);
				}
				if($this->params->get('price_with_tax')==2){
					$price_text .= JText::_('PRICE_AFTER_TAX');
				}

				if($this->params->get('show_original_price') && isset($rate->shipping_price_orig) && bccomp($rate->shipping_price_orig, 0, 5)) {
					$price_text .= JText::_('PRICE_BEFORE_ORIG');
					if($this->params->get('price_with_tax')){
						$price_text .= $this->currencyHelper->format($rate->shipping_price_orig_with_tax, $rate->shipping_currency_id_orig);
					}
					if($this->params->get('price_with_tax')==2){
						$price_text .= JText::_('PRICE_BEFORE_TAX');
					}
					if($this->params->get('price_with_tax')==2||!$this->params->get('price_with_tax')){
						$price_text .= $this->currencyHelper->format($rate->shipping_price_orig, $rate->shipping_currency_id_orig);
					}
					if($this->params->get('price_with_tax')==2){
						$price_text .= JText::_('PRICE_AFTER_TAX');
					}
					$price_text .= JText::_('PRICE_AFTER_ORIG');
				}
				$price_text .= '</span> ';
				$price_text .= JText::_('PRICE_END');
			}
?>
		<tr class="row<?php echo $k; ?>">
<?php if(!HIKASHOP_RESPONSIVE) { ?>
			<td>
				<input class="hikashop_checkout_shipping_radio" type="radio" name="hikashop_shipping<?php echo $shipping_key;?>" id="hikashop_shipping_<?php echo $rate->shipping_type.'_'.$rate->shipping_id . $shipping_key;?>" value="<?php echo $rate->shipping_type.'_'.$rate->shipping_id . $shipping_key;?>" <?php echo $checked; ?> />
			</td>
			<td><label for="hikashop_shipping_<?php echo $rate->shipping_type.'_'.$rate->shipping_id . $shipping_key;?>" style="cursor:pointer;">
				<span class="hikashop_checkout_shipping_image">
<?php } else { ?>
			<td>
				<input class="hikashop_checkout_shipping_radio" type="radio" name="hikashop_shipping<?php echo $shipping_key;?>" id="hikashop_shipping_<?php echo $rate->shipping_type.'_'.$rate->shipping_id . $shipping_key;?>" value="<?php echo $rate->shipping_type.'_'.$rate->shipping_id . $shipping_key;?>" <?php echo $checked; ?> />
				<label class="btn btn-radio" for="hikashop_shipping_<?php echo $rate->shipping_type.'_'.$rate->shipping_id . $shipping_key;?>"><?php echo $rate->shipping_name;?></label>
				<span class="hikashop_checkout_shipping_price_full"><?php echo $price_text; ?></span>
				<span class="hikashop_checkout_payment_image">
<?php }
			if(!empty($rate->shipping_images)){
				$images = explode(',',$rate->shipping_images);
				if(!empty($images)){
					foreach($images as $image){
						if(!empty($this->images_shipping[$image])){
?>
							<img src="<?php echo HIKASHOP_IMAGES .'shipping/'.  $this->images_shipping[$image];?>" alt=""/>
<?php
							}
						}
					}
				}
?>
				</span>
<?php if(!HIKASHOP_RESPONSIVE) { ?>
				</label>
			</td>
			<td><label for="hikashop_shipping_<?php echo $rate->shipping_type.'_'.$rate->shipping_id. $shipping_key;?>" style="cursor:pointer;">
				<span class="hikashop_checkout_shipping_name"><?php echo $rate->shipping_name;?></span>
				<span class="hikashop_checkout_shipping_price_full"><?php echo $price_text; ?></span>
				</label>
				<br/>
<?php } ?>
				<div class="hikashop_checkout_shipping_description"><?php echo $rate->shipping_description;?></div>
			</td>
		</tr>
<?php

		$k = 1-$k;
	}
	if($several_groups && empty($group_rates)) {
?>
		<tr>
			<td colspan="3">
				<?php echo JText::_('NO_SHIPPING_REQUIRED'); ?>
				<input type="radio" style="display:none;" name="hikashop_shipping_<?php echo $shipping_group_key; ?>" value="-_<?php echo $shipping_group_key; ?>" checked="checked" />
			</td>
		</tr>
<?php
	}

	if(!HIKASHOP_RESPONSIVE) {
?>		</table>
<?php
	} else {
?>		</table>
	</div>
</div>
<?php } ?>
	</div>
<?php
	}

	if(HIKASHOP_RESPONSIVE) {
?>
<script type="text/javascript">
(function($){
	$("#hikashop_shipping_methods .hika-radio input:checked").each(function() {
		$("label[for=" + jQuery(this).attr('id') + "]").addClass('active btn-primary');
	});
	$("#hikashop_shipping_methods .hika-radio input").change(function() {
		$(this).parents('div.hika-radio').find('label.active').removeClass('active btn-primary');
		$("label[for=" + jQuery(this).attr('id') + "]").addClass('active btn-primary');
	});
})(jQuery);
</script>
<?php
	}
?>
		<input type="hidden" name="hikashop_shippings" value="<?php echo implode(';', array_keys($this->shipping_groups)); ?>"/>
	</fieldset>
</div>
