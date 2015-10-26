<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>	<legend><?php echo JText::_('ORDER_ADD_INFO'); ?></legend>
		<div class="hika_edit"><?php
			echo $this->popup->display(
				'<img src="'. HIKASHOP_IMAGES .'edit.png" alt=""/><span>'. JText::_('HIKA_EDIT') .'</span>',
				'HIKA_SET_ORDER_ADDITIONALS',
				hikashop_completeLink('order&task=edit&subtask=additional&cid='.$this->order->order_id, true),
				'hikashop_editadditional_popup',
				750, 460, 'onclick="return window.orderMgr.setAdditionals(this);"', '', 'link'
			);
		?></div>
<script type="text/javascript">
<!--
window.orderMgr.setAdditionals = function(el) {
	window.hikashop.submitFct = function(data) {
		var w = window, o = w.Oby;
		w.hikashop.closeBox();
		window.orderMgr.updateAdditional();
		o.fireAjax('hikashop.order_update', {el: 'additionnal', obj: data});
	};
	window.hikashop.openBox(el);
	return false;
}
//-->
</script>
	<table class="admintable table">
		<tr class="hikashop_order_additional_subtotal">
			<td class="key"><label><?php echo JText::_('SUBTOTAL'); ?></label></td>
			<td>
				<span>
					<?php
						if($this->config->get('price_with_tax')){
							echo $this->currencyHelper->format($this->order->order_subtotal,$this->order->order_currency_id);
						}else{
							echo $this->currencyHelper->format($this->order->order_subtotal_no_vat,$this->order->order_currency_id);
						} ?>
				</span>
			</td>
		</tr>
		<tr class="hikashop_order_additional_coupon">
			<td class="key"><label><?php echo JText::_('HIKASHOP_COUPON'); ?></label></td>
			<td>
				<span>
					<?php
						if($this->config->get('price_with_tax')){
							echo $this->currencyHelper->format($this->order->order_discount_price*-1.0,$this->order->order_currency_id);
						}else{
							echo $this->currencyHelper->format(($this->order->order_discount_price-@$this->order->order_discount_tax)*-1.0,$this->order->order_currency_id);
						} ?> <?php echo $this->order->order_discount_code; ?>
				</span>
			</td>
		</tr>
		<tr class="hikashop_order_additional_shipping">
			<td class="key"><label><?php echo JText::_('SHIPPING'); ?></label></td>
			<td><span>
			<?php
			if($this->config->get('price_with_tax')){
				echo $this->currencyHelper->format($this->order->order_shipping_price,$this->order->order_currency_id);
			}else{
				echo $this->currencyHelper->format($this->order->order_shipping_price-@$this->order->order_shipping_tax,$this->order->order_currency_id);
			}

			if(empty($this->order->order_shipping_method) && empty($this->order->shippings)) {
				echo '- <em>'.JText::_('NONE').'</em>';
			} else if(!empty($this->order->order_shipping_method)) {
				if(!is_numeric($this->order->order_shipping_id)){
					$shipping_name = $this->getShippingName($this->order->order_shipping_method, $this->order->order_shipping_id);
					echo ' - ' . $shipping_name;
				}else{
					$shipping = $this->shippingClass->get($this->order->order_shipping_id);
					echo ' - ' . $shipping->shipping_name;
				}
			} else {
				$shippings_data = array();
				$shipping_ids = explode(';', $this->order->order_shipping_id);
				foreach($shipping_ids as $key) {
					$shipping_data = '';
					list($k, $w) = explode('@', $key);
					$shipping_id = $k;
					if(isset($this->order->shippings[$shipping_id])) {
						$shipping = $this->order->shippings[$shipping_id];
						$shipping_data = $shipping->shipping_name;
					} else {
						foreach($this->order->products as $order_product) {
							if($order_product->order_product_shipping_id == $key) {
								if(!is_numeric($order_product->order_product_shipping_id)){
									$shipping_name = $this->getShippingName($order_product->order_product_shipping_method, $shipping_id);
									$shipping_data = $shipping_name;
								}else{
									$shipping_method_data = $this->shippingClass->get($shipping_id);
									$shipping_data = $shipping_method_data->shipping_name;
								}
								break;
							}
						}
						if(empty($shipping_data))
							$shipping_data = '[ ' . $key . ' ]';
					}
					if(isset($this->order->order_shipping_params->prices[$key])) {
						$price_params = $this->order->order_shipping_params->prices[$key];
						if($this->config->get('price_with_tax')){
							$shipping_data .= ' (' . $this->currencyHelper->format($price_params->price_with_tax, $this->order->order_currency_id) . ')';
						}else{
							$shipping_data .= ' (' . $this->currencyHelper->format($price_params->price_with_tax-@$price_params->tax, $this->order->order_currency_id) . ')';
						}
					}
					$shippings_data[] = $shipping_data;
				}
				if(!empty($shippings_data))
					echo '<ul><li>'.implode('</li><li>', $shippings_data).'</li></ul>';
			}
			?></span></td>
		</tr>
		<tr class="hikashop_order_additional_payment_fee">
			<td class="key"><label><?php echo JText::_('HIKASHOP_PAYMENT'); ?></label></td>
			<td><span><?php
			if($this->config->get('price_with_tax'))
				echo $this->currencyHelper->format($this->order->order_payment_price, $this->order->order_currency_id);
			else
				echo $this->currencyHelper->format($this->order->order_payment_price - @$this->order->order_payment_tax, $this->order->order_currency_id);
			?> - <?php
			if(empty($this->order->order_payment_method))
				echo '<em>'.JText::_('NONE').'</em>';
			else{
				if(!is_numeric($this->order->order_payment_id)){
					echo $this->order->order_payment_method.' '.$this->order->order_payment_id;
				}else{
					$payment = $this->paymentClass->get($this->order->order_payment_id);
					if(!empty($payment))
						echo $payment->payment_name;
					else
						echo $this->order->order_payment_method;
				}
			}
			?></span></td>
		</tr>
