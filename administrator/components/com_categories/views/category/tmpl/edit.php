<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

$input = JFactory::getApplication()->input;

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'category.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('item-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_categories&extension=' . $input->getCmd('extension', 'com_content') . '&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate form-horizontal">
	<fieldset>
		<ul class="nav nav-tabs">
			<li class="active"><a href="#details" data-toggle="tab"><?php echo JText::_('COM_CATEGORIES_FIELDSET_DETAILS');?></a></li>
			<li><a href="#options" data-toggle="tab"><?php echo JText::_('CATEGORIES_FIELDSET_OPTIONS');?></a></li>
			<li><a href="#metadata" data-toggle="tab"><?php echo JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS');?></a></li>
			<?php if ($this->canDo->get('core.admin')): ?>
				<li><a href="#permissions" data-toggle="tab"><?php echo JText::_('COM_CATEGORIES_FIELDSET_RULES');?></a></li>
			<?php endif; ?>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="details">
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('title'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('title'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('alias'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('alias'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('description'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('description'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('extension'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('extension'); ?>
					</div>
				</div>

				<div class="row-fluid">
					<h4><?php echo JText::_('JDETAILS');?></h4>
					<hr />

					<div class="span6">
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('parent_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('parent_id'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('published'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('published'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('access'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('access'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('language'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('language'); ?>
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
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('created_user_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('created_user_id'); ?>
							</div>
						</div>
						<?php if (intval($this->item->created_time)) : ?>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('created_time'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('created_time'); ?>
								</div>
							</div>
						<?php endif; ?>
						<?php if ($this->item->modified_user_id) : ?>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('modified_user_id'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('modified_user_id'); ?>
								</div>
							</div>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('modified_time'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('modified_time'); ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="options">
				<?php echo $this->loadTemplate('options'); ?>
			</div>
			<div class="tab-pane" id="metadata">
				<?php echo $this->loadTemplate('metadata'); ?>
			</div>
			<?php if ($this->canDo->get('core.admin')): ?>
				<div class="tab-pane" id="permissions">
					<?php echo $this->form->getInput('rules'); ?>
				</div>
			<?php endif; ?>
		</div>
	</fieldset>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
