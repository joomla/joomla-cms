<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
if(empty($this->element->payment_params->common_payment_plugin)){
	$this->element->payment_params->common_payment_plugin = JRequest::getCmd('element', '');
}

$lang = JFactory::getLanguage();
$lang->load('plg_payment_'.$this->element->payment_params->common_payment_plugin, rtrim(JPATH_ADMINISTRATOR,DS).DS, $lang->getTag());
$path = JPATH_PLUGINS .DS.'payment'.DS.$this->element->payment_params->common_payment_plugin.DS.$this->element->payment_params->common_payment_plugin.'.xml';

$data = file_get_contents($path);
$form_def = '<?xml version="1.0" encoding="utf-8"?>'.preg_replace('#</config>.*#is','</form>',preg_replace('#(<\?xml version="1\.(1|0)" encoding="utf-8"\?>).*<config#is','<form',$data));
$form = JForm::getInstance('myform', $form_def);
$this->element->params = $this->element->payment_params;
$form->bind($this->element);
?>
<?php foreach ($form->getFieldsets('params') as $fieldsets => $fieldset){
		foreach($form->getFieldset($fieldset->name) as $field){
			if ($field->hidden){
					echo $field->input;
			}else{
			?>
			<tr>
				<td class="key">
						<?php echo $field->label; ?>
				</td>
				<td class="hikashop_override_backend_css" <?php echo ($field->type == 'Editor' || $field->type == 'Textarea') ? ' style="clear: both; margin: 0;"' : ''?>>
						<?php echo str_replace('name="params[','name="data[payment][payment_params][',$field->input); ?>
				</td>
			</tr>
			<?php
			}
		}
}
?>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][return_url]"><?php
			echo JText::_('RETURN_URL');
		?></label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][return_url]" value="<?php echo $this->escape(@$this->element->payment_params->return_url); ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][pending_status]"><?php
			echo JText::_('PENDING_STATUS');
		?></label>
	</td>
	<td><?php
		echo $this->data['order_statuses']->display("data[payment][payment_params][pending_status]", @$this->element->payment_params->pending_status);
	?></td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][verified_status]"><?php
			echo JText::_('VERIFIED_STATUS');
		?></label>
	</td>
	<td><?php
		echo $this->data['order_statuses']->display("data[payment][payment_params][verified_status]", @$this->element->payment_params->verified_status);
	?></td>
</tr>
<input type="hidden" name="data[payment][payment_params][common_payment_plugin]" value="<?php echo $this->element->payment_params->common_payment_plugin; ?>" />
