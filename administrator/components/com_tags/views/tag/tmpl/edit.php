<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

$app = JFactory::getApplication();
$input = $app->input;

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

// Create shortcut to parameters.
$params = $this->state->get('params');
$params = $params->toArray();

?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'tag.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('item-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_tags&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate form-horizontal">

	<?php echo JLayoutHelper::render('joomla.edit.item_title', $this); ?>

	<div class="row-fluid">
	<!-- Begin Content -->
		<div class="span10 form-horizontal">
			<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_TAGS_FIELDSET_DETAILS', true)); ?>
					<fieldset class="adminform">
						<div class="control-group form-inline">
							<?php echo $this->form->getLabel('title'); ?> <?php echo $this->form->getInput('title'); ?> <?php echo $this->form->getLabel('catid'); ?> <?php echo $this->form->getInput('catid'); ?>
						</div>
						<?php echo $this->form->getInput('description'); ?>
					</fieldset>
						<div class="row-fluid">
							<div class="span6">
								<h4><?php echo JText::_('COM_TAGS_FIELDSET_URLS_AND_IMAGES');?></h4>
								<div class="control-group">
									<?php echo $this->form->getLabel('images'); ?>
									<div class="controls">
										<?php echo $this->form->getInput('images'); ?>
									</div>
								</div>
								<?php foreach ($this->form->getGroup('images') as $field) : ?>
									<div class="control-group">
										<?php if (!$field->hidden) : ?>
											<?php echo $field->label; ?>
										<?php endif; ?>
										<div class="controls">
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
				<?php echo JHtml::_('bootstrap.endTab'); ?>

						<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_TAGS_FIELDSET_PUBLISHING', true)); ?>
							<div class="row-fluid">
								<div class="span6">
									<div class="control-group">
										<?php echo $this->form->getLabel('alias'); ?>
										<div class="controls">
											<?php echo $this->form->getInput('alias'); ?>
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
									<div class="control-group">
										<?php echo $this->form->getLabel('created_user_id'); ?>
										<div class="controls">
											<?php echo $this->form->getInput('created_user_id'); ?>
										</div>
									</div>
									<div class="control-group">
										<?php echo $this->form->getLabel('created_by_alias'); ?>
										<div class="controls">
											<?php echo $this->form->getInput('created_by_alias'); ?>
										</div>
									</div>
									<div class="control-group">
										<?php echo $this->form->getLabel('created_time'); ?>
										<div class="controls">
											<?php echo $this->form->getInput('created_time'); ?>
										</div>
									</div>
								</div>
								<div class="span6">
									<div class="control-group">
										<?php echo $this->form->getLabel('publish_up'); ?>
										<div class="controls">
											<?php echo $this->form->getInput('publish_up'); ?>
										</div>
									</div>
									<div class="control-group">
										<?php echo $this->form->getLabel('publish_down'); ?>
										<div class="controls">
											<?php echo $this->form->getInput('publish_down'); ?>
										</div>
									</div>
									<?php if ($this->item->modified_user_id) : ?>
										<div class="control-group">
											<?php echo $this->form->getLabel('modified_user_id'); ?>
											<div class="controls">
												<?php echo $this->form->getInput('modified_user_id'); ?>
											</div>
										</div>
										<div class="control-group">
											<?php echo $this->form->getLabel('modified_time'); ?>
											<div class="controls">
												<?php echo $this->form->getInput('modified_time'); ?>
											</div>
										</div>
									<?php endif; ?>

									<?php if ($this->item->version) : ?>
										<div class="control-group">
											<?php echo $this->form->getLabel('version'); ?>
											<div class="controls">
												<?php echo $this->form->getInput('version'); ?>
											</div>
										</div>
									<?php endif; ?>

									<?php if ($this->item->hits) : ?>
										<div class="control-group">
											<div class="control-label">
												<?php echo $this->form->getLabel('hits'); ?>
											</div>
											<div class="controls">
												<?php echo $this->form->getInput('hits'); ?>
											</div>
										</div>
									<?php endif; ?>
								</div>
							</div>
						<?php echo JHtml::_('bootstrap.endTab'); ?>

						<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'options', JText::_('COM_TAGS_BASIC_FIELDSET_LABEL', true)); ?>
							<?php echo $this->loadTemplate('options'); ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>

					<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'metadata', JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS', true)); ?>
							<?php echo $this->loadTemplate('metadata'); ?>
					<?php echo JHtml::_('bootstrap.endTab'); ?>
									</div>
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
			</fieldset>
		</div>
		<!-- End Sidebar -->
	</div>
</form>