<?php
	if(!empty($this->order->additional)) {
		foreach($this->order->additional as $additional) {
?>
		<tr class="hikashop_order_additional_additional">
			<td class="key"><label><?php echo JText::_($additional->order_product_name); ?></label></td>
			<td><span><?php
			if(!empty($additional->order_product_price)) {
				$additional->order_product_price = (float)$additional->order_product_price;
			}
			if(!empty($additional->order_product_price) || empty($additional->order_product_options)) {
				echo $this->currencyHelper->format($additional->order_product_price, $this->order->order_currency_id);
			} else {
				echo $additional->order_product_options;
			}
		?></span></td>
		</tr>
<?php
		}
	}

	if($this->config->get('detailed_tax_display') && !empty($this->order->order_tax_info)) {
		foreach($this->order->order_tax_info as $tax){
?>
		<tr class="hikashop_order_additional_tax">
			<td class="key"><label><?php echo $tax->tax_namekey; ?></label></td>
			<td><span><?php
				echo $this->currencyHelper->format($tax->tax_amount,$this->order->order_currency_id);
			?></span></td>
		</tr>
<?php
		}
	}
?>
		<tr class="hikashop_order_additional_total">
			<td class="key"><label><?php echo JText::_('HIKASHOP_TOTAL'); ?></label></td>
			<td><span><?php echo $this->currencyHelper->format($this->order->order_full_price,$this->order->order_currency_id); ?></span></td>
		</tr>
<?php
	if(!empty($this->extra_data['additional'])) {
		foreach($this->extra_data['additional'] as $key => $content) {
?>		<tr class="hikashop_order_additional_<?php echo $key; ?>">
			<td class="key"><label><?php echo JText::_($content['title']); ?></label></td>
			<td><?php echo $content['data'] ?></td>
		</tr>
<?php
		}
	}

	if(!empty($this->fields['order'])) {
		foreach($this->fields['order'] as $fieldName => $oneExtraField) {
?>
		<tr class="hikashop_order_additional_customfield hikashop_order_additional_customfield_<?php echo $fieldName; ?>">
			<td class="key"><?php echo $this->fieldsClass->getFieldName($oneExtraField);?></td>
			<td><span><?php
			echo $this->fieldsClass->show($oneExtraField, @$this->order->$fieldName);
		?></span></td>
		</tr>
<?php
		}
	}
?>
	</table>
<script type="text/javascript">
window.orderMgr.updateAdditional = function() {
	window.Oby.xRequest('<?php echo hikashop_completeLink('order&task=show&subtask=additional&cid='.$this->order->order_id, true, false, true); ?>', {update: 'hikashop_order_field_additional'});
}
</script>
