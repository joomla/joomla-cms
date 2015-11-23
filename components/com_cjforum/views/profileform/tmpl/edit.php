<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

JHtml::_('behavior.keepalive');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

// Create shortcut to parameters.
$params = $this->state->get('params');

// This checks if the editor config options have ever been saved. If they
// haven't they will fall back to the original settings.
$editoroptions = isset($params->show_publishing_options);
$layout = $this->params->get('layout', 'default');
$theme = $params->get('theme', 'default');

$api = CjForumApi::getProfileApi();
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'topic.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
		{
			<?php echo $this->form->getField('about')->save(); ?>
			<?php echo $this->form->getField('signature')->save(); ?>
			Joomla.submitform(task);
		}
	}
</script>
<div id="cj-wrapper" class="edit item-page<?php echo $this->pageclass_sfx; ?>">
	
	<?php echo JLayoutHelper::render($layout.'.toolbar', array('params'=>$this->params, 'state'=>$this->state));?>
	
	<?php if ($params->get('show_page_heading', 1)) : ?>
	<h1 class="page-header"><?php echo $this->escape($params->get('page_heading')); ?></h1>
	<?php endif; ?>

	<form action="<?php echo JRoute::_('index.php?option=com_cjforum&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" 
		class="form-validate" enctype="multipart/form-data">
		<fieldset>
			<ul class="nav nav-tabs">
				<li class="active"><a href="#editor" data-toggle="tab"><?php echo JText::_('COM_CJFORUM_TOPIC_CONTENT') ?></a></li>
				<li><a href="#social" data-toggle="tab"><?php echo JText::_('COM_CJFORUM_FIELDSET_SOCIAL_OPTIONS') ?></a></li>
				<?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
					<?php if ($fieldset->name != 'social' && $fieldset->name != 'jmetadata') : ?>
					<li><a href="#<?php echo $this->escape($fieldset->name);?>" data-toggle="tab"><?php echo JText::_($fieldset->label);?></a></li>
					<?php endif;?>
				<?php endforeach;?>
				<li><a href="#metadata" data-toggle="tab"><?php echo JText::_('COM_CJFORUM_METADATA') ?></a></li>
			</ul>

			<div class="tab-content">
				<div class="tab-pane form-vertical active" id="editor">
					<div class="container-fluid no-space-left no-space-right">
						<div class="row-fluid">
							<div class="span4 col-lg-4 col-md-4 col-sm-4 margin-top-20">
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
							<div class="span8 col-lg-8 col-md-8 col-sm-8">
								<div class="control-group margin-top-20">
									<div class="control-label">
										<?php echo $this->form->getLabel('handle'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('handle'); ?>
									</div>
								</div>
								
								<?php if ($this->item->params->get('access-change')) : ?>
								<div class="control-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('banned'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('banned'); ?>
									</div>
								</div>
								<?php endif; ?>
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
								<?php if ($this->item->params->get('access-admin')) : ?>
								<div class="control-group">
									<div class="control-label">
										<?php echo $this->form->getLabel('rank'); ?>
									</div>
									<div class="controls">
										<?php echo $this->form->getInput('rank'); ?>
									</div>
								</div>
								<?php endif;?>
							</div>
						</div>
					</div>
								
					<div class="control-group margin-top-20">
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
				</div>
				<div class="tab-pane form-horizontal pad-top-20" id="social">
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
				</div>
				
				<?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
					<?php if ($fieldset->name != 'social' && $fieldset->name != 'jmetadata') : ?>
					<div class="tab-pane form-horizontal pad-top-20" id="<?php echo $this->escape($fieldset->name);?>">
						<?php $fields = $this->form->getFieldset($fieldset->name); ?>
						<?php foreach ($fields as $field) : ?>
							<div class="control-group">
								<?php if ($field->hidden) : ?>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								<?php else: ?>
									<div class="control-label">
										<?php echo $field->label; ?>
										<?php if (!$field->required && $field->type != "Spacer") : ?>
											<span class="optional"><?php echo JText::_('COM_CJFORUM_OPTIONAL'); ?></span>
										<?php endif; ?>
									</div>
									<div class="controls"><?php echo $field->input; ?></div>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
				<?php endforeach; ?>
				
				<div class="tab-pane form-vertical pad-top-20" id="metadata">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('metadesc'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('metadesc'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('metakey'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('metakey'); ?>
						</div>
					</div>

					<input type="hidden" name="task" value="" />
					<input type="hidden" name="p_id" value="<?php echo $this->item->id;?>" /> 
					<input type="hidden" name="return" value="<?php echo $this->return_page; ?>" />
				</div>
			</div>
			<?php echo JHtml::_('form.token'); ?>
		</fieldset>
		
		<div class="panel panel-<?php echo $this->params->get('theme', 'default');?> center">
			<div class="panel-body">
				<div class="btn-group">
					<button type="button" class="btn" onclick="Joomla.submitbutton('profile.cancel')">
						<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL')?>
					</button>
				</div>
				<div class="btn-group">
					<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('profile.save')">
						<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE')?>
					</button>
				</div>
			</div>
		</div>
		<div style="position: absolute; top:-1000px;">
			<input type="file" name="avatar_file" id="btn-select-avatar">
		</div>
	</form>
	
	<?php echo JLayoutHelper::render($layout.'.credits', array('params'=>$this->params));?>
</div>
<div style="display: none;">
	<input type="hidden" id="cjforum_pageid" value="profileform">
</div>