<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_banners
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
<!--
	function submitbutton(task)
	{
		if (task == 'client.cancel' || document.formvalidator.isValid(document.id('client-form'))) {
			submitform(task);
		}
		// @todo Deal with the editor methods
		submitform(task);
	}
// -->
</script>

<form action="<?php JRoute::_('index.php?option=com_banners'); ?>" method="post" name="adminForm" id="client-form" class="form-validate">

<div class="width-50 fltlft">
	<fieldset class="adminform">
		<legend><?php echo empty($this->item->id) ? JText::_('COM_BANNERS_NEW_CLIENT') : JText::sprintf('COM_BANNERS_EDIT_CLIENT', $this->item->id); ?></legend>
				<?php echo $this->form->getLabel('name'); ?>
				<?php echo $this->form->getInput('name'); ?>

				<?php echo $this->form->getLabel('contact'); ?>
				<?php echo $this->form->getInput('contact'); ?>

				<?php echo $this->form->getLabel('email'); ?>
				<?php echo $this->form->getInput('email'); ?>

				<?php echo $this->form->getLabel('state'); ?>
				<?php echo $this->form->getInput('state'); ?>

				<?php echo $this->form->getLabel('purchase_type'); ?>
				<?php echo $this->form->getInput('purchase_type'); ?>

				<?php echo $this->form->getLabel('track_impressions'); ?>
				<?php echo $this->form->getInput('track_impressions'); ?>

				<?php echo $this->form->getLabel('track_clicks'); ?>
				<?php echo $this->form->getInput('track_clicks'); ?>

				<?php echo $this->form->getLabel('id'); ?>
				<?php echo $this->form->getInput('id'); ?>


	</fieldset>
</div>

<div class="width-50 fltrt">
	<?php echo JHtml::_('sliders.start','banner-client-sliders-'.$this->item->id, array('useCookie'=>1)); ?>

	<?php echo JHtml::_('sliders.panel',JText::_('COM_BANNERS_GROUP_LABEL_METADATA_OPTIONS'), 'publishing-details'); ?>
		<fieldset class="adminform">
			<?php foreach($this->form->getFieldset('metadata') as $field): ?>
				<?php if (!$field->hidden): ?>
					<?php echo $field->label; ?>
				<?php endif; ?>
				<?php echo $field->input; ?>
			<?php endforeach; ?>
		</fieldset>

	<?php echo JHtml::_('sliders.panel',JText::_('COM_BANNERS_EXTRA'), 'extra'); ?>
		<fieldset class="adminform">
			<?php foreach($this->form->getFieldset('extra') as $field): ?>
				<?php if (!$field->hidden): ?>
					<?php echo $field->label; ?>
				<?php endif; ?>
				<?php echo $field->input; ?>
			<?php endforeach; ?>
		</fieldset>

	<?php echo JHtml::_('sliders.end'); ?>
</div>

<div class="clr"></div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>