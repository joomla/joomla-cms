<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

$canDo	= BannersHelper::getActions();
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

<form action="<?php echo JRoute::_('index.php?option=com_banners&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="client-form" class="form-validate form-horizontal">

	<?php echo JLayoutHelper::render('joomla.edit.item_title', $this); ?>

	<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', empty($this->item->id) ? JText::_('COM_BANNERS_NEW_CLIENT', true) : JText::sprintf('COM_BANNERS_EDIT_CLIENT', $this->item->id, true)); ?>
			<div class="row-fluid">
				<div class="span6">
					<?php if ($canDo->get('core.edit.state')) : ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('state'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('state'); ?>
							</div>
						</div>
					<?php endif; ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('name'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('name'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('contact'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('contact'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('email'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('email'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('purchase_type'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('purchase_type'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('track_impressions'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('track_impressions'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('track_clicks'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('track_clicks'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('id'); ?>
						</div>
					</div>
				</div>
				<div class="span6">
					<?php foreach ($this->form->getFieldset('extra') as $field) : ?>
						<div class="control-group">
							<?php if (!$field->hidden) : ?>
								<div class="control-label">
									<?php echo $field->label; ?>
								</div>
							<?php endif; ?>
							<div class="controls">
								<?php echo $field->input; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'metadata', JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS', true)); ?>
			<?php foreach ($this->form->getFieldset('metadata') as $field) : ?>
				<div class="control-group">
					<?php if (!$field->hidden) : ?>
						<div class="control-label">
							<?php echo $field->label; ?>
						</div>
					<?php endif; ?>
					<div class="controls">
						<?php echo $field->input; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

	<?php echo JHtml::_('bootstrap.endTabSet'); ?>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
