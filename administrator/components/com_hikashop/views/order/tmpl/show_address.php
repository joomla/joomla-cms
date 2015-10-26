<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><legend><?php echo JText::_('HIKASHOP_'.strtoupper($this->type).'_ADDRESS'); ?></legend>
<?php

$name = $this->type.'_address';
$show_url = 'order&task=show&subtask='.$name.'&cid='.$this->order->order_id;
$save_url = 'order&task=save&subtask='.$name.'&cid='.$this->order->order_id;
$update_url = 'order&task=edit&subtask='.$name.'&cid='.$this->order->order_id;

	if(!isset($this->edit) || $this->edit !== true ) {
?>		<div class="hika_edit"><a href="<?php echo hikashop_completeLink($update_url, true);?>" onclick="return window.hikashop.get(this,'hikashop_order_field_<?php echo $name; ?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt=""/><span><?php echo JText::_('HIKA_EDIT'); ?></span></a></div>
<?php
	} else {
?>		<div class="hika_edit">
			<a href="<?php echo hikashop_completeLink($save_url, true);?>" onclick="return window.hikashop.form(this,'hikashop_order_field_<?php echo $name; ?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>ok.png" alt=""/><span><?php echo JText::_('HIKA_SAVE'); ?></span></a>
			<a href="<?php echo hikashop_completeLink($show_url, true);?>" onclick="return window.hikashop.get(this,'hikashop_order_field_<?php echo $name; ?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>cancel.png" alt=""/><span><?php echo JText::_('HIKA_CANCEL'); ?></span></a>
		</div>
<?php
	}
?>
<table class="admintable table">
<?php
$address =& $this->order->$name;
$display = 'field_backend';
if(isset($this->edit) && $this->edit === true ) {
	foreach($this->order->fields as $field){
		if($field->$display){
			$fieldname = $field->field_namekey;
?>
	<tr class="hikashop_<?php echo $this->type;?>order_address_<?php echo $fieldname;?>">
		<td class="key"><label><?php echo $this->fieldsClass->trans($field->field_realname);?></label></td>
		<td><?php
			$onWhat = 'onchange';
			if($field->field_type == 'radio')
				$onWhat = 'onclick';

			$field->table_name = 'order';
			echo $this->fieldsClass->display(
					$field,
					@$address->$fieldname,
					'data['.$name.']['.$fieldname.']',
					false,
					' ' . $onWhat . '="hikashopToggleFields(this.value,\''.$fieldname.'\',\''.$name.'\',0);"',
					false,
					$this->order->fields,
					$address
			);
		?></td>
	</tr>
<?php
		}
	}
?>
	<tr class="hikashop_<?php echo $this->type;?>_history">
		<td class="key"><label><?php echo JText::_('HISTORY'); ?></label></td>
		<td>
			<span><input onchange="window.orderMgr.<?php echo $this->type;?>_history_changed(this);" type="checkbox" id="hikashop_history_<?php echo $this->type;?>_store" name="data[history][store_data]" value="1"/><label for="hikashop_history_<?php echo $this->type;?>_store" style="display:inline-block"><?php echo JText::_('SET_HISTORY_MESSAGE');?></label></span><br/>
			<textarea id="hikashop_history_<?php echo $this->type;?>_msg" name="data[history][msg]" style="display:none;"></textarea>
		</td>
	</tr>
</table>
<script type="text/javascript">
window.orderMgr.<?php echo $this->type;?>_history_changed = function(el) {
	var fields = ['hikashop_history_<?php echo $this->type;?>_msg'], displayValue = '';
	if(!el.checked) displayValue = 'none';
	window.hikashop.setArrayDisplay(fields, displayValue);
}
</script>
<?php

	echo JHTML::_( 'form.token' );
} else {
	foreach($this->order->fields as $field){
		if($field->$display){
			$fieldname = $field->field_namekey;
?>
	<tr class="hikashop_<?php echo $this->type;?>order_address_<?php echo $fieldname;?>">
		<td class="key"><label><?php echo $this->fieldsClass->trans($field->field_realname);?></label></td>
		<td><span><?php echo $this->fieldsClass->show($field, @$address->$fieldname);?></span></td>
	</tr>
<?php
		}
	}
?></table>
<?php
}
?>
<script type="text/javascript">
window.orderMgr.update<?php echo ucfirst($this->type);?> = function() {
	window.Oby.xRequest('<?php echo hikashop_completeLink('order&task=show&subtask='.$this->type.'_address&cid='.$this->order->order_id, true, false, true); ?>',{update:'hikashop_order_field_<?php echo $this->type; ?>_address'});
}
</script>
