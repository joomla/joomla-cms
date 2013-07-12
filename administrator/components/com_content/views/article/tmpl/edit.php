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
$params = $params->toArray();

// This checks if the config options have ever been saved. If they haven't they will fall back to the original settings.
$editoroptions = isset($params['show_publishing_options']);

$app = JFactory::getApplication();
$input = $app->input;

$assoc = isset($app->item_associations) ? $app->item_associations : 0;

if (!$editoroptions)
{
	$params['show_publishing_options'] = '1';
	$params['show_article_options'] = '1';
	$params['show_urls_images_backend'] = '0';
	$params['show_urls_images_frontend'] = '0';
}

// Check if the article uses configuration settings besides global. If so, use them.
if (!empty($this->item->attribs['show_publishing_options']))
{
	$params['show_publishing_options'] = $this->item->attribs['show_publishing_options'];
}

if (!empty($this->item->attribs['show_article_options']))
{
	$params['show_article_options'] = $this->item->attribs['show_article_options'];
}

if (!empty($this->item->attribs['show_urls_images_backend']))
{
	$params['show_urls_images_backend'] = $this->item->attribs['show_urls_images_backend'];
}

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

<form action="<?php echo JRoute::_('index.php?option=com_content&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.item_title', $this); ?>

	<div class="row-fluid">
		<!-- Begin Content -->
		<div class="span10 form-horizontal">
			<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_CONTENT_ARTICLE_DETAILS', true)); ?>
					<fieldset class="adminform">
						<div class="control-group form-inline">
							<?php echo $this->form->getLabel('title'); ?> <?php echo $this->form->getInput('title'); ?> <?php echo $this->form->getLabel('catid'); ?> <?php echo $this->form->getInput('catid'); ?>
						</div>
						<?php echo $this->form->getInput('articletext'); ?>
					</fieldset>
					<?php
						// The url and images fields only show if the configuration is set to allow them. This is for legacy reasons.
					?>
					<?php if ($params['show_urls_images_backend']) : ?>
						<div class="row-fluid">
							<div class="span6">
								<h4><?php echo JText::_('COM_CONTENT_FIELDSET_URLS_AND_IMAGES');?></h4>
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
					<?php endif; ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>

				<?php // Do not show the publishing options if the edit form is configured not to. ?>
					<?php  if ($params['show_publishing_options'] || ( $params['show_publishing_options'] = '' && !empty($editoroptions)) ) : ?>
						<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_CONTENT_FIELDSET_PUBLISHING', true)); ?>
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
									<div class="control-group">
										<?php echo $this->form->getLabel('created'); ?>
										<div class="controls">
											<?php echo $this->form->getInput('created'); ?>
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
									<?php if ($this->item->modified_by) : ?>
										<div class="control-group">
											<?php echo $this->form->getLabel('modified_by'); ?>
											<div class="controls">
												<?php echo $this->form->getInput('modified_by'); ?>
											</div>
										</div>
										<div class="control-group">
											<?php echo $this->form->getLabel('modified'); ?>
											<div class="controls">
												<?php echo $this->form->getInput('modified'); ?>
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
					<?php  endif; ?>

					<?php if ($params['show_article_options'] || (( $params['show_article_options'] == '' && !empty($editoroptions) ))) : ?>
						<?php $fieldSets = $this->form->getFieldsets('attribs'); ?>
						<?php foreach ($fieldSets as $name => $fieldSet) : ?>

							<?php if ($name != 'editorConfig' && $name != 'basic-limited') : ?>
								<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'attrib-' . $name, JText::_($fieldSet->label, true)); ?>
							<?php endif; ?>

							<?php // If the parameter says to show the article options or if the parameters have never been set, we will show the article options.?>
							<?php if ($params['show_article_options'] || (( $params['show_article_options'] == '' && !empty($editoroptions) ))) : ?>
								<?php // Go through all the fieldsets except the configuration and basic-limited, which are handled separately below.?>
								<?php if ($name != 'editorConfig' && $name != 'basic-limited') : ?>
									<?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
										<p class="tip"><?php echo $this->escape(JText::_($fieldSet->description));?></p>
									<?php endif; ?>
									<?php foreach ($this->form->getFieldset($name) as $field) : ?>
										<div class="control-group">
											<?php echo $field->label; ?>
											<div class="controls">
												<?php echo $field->input; ?>
											</div>
										</div>
									<?php endforeach; ?>
								<?php endif; ?>
							<?php // If we are not showing the options we need to use the hidden fields so the values are not lost.?>
							<?php elseif ($name == 'basic-limited'):
								foreach ($this->form->getFieldset('basic-limited') as $field) :
									echo $field->input;
								endforeach;
							endif;?>

							<?php if ($name != 'editorConfig' && $name != 'basic-limited') : ?>
								<?php echo JHtml::_('bootstrap.endTab'); ?>
							<?php endif; ?>

						<?php endforeach; ?>
					<?php endif; ?>

					<?php // We need to make a separate space for the configuration
						// so that those fields always show to those wih permissions
					?>
					<?php if ($this->canDo->get('core.admin')):  ?>
						<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'editor', JText::_('COM_CONTENT_SLIDER_EDITOR_CONFIG', true)); ?>
							<?php foreach ($this->form->getFieldset('editorConfig') as $field) : ?>
								<div class="control-group">
									<?php echo $field->label; ?>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php endforeach; ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
					<?php endif ?>

					<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'metadata', JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS', true)); ?>
							<?php echo $this->loadTemplate('metadata'); ?>
					<?php echo JHtml::_('bootstrap.endTab'); ?>

					<?php if ($assoc) : ?>
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

			<?php echo JHtml::_('bootstrap.endTabSet'); ?>

			<input type="hidden" name="task" value="" />
			<input type="hidden" name="return" value="<?php echo $input->getCmd('return');?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
		<!-- End Content -->
		<!-- Begin Sidebar -->
			<?php echo JLayoutHelper::render('joomla.edit.details', $this); ?>
		<!-- End Sidebar -->
	</div>
</form>
