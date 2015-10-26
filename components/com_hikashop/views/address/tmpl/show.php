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
$name = $this->type.'_address';
$uniq_id = 'hikashop_address_'.$this->type.'_'.$this->address_id;
$pfid = '';
if(!empty($this->fieldset_id))
	$pfid = '&fid='.$this->fieldset_id;
else
	$this->fieldset_id = $uniq_id;

$show_url = 'address&task=show&subtask='.$this->type.'&cid='.$this->address_id.$pfid;
$save_url = 'address&task=save&subtask='.$this->type.'&cid='.$this->address_id.$pfid;
$update_url = 'address&task=edit&subtask='.$this->type.'&cid='.$this->address_id.$pfid;
$delete_url = 'address&task=delete&subtask='.$this->type.'&cid='.$this->address_id.'&'.hikashop_getFormToken().'=1';

?><div id="<?php echo $uniq_id; ?>">
<?php
	if(!isset($this->edit) || $this->edit !== true ) {
?>		<div class="hika_edit">
			<a href="<?php echo hikashop_completeLink($update_url, true);?>" id="<?php echo $uniq_id; ?>_edit" onclick="return window.hikashop.get(this,'<?php echo $this->fieldset_id; ?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt=""/><span><?php echo JText::_('HIKA_EDIT'); ?></span></a>
			<a href="<?php echo hikashop_completeLink($delete_url, true);?>" id="<?php echo $uniq_id; ?>_delete" onclick="return window.addressMgr.delete(this,<?php echo $this->address_id; ?>,'<?php echo $uniq_id; ?>','<?php echo $this->type; ?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt=""/><span><?php echo JText::_('HIKA_DELETE'); ?></span></a>
		</div>
<?php
	} else {
?>		<div class="hika_edit">
			<a href="<?php echo hikashop_completeLink($save_url, true);?>" onclick="return window.hikashop.form(this,'<?php echo $this->fieldset_id; ?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>ok.png" alt=""/><span><?php echo JText::_('HIKA_SAVE'); ?></span></a>
			<a href="<?php echo hikashop_completeLink($show_url, true);?>" onclick="return window.hikashop.get(this,'<?php echo $this->fieldset_id; ?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>cancel.png" alt=""/><span><?php echo JText::_('HIKA_CANCEL'); ?></span></a>
		</div>
<?php
	}
?>
<?php
$display = 'field_backend';
if(isset($this->edit) && $this->edit === true ) {
?>
<table class="admintable table">
<?php
	foreach($this->fields as $field){
		if($field->$display){
			$fieldname = $field->field_namekey;
?>
	<tr class="hikashop_<?php echo $this->type;?>_address_<?php echo $fieldname;?>" id="hikashop_<?php echo $this->type; ?>_address_<?php echo $fieldname; ?>">
		<td class="key"><label><?php echo $this->fieldsClass->trans($field->field_realname);?></label></td>
		<td><?php
			$onWhat = 'onchange';
			if($field->field_type == 'radio')
				$onWhat = 'onclick';

			$field->table_name = 'order';
			echo $this->fieldsClass->display(
					$field,
					@$this->address->$fieldname,
					'data['.$name.']['.$fieldname.']',
					false,
					' ' . $onWhat . '="hikashopToggleFields(this.value,\''.$fieldname.'\',\''.$name.'\',0);"',
					false,
					$this->fields,
					$this->address
			);
		?></td>
	</tr>
<?php
		}
	}
?>
</table>
<?php
} else {

	if(false) {
?>
<table class="admintable table">
<?php
		foreach($this->fields as $field){
			if($field->$display){
				$fieldname = $field->field_namekey;
?>
	<tr class="hikashop_<?php echo $this->type;?>order_address_<?php echo $fieldname;?>">
		<td class="key"><label><?php echo $this->fieldsClass->trans($field->field_realname);?></label></td>
		<td><span><?php echo $this->fieldsClass->show($field, @$this->address->$fieldname);?></span></td>
	</tr>
<?php
			}
		}
?>
</table>
<?php
	} else {
?>
<div class="hikashop_address_content" onclick="return window.addressMgr.click(this,<?php echo $this->address_id;?>,'<?php echo $uniq_id; ?>','<?php echo $this->type; ?>');">
<?php
		if(empty($this->addressClass))
			$this->addressClass = hikashop_get('class.address');
		echo $this->addressClass->displayAddress($this->fields,$this->address,'address');
?>
</div>
<?php
	}
}

if(isset($this->edit) && $this->edit === true) {
	echo '<input type="hidden" name="data['.$name.'][address_id]" value="'.$this->address_id.'"/>';
	echo JHTML::_( 'form.token' );
}
?>
<script type="text/javascript">
if(!window.addressMgr) window.addressMgr = {};
window.addressMgr.update<?php echo ucfirst($this->type);?> = function() {
	window.Oby.xRequest('<?php echo hikashop_completeLink('address&task=show&subtask='.$this->type.'_address&cid='.$this->address_id, true, false, true); ?>',{update:'<?php echo $this->fieldset_id; ?>'});
};
<?php
static $hikashop_address_show_js_init = false;
if(!$hikashop_address_show_js_init) {
	$hikashop_address_show_js_init = true;
?>
window.addressMgr.delete = function(el, cid, uid, type) {
	if(!confirm('<?php echo JText::_('HIKASHOP_CONFIRM_DELETE_ADDRESS', true); ?>'))
		return false;
	var w = window, o = w.Oby, d = document;
	o.xRequest(el.href, null, function(xhr) { if(xhr.status == 200) {
		if(xhr.responseText == '1') {
			var target = d.getElementById(uid);
			if(target) target.parentNode.removeChild(target);
			window.Oby.fireAjax('hikashop_address_deleted',{'type':type,'cid':cid,'uid':uid,'el':el});
		} else if(xhr.responseText != '0')
			o.updateElem(uid, xhr.responseText);
	}});
	return false;
};
window.addressMgr.click = function(el, cid, uid, type) { window.Oby.fireAjax('hikashop_address_click',{'type':type,'cid':cid,'uid':uid,'el':el}); }
<?php
}

if(JRequest::getVar('tmpl', '') == 'component') {
	if(empty($this->addressClass))
		$this->addressClass = hikashop_get('class.address');
	$miniFormat = $this->addressClass->miniFormat($this->address);
?>
window.Oby.fireAjax('hikashop_address_changed',{'type':'<?php echo $this->type; ?>','edit':<?php echo $this->edit?'1':'0'; ?>,'cid':<?php echo $this->address_id; ?>,'miniFormat':'<?php echo str_replace('\'','\\\'', $miniFormat); ?>'<?php
	$previous_id = JRequest::getVar('previous_cid', null);
	if((!empty($previous_id) || $previous_id === 0) && is_int($previous_id))
		echo ',\'previous_cid\':' . $previous_id;
?>});
<?php
	echo $this->init_js;
}
?>
</script>
</div>
