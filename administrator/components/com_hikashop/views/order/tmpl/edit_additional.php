<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hika_toolbar">
	<div class="hika_toolbar_btn hika_btn_32">
		<div class="hika_toolbar_right">
			<div class="btn"><a href="#save" onclick="document.getElementById('hikashop_order_notify').value = 1;return window.hikashop.submitform('save','hikashop_order_additional_form');"><span class="btnIcon icon-32-apply"></span><span class="btnName">Save & Notify</span></a></div>
			<div class="btn"><a href="#save" onclick="return window.hikashop.submitform('save','hikashop_order_additional_form');"><span class="btnIcon icon-32-apply"></span><span class="btnName">Save</span></a></div>
		</div>
		<div style="clear:right"></div>
	</div>
</div>
<form action="<?php echo hikashop_completeLink('order&task=save&subtask=additional&tmpl=component'); ?>" name="hikashop_order_additional_form" id="hikashop_order_additional_form" method="post" enctype="multipart/form-data">
	<dl class="hika_options">
		<dt class="hikashop_order_additional_subtotal"><label><?php echo JText::_('SUBTOTAL'); ?></label></dt>
		<dd class="hikashop_order_additional_subtotal"><span><?php echo $this->currencyHelper->format($this->order->order_subtotal,$this->order->order_currency_id); ?></span></dd>
