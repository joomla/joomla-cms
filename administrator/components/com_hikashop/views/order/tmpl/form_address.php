<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php $this->typeU=strtoupper($this->type);
if(empty($this->order->fields)) return;
?>
<fieldset class="adminform" id="htmlfieldset_<?php echo $this->type; ?>">
	<legend><?php echo JText::_('HIKASHOP_'. $this->typeU .'_ADDRESS'); ?></legend>
	<?php
	$name = $this->type.'_address';

	if(($this->type!='shipping' || !empty($this->order->order_shipping_address_id)) && !empty($this->order->$name)){
		$address =& $this->order->$name;
		if(empty($this->nobutton)){
		?>
		<div style="float:right">
			<?php echo $this->popup->display(
				'<img style="vertical-align:middle;" alt="'.JText::_('HIKA_EDIT').'" src="'. HIKASHOP_IMAGES.'edit.png"/>',
				'HIKA_EDIT',
				hikashop_completeLink('order&task=address&address_id='.$address->address_id.'&type='.$this->type.'&order_id='.$this->order->order_id,true),
				'order_'.$this->type.'_popup',
				760, 480, '', '', 'link'
			); ?>
		</div>
		<?php }?>
		<table class="admintable table">
		<?php
		if(empty($this->display_type)){
			$this->display_type = 'backend';
		}
		$display = 'field_'.$this->display_type;
		foreach($this->order->fields as $field){
			if($field->$display){
				$fieldname = $field->field_namekey;
				?>
				<tr>
					<td class="key">
						<?php echo $this->fieldsClass->getFieldName($field);?>
					</td>
					<td>
						<?php echo $this->fieldsClass->show($field,$address->$fieldname);?>
					</td>
				</tr>
				<?php
			}
		}
		?>
		</table>

		<?php
	}else{
		?>
		<div style="float:right">
			<?php echo $this->popup->display(
				'<img style="vertical-align:middle;" alt="'.JText::_('HIKA_ADD').'" src="'. HIKASHOP_IMAGES.'add.png"/>',
				'HIKA_ADD',
				hikashop_completeLink('order&task=address&type='.$this->type.'&order_id='.$this->order->order_id,true),
				'order_'.$this->type.'_popup',
				760, 480, '', '', 'link'
			); ?>
		</div>
		<?php
	}
	?>
</fieldset>
