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
			<div class="btn"><a href="#save" onclick="return window.hikashop.submitform('save','hikashop_order_product_form');"><span class="btnIcon icon-32-apply"></span><span class="btnName">Save</span></a></div>
		</div>
		<div style="clear:right"></div>
	</div>
</div>
<form action="<?php echo hikashop_completeLink('order&task=save&subtask=products&tmpl=component'); ?>" name="hikashop_order_product_form" id="hikashop_order_product_form" method="post" enctype="multipart/form-data">
	<dl class="hika_options">
		<dt class="hikashop_order_product_id"><label><?php echo JText::_('PRODUCT'); ?></label></dt>
		<dd class="hikashop_order_product_id"><?php echo (int)@$this->orderProduct->product_id; ?> - <?php echo @$this->originalProduct->product_name; ?></dd>

		<dt class="hikashop_order_product_name"><label><?php echo JText::_('HIKA_NAME'); ?></label></dt>
		<dd class="hikashop_order_product_name">
			<input type="text" name="data[order][product][order_product_name]" value="<?php echo $this->escape(@$this->orderProduct->order_product_name); ?>" />
		</dd>

		<dt class="hikashop_order_product_code"><label><?php echo JText::_('PRODUCT_CODE'); ?></label></dt>
		<dd class="hikashop_order_product_code">
			<input type="text" name="data[order][product][order_product_code]" value="<?php echo $this->escape(@$this->orderProduct->order_product_code); ?>" />
		</dd>

		<dt class="hikashop_order_product_price"><label><?php echo JText::_('UNIT_PRICE'); ?></label></dt>
		<dd class="hikashop_order_product_price">
			<input type="text" id="hikashop_order_product_price_input" name="data[order][product][order_product_price]" value="<?php echo @$this->orderProduct->order_product_price; ?>" />
		</dd>

		<dt class="hikashop_order_product_vat"><label><?php echo JText::_('VAT'); ?></label></dt>
		<dd class="hikashop_order_product_vat">
			<input type="text" name="data[order][product][order_product_tax]" value="<?php echo @$this->orderProduct->order_product_tax; ?>" />
			<?php echo $this->ratesType->display( "data[order][product][tax_namekey]" , @$this->orderProduct->order_product_tax_info[0]->tax_namekey ); ?>
		</dd>

		<dt class="hikashop_order_product_quantity"><label><?php echo JText::_('PRODUCT_QUANTITY'); ?></label></dt>
		<dd class="hikashop_order_product_quantity">
			<input type="text" name="data[order][product][order_product_quantity]" value="<?php echo @$this->orderProduct->order_product_quantity; ?>" />
		</dd>

<?php
	if(!empty($this->extra_data['products'])) {
		foreach($this->extra_data['products'] as $key => $content) {
?>		<dt class="hikashop_order_product_<?php echo $key; ?>"><label><?php echo JText::_($content['title']); ?></label></dt>
		<dd class="hikashop_order_product_<?php echo $key; ?>"><?php echo $content['data']; ?></dd>
<?php
		}
	}

	if(!empty($this->fields['item'])) {
		$editCustomFields = true;
		foreach($this->fields['item'] as $fieldName => $oneExtraField) {
?>
		<dt class="hikashop_order_product_customfield hikashop_order_product_customfield_<?php echo $fieldName; ?>"><?php echo $this->fieldsClass->getFieldName($oneExtraField);?></dt>
		<dd class="hikashop_order_product_customfield hikashop_order_product_customfield_<?php echo $fieldName; ?>"><span><?php
			if($editCustomFields) {
				echo $this->fieldsClass->display($oneExtraField, @$this->orderProduct->$fieldName, 'data[order][product]['.$fieldName.']',false,'',true);
			} else {
				echo $this->fieldsClass->show($oneExtraField, @$this->orderProduct->$fieldName);
			}
		?></span></dd>
<?php
		}
	}
?>
		<dt class="hikashop_orderproduct_history"><label><?php echo JText::_('HISTORY'); ?></label></dt>
		<dd class="hikashop_orderproduct_history">
			<span><input onchange="window.orderMgr.orderproduct_history_changed(this);" type="checkbox" id="hikashop_history_orderproduct_store" name="data[history][store_data]" value="1"/><label for="hikashop_history_orderproduct_store" style="display:inline-block"><?php echo JText::_('SET_HISTORY_MESSAGE');?></label></span><br/>
			<textarea id="hikashop_history_orderproduct_msg" name="data[history][msg]" style="display:none;"></textarea>
		</dd>
<script type="text/javascript">
if(!window.orderMgr)
	window.orderMgr = {};
window.orderMgr.orderproduct_history_changed = function(el) {
	var fields = ['hikashop_history_orderproduct_msg'], displayValue = '';
	if(!el.checked) displayValue = 'none';
	window.hikashop.setArrayDisplay(fields, displayValue);
}
<?php if(!empty($this->extra_data['js'])) { echo $this->extra_data['js']; } ?>
</script>
	</dl>
	<input type="hidden" name="data[order][history][history_type]" value="modification" />
	<input type="hidden" name="data[order][product][order_product_id]" value="<?php echo @$this->orderProduct->order_product_id;?>" />
	<input type="hidden" name="data[order][product][product_id]" value="<?php echo @$this->orderProduct->product_id;?>" />
	<input type="hidden" name="data[order][product][order_id]" value="<?php echo @$this->orderProduct->order_id;?>" />
	<input type="hidden" name="data[products]" value="1" />
	<input type="hidden" name="cid[]" value="<?php echo @$this->orderProduct->order_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="save" />
	<input type="hidden" name="subtask" value="products" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="ctrl" value="order" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
