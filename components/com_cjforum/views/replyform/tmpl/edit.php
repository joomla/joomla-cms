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
$topic_uri = CjForumHelperRoute::getTopicRoute($this->item->topic_id, $this->item->catid);

// This checks if the editor config options have ever been saved. If they
// haven't they will fall back to the original settings.
$editoroptions = isset($params->show_publishing_options);
$theme = $params->get('theme', 'default');
$layout = $this->params->get('layout', 'default');
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'reply.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
		{
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task);
		}
	}
</script>
<div id="cj-wrapper" class="edit item-page<?php echo $this->pageclass_sfx; ?>">
	
	<?php echo JLayoutHelper::render($layout.'.toolbar', array('params'=>$this->params, 'state'=>$this->state));?>
	
	<?php if ($params->get('show_page_heading', 1)) : ?>
	<div class="page-header">
		<h1>
			<?php echo $this->escape($params->get('page_heading')); ?>
		</h1>
	</div>
	<?php endif; ?>

	<form action="<?php echo JRoute::_('index.php?option=com_cjforum&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" 
		class="form-validate form-vertical" enctype="multipart/form-data">
		<fieldset>
			<ul class="nav nav-tabs">
				<li class="active"><a href="#editor" data-toggle="tab"><?php echo JText::_('COM_CJFORUM_POST_REPLY') ?></a></li>
				<?php if($params->get('show_publishing_options', 1) == 1):?>
				<li><a href="#publishing" data-toggle="tab"><?php echo JText::_('COM_CJFORUM_PUBLISHING') ?></a></li>
				<?php endif;?>
			</ul>

			<div class="tab-content">
				<div class="tab-pane active" id="editor">
					<?php echo $this->form->getInput('description'); ?>
					<div class="panel panel-<?php echo $theme?> attachments">
						<div class="panel-heading"><?php echo JText::_('COM_CJFORUM_ATTACHMENTS');?></div>
						<div class="panel-body">
							<div class="attachment">
								<button type="button" class="btn btn-default btn-attach-file"><i class="fa fa-file"></i> <?php echo JText::_('COM_CJFORUM_CHOOSE_FILE');?></button>
								<span class="filename"></span>
								<input type="file" name="attachment_file[]" value="" style="display: none;">
							</div>
						</div>
					</div>
				</div>
				<?php if($params->get('show_publishing_options', 1) == 1):?>
				<div class="tab-pane" id="publishing">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('created_by_alias'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('created_by_alias'); ?>
						</div>
					</div>
					<?php if ($this->item->params->get('access-edit-state')) : ?>
						<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('state'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('state'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('featured'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('featured'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('publish_up'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('publish_up'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('publish_down'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('publish_down'); ?>
						</div>
					</div>
					<?php endif; ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('access'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('access'); ?>
						</div>
					</div>
					<?php if (is_null($this->item->id)):?>
					<div class="control-group">
						<div class="control-label"></div>
						<div class="controls">
							<?php echo JText::_('COM_CJFORUM_ORDERING'); ?>
						</div>
					</div>
					<?php endif; ?>
				</div>
				<?php endif;?>

				<input type="hidden" name="task" value="" /> 
				<input type="hidden" name="r_id" value="<?php echo $this->item->id;?>">
				<input type="hidden" name="cid" value="<?php echo $this->item->id;?>">
				<input type="hidden" name="jform[topic_id]" value="<?php echo $this->item->topic_id; ?>" />
				<input type="hidden" name="return" value="<?php echo base64_encode(JRoute::_($topic_uri));?>">
			</div>
			<?php echo JHtml::_('form.token'); ?>
		</fieldset>
		
		<div class="panel panel-<?php echo $this->params->get('theme', 'default');?> center">
			<div class="panel-body">
				<div class="btn-group">
					<button type="button" class="btn" onclick="Joomla.submitbutton('reply.cancel')">
						<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL')?>
					</button>
				</div>
				<div class="btn-group">
					<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('reply.save')">
						<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE')?>
					</button>
				</div>
			</div>
		</div>
	</form>
	
	<?php echo JLayoutHelper::render($layout.'.credits', array('params'=>$this->params));?>
	
	<div style="display: none;">
		<input type="hidden" id="cjforum_pageid" value="form">
		
		<div id="tpl-attachment">
			<div class="attachment">
				<button type="button" class="btn btn-attach-file"><i class="fa fa-file"></i> <?php echo JText::_('COM_CJFORUM_CHOOSE_FILE');?></button>
				<span class="filename"></span>
				<input type="file" name="attachment_file[]" value="" style="display: none;">
			</div>
		</div>
	</div>
</div>
