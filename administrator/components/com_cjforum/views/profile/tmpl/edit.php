<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

// Create shortcut to parameters.
$params = $this->state->get('params');

$app = JFactory::getApplication();
$input = $app->input;
$assoc = JLanguageAssociations::isEnabled();

// This checks if the config options have ever been saved. If they haven't they
// will fall back to the original settings.
$params = json_decode($params);
$document = JFactory::getDocument();

CJFunctions::load_jquery(array('libs'=>array('fontawesome')));
CJFunctions::add_css_to_document($document, JUri::root(true).'/media/com_cjforum/css/cj.forum.min.css', true);
CJFunctions::add_script(JUri::root(true).'/media/com_cjforum/js/jquery.guillotine.js', true);
CJFunctions::add_script(JUri::root(true).'/media/com_cjforum/js/cj.forum.min.js', true);

$api = CjForumApi::getProfileApi();
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'topic.cancel' || document.formvalidator.isValid(document.getElementById('item-form')))
		{
			<?php echo $this->form->getField('about')->save(); ?>
			<?php echo $this->form->getField('signature')->save(); ?>
			Joomla.submitform(task, document.getElementById('item-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_cjforum&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" 
	id="item-form" class="form-validate" enctype="multipart/form-data">
	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_CJFORUM_TOPIC_CONTENT', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<fieldset class="adminform form-vertical">
					<div class="form-inline form-inline-header">
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('handle'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('handle'); ?></div>
						</div>
					</div>				
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('about'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('about'); ?>
						</div>
					</div>
					<div class="control-group margin-top-20">
						<div class="control-label">
							<?php echo $this->form->getLabel('signature'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('signature'); ?>
						</div>
					</div>
				</fieldset>
			</div>
			<div class="span3">
				<div class="control-group center text-center">
					<div style="width: 256px;">
						<div id="avatar-container" class="margin-bottom-5">
							<img id="avatar-image" alt="avatar" src="<?php echo $api->resolveAvatarLocation($this->item->avatar, 256).'?dummy='.time();?>">
						</div>
						 <div id="avatar-controls" class="label label-inverse margin-bottom-10">
						 	<a href="#" id="change_avatar" title="Change Avatar" data-toggle="tooltip" onclick="return false;"><i class="fa fa-folder-open"></i></a>
						 	<a href="#" id="rotate_left" title="Rotate left" data-toggle="tooltip" onclick="return false;"><i class="fa fa-rotate-left"></i></a>
						 	<a href="#" id="zoom_out" title="Zoom out" data-toggle="tooltip" onclick="return false;"><i class="fa fa-search-minus"></i></a>
						 	<a href="#" id="fit" title="Fit image" data-toggle="tooltip" onclick="return false;"><i class="fa fa-arrows-alt"></i></a>
						 	<a href="#" id="zoom_in" title="Zoom in" data-toggle="tooltip" onclick="return false;"><i class="fa fa-search-plus"></i></a>
						 	<a href="#" id="rotate_right" title="Rotate right" data-toggle="tooltip" onclick="return false;"><i class="fa fa-rotate-right"></i></a>
						 </div>
						 <p class="muted text-muted"><small><?php echo JText::_('COM_CJFORUM_AVATAR_SELECTION_HELP');?></small></p>
						 <input type="hidden" name="avatar-coords">
					</div>
				</div>
				
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('banned'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('banned'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('birthday'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('birthday'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('location'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('location'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('gender'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('gender'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('rank'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('rank'); ?>
					</div>
				</div>
				<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'social', JText::_('COM_CJFORUM_FIELDSET_SOCIAL_OPTIONS', true)); ?>
			<?php foreach ($this->form->getFieldset('social') as $field) : ?>
			<div class="control-group">
				<div class="control-label">
					<?php echo $field->label; ?>
				</div>
				<div class="controls">
					<?php echo $field->input; ?>
				</div>
			</div>
			<?php endforeach; ?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php // Do not show the publishing options if the edit form is configured not to. ?>
		<?php if ($params->show_publishing_options == 1) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_CJFORUM_FIELDSET_PUBLISHING', true)); ?>
			<div class="row-fluid form-horizontal-desktop">
			<div class="span6">
				<?php echo JLayoutHelper::render('joomla.edit.metadata', $this); ?>
			</div>
		</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>

		<input type="hidden" name="task" value="" /> <input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>" />
		<div style="position: absolute; top:-1000px;">
			<input type="file" name="avatar_file" id="btn-select-avatar">
		</div>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<div style="display: none;">
	<input type="hidden" id="cjforum_pageid" value="profileform">
</div>