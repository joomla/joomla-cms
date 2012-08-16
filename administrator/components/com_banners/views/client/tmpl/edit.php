<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$canDo	= BannersHelper::getActions();
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'client.cancel' || document.formvalidator.isValid(document.id('client-form'))) {
			Joomla.submitform(task, document.getElementById('client-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_banners&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="client-form" class="form-validate form-horizontal">
	<!-- Begin Content -->
		<ul class="nav nav-tabs">
		  <li class="active"><a href="#general" data-toggle="tab"><?php echo empty($this->item->id) ? JText::_('COM_BANNERS_NEW_CLIENT') : JText::sprintf('COM_BANNERS_EDIT_CLIENT', $this->item->id);?></a></li>
		  <li><a href="#metadata" data-toggle="tab"><?php echo JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS');?></a></li>
		</ul>

		<div class="tab-content">
			<!-- Begin Tabs -->
			<div class="tab-pane active" id="general">
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
						<?php foreach($this->form->getFieldset('extra') as $field): ?>
							<div class="control-group">
								<?php if (!$field->hidden): ?>
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
			</div>
			<div class="tab-pane" id="metadata">
				<?php foreach($this->form->getFieldset('metadata') as $field): ?>
					<div class="control-group">
						<?php if (!$field->hidden): ?>
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
			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	<!-- End Content -->
</form>