<?php if(isset($this->edit) && $this->edit === true) { ?>
		<dt class="hikashop_order_additional_coupon"><label><?php echo JText::_('HIKASHOP_COUPON'); ?></label></dt>
		<dd class="hikashop_order_additional_coupon">
			<input type="text" name="data[order][order_discount_code]" value="<?php echo $this->escape(@$this->order->order_discount_code); ?>" /><br/>
			<input type="text" name="data[order][order_discount_price]" value="<?php echo @$this->order->order_discount_price; ?>" /><br/>
			<input name="data[order][order_discount_tax]" value="<?php echo @$this->order->order_discount_tax; ?>" />
			<?php echo $this->ratesType->display( "data[order][order_discount_tax_namekey]" , @$this->order->order_discount_tax_namekey ); ?>
		</dd>
<?php } else { ?>
		<dt class="hikashop_order_additional_coupon"><label><?php echo JText::_('HIKASHOP_COUPON'); ?></label></dt>
		<dd class="hikashop_order_additional_coupon"><span><?php echo $this->currencyHelper->format($this->order->order_discount_price*-1.0,$this->order->order_currency_id); ?> <?php echo $this->order->order_discount_code; ?></span></dd>
<?php }

	if(isset($this->edit) && $this->edit === true) { ?>
		<dt class="hikashop_order_additional_shipping"><label><?php echo JText::_('SHIPPING'); ?></label></dt>
		<dd class="hikashop_order_additional_shipping">
<?php if(strpos($this->order->order_shipping_id, ';') === false) { ?>
			<?php echo $this->shippingPlugins->display('data[order][shipping]',$this->order->order_shipping_method,$this->order->order_shipping_id); ?><br/>
<?php } ?>
			<input type="text" name="data[order][order_shipping_price]" value="<?php echo $this->order->order_shipping_price; ?>" /><br/>
			<input type="text" name="data[order][order_shipping_tax]" value="<?php echo @$this->order->order_shipping_tax; ?>" />
			<?php echo $this->ratesType->display( "data[order][order_shipping_tax_namekey]" , @$this->order->order_shipping_tax_namekey ); ?><br/>
<?php
		if(strpos($this->order->order_shipping_id, ';') !== false) {
?>
			<table class="hikam_table table table-striped">
				<thead>
					<tr>
						<th><?php echo JText::_('WAREHOUSE'); ?></th>
						<th><?php echo JText::_('HIKASHOP_SHIPPING_METHOD'); ?></th>
						<th><?php echo JText::_('SHIPPING_PRICE'); ?></th>
						<th><?php echo JText::_('SHIPPING_TAX'); ?></th>
					</tr>
				</thead>
				<tbody>
<?php
			$warehouses = array(
				JHTML::_('select.option', 0, JText::_('HIKA_NONE'))
			);
			$shipping_ids = explode(';', $this->order->order_shipping_id);
			foreach($shipping_ids as $shipping_key) {
				$shipping_warehouse = 0;
				if(strpos($shipping_key, '@') !== false)
					list($shipping_id, $shipping_warehouse) = explode('@', $shipping_key, 2);
				else
					$shipping_id = (int)$shipping_key;
				$warehouses[] = JHTML::_('select.option', $shipping_warehouse, $shipping_warehouse);
				$shipping_method = '';
				foreach($this->order->shippings as $s) {
					if((int)$s->shipping_id == $shipping_id) {
						$shipping_method = $s->shipping_type;
						break;
					}
				}
				$k = $shipping_id.'_'.$shipping_warehouse;
				$prices = @$this->order->order_shipping_params->prices[$shipping_key];
?>
					<tr>
						<td><?php echo $shipping_warehouse; ?></td>
						<td><?php echo $this->shippingPlugins->display('data[order][shipping]['.$shipping_warehouse.']',$shipping_method,$shipping_id, true, ' style="max-width:160px;"'); ?></td>
						<td><input type="text" name="data[order][order_shipping_prices][<?php echo $shipping_warehouse; ?>]" value="<?php echo @$prices->price_with_tax; ?>" /></td>
						<td><input type="text" name="data[order][order_shipping_taxs][<?php echo $shipping_warehouse; ?>]" value="<?php echo @$prices->tax; ?>" /></td>
					</tr>
<?php
			}
?>				</tbody>
			</table>
			<table class="hika_table table table-striped">
				<thead>
					<tr>
						<th><?php echo JText::_('PRODUCT'); ?></th>
						<th><?php echo JText::_('WAREHOUSE'); ?></th>
					</tr>
				</thead>
				<tbody>
<?php
			foreach($this->order->products as $k => $product) {
				$map = 'data[order][warehouses]['.$product->order_product_id.']';
				$value = 0;
				if(strpos($product->order_product_shipping_id, '@') !== false)
					$value = substr($product->order_product_shipping_id, strpos($product->order_product_shipping_id, '@')+1);
?>
					<tr>
						<td><?php echo $product->order_product_name; ?></td>
						<td><?php echo JHTML::_('select.genericlist', $warehouses, $map, 'class="inputbox"', 'value', 'text', $value); ?></td>
					</tr>
<?php
			}
?>
				</tbody>
			</table>
<?php
	} ?>
		</dd>
<?php } else { ?>
		<dt class="hikashop_order_additional_shipping"><label><?php echo JText::_('SHIPPING'); ?></label></dt>
		<dd class="hikashop_order_additional_shipping"><span><?php echo $this->currencyHelper->format($this->order->order_shipping_price, $this->order->order_currency_id); ?> - <?php
			if(empty($this->order->order_shipping_method))
				echo '<em>'.JText::_('HIKA_NONE').'</em>';
			else
				echo $this->order->order_shipping_method;
			?></span></dd>
<?php }

	if(isset($this->edit) && $this->edit === true) { ?>
		<dt class="hikashop_order_additional_payment"><label><?php echo JText::_('HIKASHOP_PAYMENT'); ?></label></dt>
		<dd class="hikashop_order_additional_payment">
			<?php echo $this->paymentPlugins->display('data[order][payment]',$this->order->order_payment_method,$this->order->order_payment_id); ?><br/>
			<input type="text" name="data[order][order_payment_price]" value="<?php echo $this->order->order_payment_price; ?>" /><br/>
			<input type="text" name="data[order][order_payment_tax]" value="<?php echo @$this->order->order_payment_tax; ?>" />
			<?php echo $this->ratesType->display( "data[order][order_payment_tax_namekey]" , @$this->order->order_payment_tax_namekey ); ?>
		</dd>
<?php } else { ?>
		<dt class="hikashop_order_additional_payment_fee"><label><?php echo JText::_('HIKASHOP_PAYMENT'); ?></label></dt>
		<dd class="hikashop_order_additional_payment_fee"><span><?php echo $this->currencyHelper->format($this->order->order_payment_price, $this->order->order_currency_id); ?> - <?php
			if(empty($this->order->order_payment_method))
				echo '<em>'.JText::_('HIKA_NONE').'</em>';
			else
				echo $this->order->order_payment_method;
			?></span></dd>
<?php }

	if(!empty($this->order->additional)) {
		foreach($this->order->additional as $additional) {
?>
		<dt class="hikashop_order_additional_additional"><label><?php echo JText::_($additional->order_product_name); ?></label></dt>
		<dd class="hikashop_order_additional_additional"><span><?php
			if(!empty($additional->order_product_price)) {
				$additional->order_product_price = (float)$additional->order_product_price;
			}
			if(!empty($additional->order_product_price) || empty($additional->order_product_options)) {
				echo $this->currencyHelper->format($additional->order_product_price, $this->order->order_currency_id);
			} else {
				echo $additional->order_product_options;
			}
		?></span></dd>
<?php
		}
	}
