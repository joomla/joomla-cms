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
JHtml::_('behavior.modal', 'a.modal_jform_contenthistory');

// Create shortcut to parameters.
$params = $this->state->get('params');
// $images = json_decode($this->item->images);
// $urls = json_decode($this->item->urls);

// This checks if the editor config options have ever been saved. If they
// haven't they will fall back to the original settings.
$editoroptions = isset($params->show_publishing_options);
if (! $editoroptions)
{
	$params->show_urls_images_frontend = '0';
}

$layout = $params->get('layout', 'default');
$theme = $params->get('theme', 'default');
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'topic.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
		{
			<?php echo $this->form->getField('topictext')->save(); ?>
			Joomla.submitform(task);
		}
	}
</script>
<div id="cj-wrapper" class="edit item-page<?php echo $this->pageclass_sfx; ?>">
	
	<?php echo JLayoutHelper::render($layout.'.toolbar', array('params'=>$params, 'state'=>$this->state));?>
	
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
				<li class="active"><a href="#editor" data-toggle="tab"><?php echo JText::_('COM_CJFORUM_TOPIC_CONTENT') ?></a></li>
				
				<?php if ($params->get('show_urls_images_frontend') ) : ?>
				<li><a href="#images" data-toggle="tab"><?php echo JText::_('COM_CJFORUM_IMAGES_AND_URLS') ?></a></li>
				<?php endif; ?>
				
				<?php if($params->get('show_publishing_options', 1) == 1):?>
				<li><a href="#publishing" data-toggle="tab"><?php echo JText::_('COM_CJFORUM_PUBLISHING') ?></a></li>
				<?php endif;?>
				
				<li><a href="#language" data-toggle="tab"><?php echo JText::_('JFIELD_LANGUAGE_LABEL') ?></a></li>
				<li><a href="#metadata" data-toggle="tab"><?php echo JText::_('COM_CJFORUM_METADATA') ?></a></li>
			</ul>

			<div class="tab-content">
				<div class="tab-pane active" id="editor">
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('title'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('title'); ?>
						</div>
					</div>

					<?php if (is_null($this->item->id)) : ?>
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('alias'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('alias'); ?>
						</div>
					</div>
					<?php endif; ?>
					
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('catid'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('catid'); ?>
						</div>
					</div>
					
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('tags'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('tags'); ?>
						</div>
					</div>

					<div class="clearfix">
						<?php echo $this->form->getInput('topictext'); ?>
					</div>
					
					<div class="panel panel-<?php echo $theme?> attachments margin-top-10">
						<div class="panel-heading"><?php echo JText::_('COM_CJFORUM_ATTACHMENTS');?></div>
						<div class="panel-body">
							<?php 
							if(!empty($this->item->attachments))
							{
								foreach ($this->item->attachments as $attachment)
								{
									?>
									<div class="attachment">
										<a href="#" class="btn-delete-attachment" onclick="return false;">
											<i class="fa fa-trash-o"></i> <?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_DELETE');?>
										</a>
										<span class="filename"><?php echo $this->escape($attachment->filename);?></span>
										<input type="hidden" name="existing_attachment[]" value="<?php echo $attachment->id;?>" style="display: none;">
									</div>
									<?php 
								}
							}
							?>
							<div class="attachment">
								<a href="#" class="btn btn-danger btn-delete-attachment" onclick="return false;" style="display: none;">
									<i class="fa fa-trash-o"></i> <?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_DELETE');?>
								</a>
								<button type="button" class="btn btn-default btn-attach-file"><i class="fa fa-file"></i> <?php echo JText::_('COM_CJFORUM_CHOOSE_FILE');?></button>
								<span class="filename"></span>
								<input type="file" name="attachment_file[]" value="" style="display: none;">
							</div>
						</div>
					</div>
				</div>
				<?php if ($params->get('show_urls_images_frontend')): ?>
				<div class="tab-pane" id="images">
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('image_intro', 'images'); ?>
							<?php echo $this->form->getInput('image_intro', 'images'); ?>
						</div>
					</div>
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('image_intro_alt', 'images'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('image_intro_alt', 'images'); ?>
						</div>
					</div>
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('image_intro_caption', 'images'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('image_intro_caption', 'images'); ?>
						</div>
					</div>
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('float_intro', 'images'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('float_intro', 'images'); ?>
						</div>
					</div>
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('image_fulltext', 'images'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('image_fulltext', 'images'); ?>
						</div>
					</div>
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('image_fulltext_alt', 'images'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('image_fulltext_alt', 'images'); ?>
						</div>
					</div>
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('image_fulltext_caption', 'images'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('image_fulltext_caption', 'images'); ?>
						</div>
					</div>
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('float_fulltext', 'images'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('float_fulltext', 'images'); ?>
						</div>
					</div>
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('urla', 'urls'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('urla', 'urls'); ?>
						</div>
					</div>
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('urlatext', 'urls'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('urlatext', 'urls'); ?>
						</div>
					</div>
					<div class="control-group form-group">
						<div class="controls">
							<?php echo $this->form->getInput('targeta', 'urls'); ?>
						</div>
					</div>
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('urlb', 'urls'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('urlb', 'urls'); ?>
						</div>
					</div>
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('urlbtext', 'urls'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('urlbtext', 'urls'); ?>
						</div>
					</div>
					<div class="control-group form-group">
						<div class="controls">
							<?php echo $this->form->getInput('targetb', 'urls'); ?>
						</div>
					</div>
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('urlc', 'urls'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('urlc', 'urls'); ?>
						</div>
					</div>
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('urlctext', 'urls'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('urlctext', 'urls'); ?>
						</div>
					</div>
					<div class="control-group form-group">
						<div class="controls">
							<?php echo $this->form->getInput('targetc', 'urls'); ?>
						</div>
					</div>
				</div>
				<?php endif; ?>
				
				<?php if($params->get('show_publishing_options', 1) == 1):?>
				<div class="tab-pane" id="publishing">
					<?php if ($params->get('save_history', 0)) : ?>
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('version_note'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('version_note'); ?>
						</div>
					</div>
					<?php endif; ?>
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('created_by_alias'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('created_by_alias'); ?>
						</div>
					</div>
					<?php if ($this->item->params->get('access-change')) : ?>
						<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('state'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('state'); ?>
						</div>
					</div>
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('featured'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('featured'); ?>
						</div>
					</div>
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('publish_up'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('publish_up'); ?>
						</div>
					</div>
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('publish_down'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('publish_down'); ?>
						</div>
					</div>
					<?php endif; ?>
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('access'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('access'); ?>
						</div>
					</div>
					<?php if (is_null($this->item->id)):?>
					<div class="control-group form-group">
						<div class="control-label"></div>
						<div class="controls">
							<?php echo JText::_('COM_CJFORUM_ORDERING'); ?>
						</div>
					</div>
					<?php endif; ?>
				</div>
				<?php endif;?>
				
				<div class="tab-pane" id="language">
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('language'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('language'); ?>
						</div>
					</div>
				</div>
				<div class="tab-pane" id="metadata">
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('metadesc'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('metadesc'); ?>
						</div>
					</div>
					<div class="control-group form-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('metakey'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('metakey'); ?>
						</div>
					</div>

					<input type="hidden" name="task" value="" />
					<input type="hidden" name="t_id" value="<?php echo $this->item->id;?>" /> 
					<input type="hidden" name="return" value="<?php echo $this->return_page; ?>" />
					
					<?php if ($params->get('enable_category', 0) == 1) :?>
					<input type="hidden" name="jform[catid]" value="<?php echo $params->get('catid', 1); ?>" />
					<?php endif; ?>
				</div>
			</div>
			<?php echo JHtml::_('form.token'); ?>
		</fieldset>
		
		<div class="panel panel-<?php echo $params->get('theme', 'default');?> center">
			<div class="panel-body">
				<div class="subscribe margin-bottom-20">
					<label class="inline"><input type="checkbox" name="subscribe" value="1"> <?php echo JText::_('COM_CJFORUM_SEND_NOTIFICATIONS');?></label>
				</div>
				<div class="btn-group">
					<button type="button" class="btn" onclick="Joomla.submitbutton('topic.cancel')">
						<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL')?>
					</button>
				</div>
				<div class="btn-group">
					<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('topic.save')">
						<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE')?>
					</button>
				</div>
				<?php if ($params->get('save_history', 0)) : ?>
				<div class="btn-group">
					<?php echo $this->form->getInput('contenthistory'); ?>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</form>
	
	<?php echo JLayoutHelper::render($layout.'.credits', array('params'=>$params));?>
	
	<div style="display: none;">
		<input type="hidden" id="cjforum_pageid" value="form">
		
		<div id="tpl-attachment">
			<div class="attachment">
				<a href="#" class="btn btn-danger btn-delete-attachment" onclick="return false;" style="display: none;">
					<i class="fa fa-trash-o"></i> <?php echo JText::_('COM_CJFORUM_TOPIC_ACTION_DELETE');?>
				</a>
				<button type="button" class="btn btn-attach-file"><i class="fa fa-file"></i> <?php echo JText::_('COM_CJFORUM_CHOOSE_FILE');?></button>
				<span class="filename"></span>
				<input type="file" name="attachment_file[]" value="" style="display: none;">
			</div>
		</div>
	</div>
</div>
