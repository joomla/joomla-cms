<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

$app = JFactory::getApplication();
$input = $app->input;

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'category.cancel' || document.formvalidator.isValid(document.id('item-form')))
		{
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('item-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_categories&extension=' . $input->getCmd('extension', 'com_content') . '&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate form-horizontal">
	<div class="row-fluid">
	<!-- Begin Content -->
		<div class="span10 form-horizontal">
			<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_CATEGORIES_FIELDSET_DETAILS', true)); ?>
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
				<?php echo JHtml::_('bootstrap.endTab'); ?>

				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_CATEGORIES_FIELDSET_PUBLISHING', true)); ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('id'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('hits'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('hits'); ?>
						</div>
					</div>
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
				<?php echo JHtml::_('bootstrap.endTab'); ?>

				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'options', JText::_('CATEGORIES_FIELDSET_OPTIONS', true)); ?>
					<fieldset>
						<?php echo $this->loadTemplate('options'); ?>
					</fieldset>
				<?php echo JHtml::_('bootstrap.endTab'); ?>

				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'metadata', JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS', true)); ?>
					<fieldset>
						<?php echo $this->loadTemplate('metadata'); ?>
					</fieldset>
				<?php echo JHtml::_('bootstrap.endTab'); ?>

				<?php echo $this->loadTemplate('extrafields'); ?>

				<?php if ($this->assoc) : ?>
					<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'associations', JText::_('JGLOBAL_FIELDSET_ASSOCIATIONS', true)); ?>
						<fieldset>
							<?php echo $this->loadTemplate('associations'); ?>
						</fieldset>
					<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php endif; ?>

				<?php if ($this->canDo->get('core.admin')) : ?>
					<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'rules', JText::_('COM_CATEGORIES_FIELDSET_RULES', true)); ?>
						<fieldset>
							<?php echo $this->form->getInput('rules'); ?>
						</fieldset>
					<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php endif; ?>

			<?php echo JHtml::_('bootstrap.endTabSet'); ?>

			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
		<!-- End Content -->
		<!-- Begin Sidebar -->
		<div class="span2">
			<h4><?php echo JText::_('JDETAILS');?></h4>
			<hr />
			<fieldset class="form-vertical">
				<div class="control-group">
					<div class="controls">
						<?php echo $this->form->getValue('title'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('parent_id'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('parent_id'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('published'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('published'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('access'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('access'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('language'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('language'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php foreach ($this->form->getFieldset('jmetadata') as $field) : ?>
						<?php if ($this->typeId && $field->name == 'jform[metadata][tags][]') :?>
						<div class="control-group">
							<div class="control-label"><?php echo $field->label; ?></div>
							<div class="controls"><?php echo $field->input; ?></div>
						</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</fieldset>
		</div>
		<!-- End Sidebar -->
	</div>
</form>
