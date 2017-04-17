<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><h1 id="hikashop_address_form_header_iframe"><?php echo JText::_('ADDRESS_INFORMATION');?></h1>
<div id="hikashop_address_form_span_iframe">
	<form action="<?php echo hikashop_completeLink('address&task=save'); ?>" method="post" name="hikashop_address_form" enctype="multipart/form-data">
		<table>
<?php
	foreach($this->extraFields['address'] as $fieldName => $oneExtraField) {
?>
			<tr class="hikashop_address_<?php echo $fieldName;?>_line" id="hikashop_address_<?php echo $oneExtraField->field_namekey; ?>">
				<td class="key"><?php
					echo $this->fieldsClass->getFieldName($oneExtraField);
				?></td>
				<td><?php
					$onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';
					echo $this->fieldsClass->display(
						$oneExtraField,
						@$this->address->$fieldName,
						'data[address]['.$fieldName.']',
						false,
						' '.$onWhat.'="hikashopToggleFields(this.value,\''.$fieldName.'\',\'address\',0);"',
						false,
						$this->extraFields['address'],
						@$this->address
					);
				?></td>
				</tr>
<?php }	?>
		</table>
		<input type="hidden" name="Itemid" value="<?php global $Itemid; echo $Itemid; ?>"/>
		<input type="hidden" name="ctrl" value="address"/>
		<input type="hidden" name="tmpl" value="component"/>
		<input type="hidden" name="task" value="save"/>
		<input type="hidden" name="type" value="<?php echo JRequest::getVar('type',''); ?>"/>
		<input type="hidden" name="action" value="<?php echo JRequest::getVar('task',''); ?>"/>
		<input type="hidden" name="makenew" value="<?php echo JRequest::getInt('makenew'); ?>"/>
		<input type="hidden" name="redirect" value="<?php echo JRequest::getWord('redirect','');?>"/>
		<input type="hidden" name="step" value="<?php echo JRequest::getInt('step',-1);?>"/>
<?php
	if(!empty($address->address_user_id)){
		$id = $address->address_user_id;
	}else{
		$id = $this->user_id;
	}
?>
		<input type="hidden" name="data[address][address_user_id]" value="<?php echo $id;?>"/>
<?php
	if(!JRequest::getInt('makenew')){
?>
		<input type="hidden" name="data[address][address_id]" value="<?php echo (int)@$this->address->address_id;?>"/>
		<input type="hidden" name="address_id" value="<?php echo (int)@$this->address->address_id;?>"/>
<?php
	}
	echo JHTML::_( 'form.token' );
	echo $this->cart->displayButton(JText::_('OK'),'ok',$this->params,hikashop_completeLink('address&task=save'),'if(hikashopCheckChangeForm(\'address\',\'hikashop_address_form\')) document.forms[\'hikashop_address_form\'].submit(); return false;');
?>
	</form>
</div>
<div class="clear_both"></div>