?>
		<dt class="hikashop_order_additional_total"><label><?php echo JText::_('HIKASHOP_TOTAL'); ?></label></dt>
		<dd class="hikashop_order_additional_total"><span><?php echo $this->currencyHelper->format($this->order->order_full_price,$this->order->order_currency_id); ?></span></dd>
<?php
	if(!empty($this->extra_data['additional'])) {
		foreach($this->extra_data['additional'] as $key => $content) {
?>		<dt class="hikashop_order_additional_<?php echo $key; ?>"><label><?php echo JText::_($content['title']); ?></label></dt>
		<dd class="hikashop_order_additional_<?php echo $key; ?>"><span><?php echo $content['data']; ?></span></dd>
<?php
		}
	}

	if(!empty($this->fields['order'])) {
		$editCustomFields = false;
		if(isset($this->edit) && $this->edit === true) {
			$editCustomFields = true;
		}
		foreach($this->fields['order'] as $fieldName => $oneExtraField) {
?>
		<dt class="hikashop_order_additional_customfield hikashop_order_additional_customfield_<?php echo $fieldName; ?>"><?php echo $this->fieldsClass->getFieldName($oneExtraField);?></dt>
		<dd class="hikashop_order_additional_customfield hikashop_order_additional_customfield_<?php echo $fieldName; ?>"><span><?php
			if($editCustomFields) {
				echo $this->fieldsClass->display($oneExtraField, @$this->order->$fieldName, 'data[orderfields]['.$fieldName.']');
			} else {
				echo $this->fieldsClass->show($oneExtraField, @$this->order->$fieldName);
			}
		?></span></dd>
<?php
		}
	}

?>
		<dt class="hikashop_orderadditional_history"><label><?php echo JText::_('HISTORY'); ?></label></dt>
		<dd class="hikashop_orderadditional_history">
			<span><input onchange="window.orderMgr.orderadditional_history_changed(this);" type="checkbox" id="hikashop_history_orderadditional_store" name="data[history][store_data]" value="1"/><label for="hikashop_history_orderadditional_store" style="display:inline-block"><?php echo JText::_('SET_HISTORY_MESSAGE');?></label></span><br/>
			<textarea id="hikashop_history_orderadditional_msg" name="data[history][msg]" style="display:none;"></textarea>
		</dd>
		<dd class="hikashop_orderadditional_usermsg">
			<span><input onchange="window.orderMgr.orderadditional_usermsg_changed(this);" type="checkbox" id="hikashop_history_orderadditional_usermsg_send" name="data[history][usermsg_send]" value="1"/><label for="hikashop_history_orderadditional_usermsg_send" style="display:inline-block"><?php echo JText::_('SEND_USER_MESSAGE');?></label></span><br/>
			<textarea id="hikashop_history_orderadditional_usermsg" name="data[history][usermsg]" style="display:none;"></textarea>
		</dd>
<script type="text/javascript">
if(!window.orderMgr)
	window.orderMgr = {};
window.orderMgr.orderadditional_history_changed = function(el) {
	var fields = ['hikashop_history_orderadditional_msg'], displayValue = '';
	if(!el.checked) displayValue = 'none';
	window.hikashop.setArrayDisplay(fields, displayValue);
}
window.orderMgr.orderadditional_usermsg_changed = function(el) {
	var fields = ['hikashop_history_orderadditional_usermsg'], displayValue = '';
	if(!el.checked) displayValue = 'none';
	window.hikashop.setArrayDisplay(fields, displayValue);
}
<?php if(!empty($this->extra_data['js'])) { echo $this->extra_data['js']; } ?>
</script>
	</dl>
	<input type="hidden" name="data[notify]" id="hikashop_order_notify" value="0" />
	<input type="hidden" name="data[additional]" value="1" />
	<input type="hidden" name="data[customfields]" value="1" />
	<input type="hidden" name="cid[]" value="<?php echo @$this->order->order_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="save" />
	<input type="hidden" name="subtask" value="additional" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="ctrl" value="order" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
