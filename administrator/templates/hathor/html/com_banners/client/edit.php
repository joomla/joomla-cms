<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'client.cancel' || document.formvalidator.isValid(document.id('client-form')))
		{
			Joomla.submitform(task, document.getElementById('client-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_banners&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="client-form" class="form-validate">

<div class="col main-section">
	<fieldset class="adminform">
		<legend><?php echo empty($this->item->id) ? JText::_('COM_BANNERS_NEW_CLIENT') : JText::sprintf('COM_BANNERS_EDIT_CLIENT', $this->item->id); ?></legend>
		<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('name'); ?>
				<?php echo $this->form->getInput('name'); ?></li>

				<li><?php echo $this->form->getLabel('contact'); ?>
				<?php echo $this->form->getInput('contact'); ?></li>

				<li><?php echo $this->form->getLabel('email'); ?>
				<?php echo $this->form->getInput('email'); ?></li>

				<?php if ($canDo->get('core.edit.state')) : ?>
					<li><?php echo $this->form->getLabel('state'); ?>
					<?php echo $this->form->getInput('state'); ?></li>
				<?php endif; ?>

				<li><?php echo $this->form->getLabel('purchase_type'); ?>
				<?php echo $this->form->getInput('purchase_type'); ?></li>

				<li><?php echo $this->form->getLabel('track_impressions'); ?>
				<?php echo $this->form->getInput('track_impressions'); ?></li>

				<li><?php echo $this->form->getLabel('track_clicks'); ?>
				<?php echo $this->form->getInput('track_clicks'); ?></li>

				<li><?php echo $this->form->getLabel('id'); ?>
				<?php echo $this->form->getInput('id'); ?></li>
		</ul>

	</fieldset>
</div>

<div class="col options-section">
	<?php echo JHtml::_('sliders.start', 'banner-client-sliders-' . $this->item->id, array('useCookie' => 1)); ?>

	<?php echo JHtml::_('sliders.panel', JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'), 'metadata'); ?>
		<fieldset class="panelform">
		<legend class="element-invisible"><?php echo JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'); ?></legend>
		<ul class="adminformlist">
			<?php foreach ($this->form->getFieldset('metadata') as $field) : ?>
				<li>
					<?php if (!$field->hidden) : ?>
						<?php echo $field->label; ?>
					<?php endif; ?>
					<?php echo $field->input; ?>
				</li>
			<?php endforeach; ?>
			</ul>
		</fieldset>

	<?php echo JHtml::_('sliders.panel', JText::_('COM_BANNERS_EXTRA'), 'extra'); ?>
		<fieldset class="panelform">
		<legend class="element-invisible"><?php echo JText::_('COM_BANNERS_EXTRA'); ?></legend>
		<ul class="adminformlist">
			<?php foreach ($this->form->getFieldset('extra') as $field) : ?>
				<li><?php if (!$field->hidden) : ?>
					<?php echo $field->label; ?>
				<?php endif; ?>
				<?php echo $field->input; ?></li>
			<?php endforeach; ?>
			</ul>
		</fieldset>

	<?php echo JHtml::_('sliders.end'); ?>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</div>

<div class="clr"></div>
</form>
