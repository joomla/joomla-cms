<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="btn" type="button" onclick="submitbutton('saveaddress');"><img src="<?php echo HIKASHOP_IMAGES; ?>save.png"/><?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('order',true); ?>" method="post"  name="adminForm" id="adminForm">
	<table width="100%" class="admintable table">
	<?php
		$name = $this->type.'_address';

		if(!empty($this->element->$name)){
			$address =& $this->element->$name;
			foreach($this->element->fields as $field){
				if($field->field_backend){
					$fieldname = $field->field_namekey;
					?>
					<tr>
						<td class="key">
							<?php echo $this->fieldsClass->getFieldName($field);?>
						</td>
						<td>
							<?php echo $this->fieldsClass->display($field,$address->$fieldname,'data[address]['.$fieldname.']'); ?>
						</td>
					</tr>
					<?php
				}
			}
		}
		$this->setLayout('notification'); echo $this->loadTemplate();?>
	</table>
	<input type="hidden" name="data[order][history][history_type]" value="modification" />
	<input type="hidden" name="data[order][order_id]" value="<?php echo @$this->element->order_id;?>" />
	<input type="hidden" name="data[address][address_user_id]" value="<?php echo @$address->address_user_id;?>" />
	<input type="hidden" name="data[address][address_id]" value="<?php echo @$this->id;?>" />
	<input type="hidden" name="type" value="<?php echo @$this->type;?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="order" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
