<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.combobox');
$hasContent = empty($this->item->module) || $this->item->module == 'custom' || $this->item->module == 'mod_custom';

$script = "Joomla.submitbutton = function(task)
	{
			if (task == 'module.cancel' || document.formvalidator.isValid(document.getElementById('module-form'))) {";
if ($hasContent)
{
	$script .= $this->form->getField('content')->save();
}
$script .= "	Joomla.submitform(task, document.getElementById('module-form'));
				if (self != top)
				{
					window.parent.jQuery('.modal').modal('hide');
				}
			}
	}";

JFactory::getDocument()->addScriptDeclaration($script);
?>
<div class="module-edit">

<form action="<?php echo JRoute::_('index.php?option=com_modules&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="module-form" class="form-validate">
	<div class="col main-section">
		<fieldset class="adminform">
			<legend><?php echo JText::_('JDETAILS'); ?></legend>
			<ul class="adminformlist">

			<li><?php echo $this->form->getLabel('title'); ?>
			<?php echo $this->form->getInput('title'); ?></li>

			<li><?php echo $this->form->getLabel('showtitle'); ?>
			<?php echo $this->form->getInput('showtitle'); ?></li>

			<li><?php echo $this->form->getLabel('position'); ?>
			<?php echo $this->form->getInput('custom_position'); ?>
			<label id="jform_custom_position-lbl" for="jform_custom_position" class="element-invisible"><?php echo JText::_('TPL_HATHOR_COM_MODULES_CUSTOM_POSITION_LABEL');?></label>
			<?php echo $this->form->getInput('position'); ?></li>

			<?php if ((string) $this->item->xml->name != 'Login Form') : ?>
			<li><?php echo $this->form->getLabel('published'); ?>
			<?php echo $this->form->getInput('published'); ?></li>
			<?php endif; ?>

			<li><?php echo $this->form->getLabel('access'); ?>
			<?php echo $this->form->getInput('access'); ?></li>

			<li><?php echo $this->form->getLabel('ordering'); ?>
			<?php echo $this->form->getInput('ordering'); ?></li>

			<?php if ((string) $this->item->xml->name != 'Login Form') : ?>
			<li><?php echo $this->form->getLabel('publish_up'); ?>
			<?php echo $this->form->getInput('publish_up'); ?></li>

			<li><?php echo $this->form->getLabel('publish_down'); ?>
			<?php echo $this->form->getInput('publish_down'); ?></li>
			<?php endif; ?>

			<li><?php echo $this->form->getLabel('language'); ?>
			<?php echo $this->form->getInput('language'); ?></li>

			<li><?php echo $this->form->getLabel('note'); ?>
			<?php echo $this->form->getInput('note'); ?></li>

			<?php if ($this->item->id) : ?>
				<li><?php echo $this->form->getLabel('id'); ?>
				<?php echo $this->form->getInput('id'); ?></li>
			<?php endif; ?>

			<li><?php echo $this->form->getLabel('module'); ?>
			<?php echo $this->form->getInput('module'); ?>
			<span class="faux-input"><?php if ($this->item->xml) echo ($text = (string) $this->item->xml->name) ? JText::_($text) : $this->item->module;else echo JText::_(COM_MODULES_ERR_XML);?></span></li>

			<li><?php echo $this->form->getLabel('client_id'); ?>
			<input type="text" size="35" id="jform_client_id" value="<?php echo $this->item->client_id == 0 ? JText::_('JSITE') : JText::_('JADMINISTRATOR'); ?>	" class="readonly" readonly="readonly" />
			<?php echo $this->form->getInput('client_id'); ?></li>
			</ul>
			<div class="clr"></div>

			<?php if ($this->item->xml) : ?>
				<?php if ($text = trim($this->item->xml->description)) : ?>
					<span class="faux-label">
						<?php echo JText::_('COM_MODULES_MODULE_DESCRIPTION'); ?>
					</span>
					<div class="clr"></div>
					<div class="readonly mod-desc extdescript">
						<?php echo JText::_($text); ?>
					</div>
				<?php endif; ?>
			<?php else : ?>
				<?php echo JText::_('COM_MODULES_ERR_XML'); ?>
			<?php endif; ?>
			<div class="clr"></div>
		</fieldset>
	</div>

	<div class="col options-section">
	<?php echo JHtml::_('sliders.start', 'module-sliders'); ?>
		<?php echo $this->loadTemplate('options'); ?>
	<?php echo JHtml::_('sliders.end'); ?>
	</div>

	<?php if ($hasContent) : ?>
		<div class="col main-section">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_MODULES_CUSTOM_OUTPUT'); ?></legend>
			<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('content'); ?>
			<div class="clr"></div>
				<?php echo $this->form->getInput('content'); ?></li>
			</ul>
		</fieldset>
		</div>
	<?php endif; ?>

	<?php if ($this->item->client_id == 0) :?>
	<div class="col main-section">
		<?php echo $this->loadTemplate('assignment'); ?>
	</div>
	<?php endif; ?>

	<div>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
</div>
