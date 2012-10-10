<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

// Create shortcut to parameters.
$params = $this->state->get('params');
$params = $params->toArray();

// This checks if the config options have ever been saved. If they haven't they will fall back to the original settings.
$editoroptions = isset($params['show_publishing_options']);

$input = JFactory::getApplication()->input;

if (!$editoroptions):
	$params['show_publishing_options'] = '1';
	$params['show_article_options'] = '1';
	$params['show_urls_images_backend'] = '0';
	$params['show_urls_images_frontend'] = '0';
endif;

// Check if the article uses configuration settings besides global. If so, use them.
if (!empty($this->item->attribs['show_publishing_options'])):
		$params['show_publishing_options'] = $this->item->attribs['show_publishing_options'];
endif;
if (!empty($this->item->attribs['show_article_options'])):
		$params['show_article_options'] = $this->item->attribs['show_article_options'];
endif;
if (!empty($this->item->attribs['show_urls_images_backend'])):
		$params['show_urls_images_backend'] = $this->item->attribs['show_urls_images_backend'];
endif;

?>

<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'article.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			<?php echo $this->form->getField('articletext')->save(); ?>
			Joomla.submitform(task, document.getElementById('item-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_content&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<div class="row-fluid">
		<!-- Begin Content -->
		<div class="span10 form-horizontal">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('COM_CONTENT_ARTICLE_DETAILS');?></a></li>
				<li><a href="#publishing" data-toggle="tab"><?php echo JText::_('COM_CONTENT_FIELDSET_PUBLISHING');?></a></li>
				<?php $fieldSets = $this->form->getFieldsets('attribs'); ?>
					<?php foreach ($fieldSets as $name => $fieldSet) : ?>
					<?php if ($params['show_article_options'] || (( $params['show_article_options'] == '' && !empty($editoroptions) ))): ?>
						<?php if ($name != 'editorConfig' && $name != 'basic-limited') : ?>
							<li><a href="#attrib-<?php echo $name;?>" data-toggle="tab"><?php echo JText::_($fieldSet->label);?></a></li>
						<?php endif ?>
					<?php endif; ?>
				<?php endforeach; ?>
				<?php if ( $this->canDo->get('core.admin')   ):  ?>
					<li><a href="#editor" data-toggle="tab"><?php echo JText::_('COM_CONTENT_SLIDER_EDITOR_CONFIG');?></a></li>
				<?php endif ?>
				<li><a href="#metadata" data-toggle="tab"><?php echo JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS');?></a></li>
				<?php if ($this->canDo->get('core.admin')): ?>
					<li><a href="#permissions" data-toggle="tab"><?php echo JText::_('COM_CONTENT_FIELDSET_RULES');?></a></li>
				<?php endif ?>
			</ul>

			<div class="tab-content">
				<!-- Begin Tabs -->
				<div class="tab-pane active" id="general">
					<fieldset class="adminform">
						<div class="control-group form-inline">
							<?php echo $this->form->getLabel('title'); ?> <?php echo $this->form->getInput('title'); ?> <?php echo $this->form->getLabel('catid'); ?> <?php echo $this->form->getInput('catid'); ?>
						</div>
						<?php echo $this->form->getInput('articletext'); ?>
					</fieldset>
					<?php
						// The url and images fields only show if the configuration is set to allow them. This is for legacy reasons.
					?>
					<?php if ($params['show_urls_images_backend']): ?>
						<div class="row-fluid">
							<div class="span6">
								<h4><?php echo JText::_('COM_CONTENT_FIELDSET_URLS_AND_IMAGES');?></h4>
								<div class="control-group">
									<?php echo $this->form->getLabel('images'); ?>
									<div class="controls">
										<?php echo $this->form->getInput('images'); ?>
									</div>
								</div>
								<?php foreach($this->form->getGroup('images') as $field): ?>
									<div class="control-group">
										<?php if (!$field->hidden): ?>
											<?php echo $field->label; ?>
										<?php endif; ?>
										<div class="controls">
											<?php echo $field->input; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
							<div class="span6">
								<?php foreach($this->form->getGroup('urls') as $field): ?>
									<div class="control-group">
										<?php if (!$field->hidden): ?>
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
				</div>

				<?php // Do not show the publishing options if the edit form is configured not to. ?>
					<?php  if ($params['show_publishing_options'] || ( $params['show_publishing_options'] = '' && !empty($editoroptions)) ): ?>
						<div class="tab-pane" id="publishing">
							<div class="row-fluid">
								<div class="span6">
									<div class="control-group">
										<?php echo $this->form->getLabel('alias'); ?>
										<div class="controls">
											<?php echo $this->form->getInput('alias'); ?>
										</div>
									</div>
									<div class="control-group">
										<?php echo $this->form->getLabel('id'); ?>
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
											<?php echo $this->form->getLabel('hits'); ?>
											<div class="controls">
												<?php echo $this->form->getInput('hits'); ?>
											</div>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php  endif; ?>
					<?php  $fieldSets = $this->form->getFieldsets('attribs'); ?>
						<?php foreach ($fieldSets as $name => $fieldSet) : ?>
							<div class="tab-pane" id="attrib-<?php echo $name;?>">
							<?php
								// If the parameter says to show the article options or if the parameters have never been set, we will
								// show the article options.

								if ($params['show_article_options'] || (( $params['show_article_options'] == '' && !empty($editoroptions) ))):
									// Go through all the fieldsets except the configuration and basic-limited, which are
									// handled separately below.

									if ($name != 'editorConfig' && $name != 'basic-limited') : ?>
										<?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
											<p class="tip"><?php echo $this->escape(JText::_($fieldSet->description));?></p>
										<?php endif;
										foreach ($this->form->getFieldset($name) as $field) : ?>
											<div class="control-group">
												<?php echo $field->label; ?>
												<div class="controls">
													<?php echo $field->input; ?>
												</div>
											</div>
										<?php endforeach;
									endif;
								// If we are not showing the options we need to use the hidden fields so the values are not lost.
								elseif ($name == 'basic-limited'):
									foreach ($this->form->getFieldset('basic-limited') as $field) :
										echo $field->input;
									endforeach;
								endif;
							?>
							</div>
						<?php endforeach;
						// We need to make a separate space for the configuration
						// so that those fields always show to those wih permissions

						if ($this->canDo->get('core.admin')):  ?>
						<div class="tab-pane" id="editor">
							<?php foreach ($this->form->getFieldset('editorConfig') as $field) : ?>
								<div class="control-group">
									<?php echo $field->label; ?>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif ?>

					<div class="tab-pane" id="metadata">
						<fieldset>
							<?php echo $this->loadTemplate('metadata'); ?>
						</fieldset>
					</div>

					<?php if ($this->canDo->get('core.admin')): ?>
						<div class="tab-pane" id="permissions">
							<fieldset>
								<?php echo $this->form->getInput('rules'); ?>
							</fieldset>
						</div>
					<?php endif; ?>
				<!-- End Tabs -->
			</div>
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="return" value="<?php echo $input->getCmd('return');?>" />
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
					<?php echo $this->form->getLabel('state'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('state'); ?>
					</div>
				</div>

				<div class="control-group">
					<?php echo $this->form->getLabel('access'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('access'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php echo $this->form->getLabel('featured'); ?>
					<div class="controls">
						<?php echo $this->form->getInput('featured'); ?>
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
