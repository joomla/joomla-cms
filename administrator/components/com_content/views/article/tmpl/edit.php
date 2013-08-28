<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

// Create shortcut to parameters.
$params = $this->state->get('params');

$app = JFactory::getApplication();
$input = $app->input;

?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'article.cancel' || document.formvalidator.isValid(document.id('item-form')))
		{
			<?php echo $this->form->getField('articletext')->save(); ?>
			Joomla.submitform(task, document.getElementById('item-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_content&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_CONTENT_ARTICLE_CONTENT', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<fieldset class="adminform">
					<?php echo $this->form->getInput('articletext'); ?>
				</fieldset>
			</div>
			<div class="span3">
				<?php echo JLayoutHelper::render('joomla.edit.main', $this); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php // Do not show the publishing options if the edit form is configured not to. ?>
		<?php if ($params->get('show_publishing_options')) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_CONTENT_FIELDSET_PUBLISHING', true)); ?>
			<div class="row-fluid">
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
					<div class="control-group">
						<?php echo $this->form->getLabel('created'); ?>
						<div class="controls">
							<?php echo $this->form->getInput('created'); ?>
						</div>
					</div>
					<div class="control-group">
						<?php echo $this->form->getLabel('created_by'); ?>
						<div class="controls">
							<?php echo $this->form->getInput('created_by'); ?>
						</div>
					</div>
					<div class="control-group">
						<?php echo $this->form->getLabel('created_by_alias'); ?>
						<div class="controls">
							<?php echo $this->form->getInput('created_by_alias'); ?>
						</div>
					</div>
					<?php if ($this->item->modified_by) : ?>
						<div class="control-group">
							<?php echo $this->form->getLabel('modified'); ?>
							<div class="controls">
								<?php echo $this->form->getInput('modified'); ?>
							</div>
						</div>
						<div class="control-group">
							<?php echo $this->form->getLabel('modified_by'); ?>
							<div class="controls">
								<?php echo $this->form->getInput('modified_by'); ?>
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
					<?php echo $this->loadTemplate('metadata'); ?>
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

		<?php // Do not show the images and links options if the edit form is configured not to. ?>
		<?php if ($params->get('show_urls_images_backend')) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'images', JText::_('COM_CONTENT_FIELDSET_URLS_AND_IMAGES', true)); ?>
			<div class="row-fluid">
				<div class="span6">
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
				<div class="span6">
					<?php foreach ($this->form->getGroup('urls') as $field) : ?>
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
		<?php endif; ?>

		<?php if (isset($app->item_associations)) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'associations', JText::_('JGLOBAL_FIELDSET_ASSOCIATIONS', true)); ?>
			<?php echo $this->loadTemplate('associations'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

		<?php if ($this->canDo->get('core.admin')) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('COM_CONTENT_FIELDSET_RULES', true)); ?>
			<fieldset>
				<?php echo $this->form->getInput('rules'); ?>
			</fieldset>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

		<?php $fieldSets = $this->form->getFieldsets('attribs'); ?>
		<?php if ($params->get('show_article_options')) : ?>
			<?php foreach ($fieldSets as $name => $fieldSet) : ?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'attrib-' . $name, JText::_($fieldSet->label, true)); ?>

				<?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
					<p class="tip"><?php echo $this->escape(JText::_($fieldSet->description)); ?></p>
				<?php endif; ?>
				<?php
				$split = count($this->form->getFieldset($name)) > 10 ? ceil(count($this->form->getFieldset($name)) / 2) : 0;
				$count = 0;
				?>

				<div class="row-fluid">
					<div class="span<?php echo $split ? 6 : 12; ?>">
						<?php foreach ($this->form->getFieldset($name) as $field) : ?>
							<div class="control-group">
								<?php echo $field->label; ?>
								<div class="controls">
									<?php echo $field->input; ?>
								</div>
							</div>
							<?php echo (++$count == $split) ? '</div><div class="span6">' : ''; ?>
						<?php endforeach; ?>
					</div>
				</div>

				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endforeach; ?>
		<?php else: ?>
			<div styl="display:hidden;">
				<?php foreach ($fieldSets as $name => $fieldSet) : ?>
					<?php foreach ($this->form->getFieldset($name) as $field) : ?>
						<?php echo $field->input; ?>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
