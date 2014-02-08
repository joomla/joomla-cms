<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.modal', 'a.modal_jform_contenthistory');

// Create shortcut to parameters.
$params = $this->state->get('params');
//$images = json_decode($this->item->images);
//$urls = json_decode($this->item->urls);

// This checks if the editor config options have ever been saved. If they haven't they will fall back to the original settings.
$editoroptions = isset($params->show_publishing_options);
if (!$editoroptions)
{
	$params->show_urls_images_frontend = '0';
}
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'article.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
		{
			<?php echo $this->form->getField('articletext')->save(); ?>
			Joomla.submitform(task);
		}
	}
</script>
<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">
	<?php if ($params->get('show_page_heading', 1)) : ?>
	<div class="page-header">
		<h1>
			<?php echo $this->escape($params->get('page_heading')); ?>
		</h1>
	</div>
	<?php endif; ?>

	<form action="<?php echo JRoute::_('index.php?option=com_content&a_id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-vertical">
		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('article.save')">
					<span class="icon-ok"></span>&#160;<?php echo JText::_('JSAVE') ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn" onclick="Joomla.submitbutton('article.cancel')">
					<span class="icon-cancel"></span>&#160;<?php echo JText::_('JCANCEL') ?>
				</button>
			</div>
			<?php if ($params->get('save_history', 0)) : ?>
			<div class="btn-group">
				<?php echo $this->form->getInput('contenthistory'); ?>
			</div>
			<?php endif; ?>
		</div>
		<fieldset>
			<ul class="nav nav-tabs">
				<li class="active"><a href="#editor" data-toggle="tab"><?php echo JText::_('COM_CONTENT_ARTICLE_CONTENT') ?></a></li>
				<?php if ($params->get('show_urls_images_frontend') ) : ?>
				<li><a href="#images" data-toggle="tab"><?php echo JText::_('COM_CONTENT_IMAGES_AND_URLS') ?></a></li>
				<?php endif; ?>
				<li><a href="#publishing" data-toggle="tab"><?php echo JText::_('COM_CONTENT_PUBLISHING') ?></a></li>
				<li><a href="#language" data-toggle="tab"><?php echo JText::_('JFIELD_LANGUAGE_LABEL') ?></a></li>
				<li><a href="#metadata" data-toggle="tab"><?php echo JText::_('COM_CONTENT_METADATA') ?></a></li>
			</ul>

			<div class="tab-content">
				<div class="tab-pane active" id="editor">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('title'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('title'); ?>
						</div>
					</div>

					<?php if (is_null($this->item->id)) : ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('alias'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('alias'); ?>
						</div>
					</div>
					<?php endif; ?>

					<?php echo $this->form->getInput('articletext'); ?>
				</div>
				<?php if ($params->get('show_urls_images_frontend')): ?>
				<div class="tab-pane" id="images">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('image_intro', 'images'); ?>
							<?php echo $this->form->getInput('image_intro', 'images'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('image_intro_alt', 'images'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('image_intro_alt', 'images'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('image_intro_caption', 'images'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('image_intro_caption', 'images'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('float_intro', 'images'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('float_intro', 'images'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('image_fulltext', 'images'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('image_fulltext', 'images'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('image_fulltext_alt', 'images'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('image_fulltext_alt', 'images'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('image_fulltext_caption', 'images'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('image_fulltext_caption', 'images'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('float_fulltext', 'images'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('float_fulltext', 'images'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('urla', 'urls'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('urla', 'urls'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('urlatext', 'urls'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('urlatext', 'urls'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="controls">
							<?php echo $this->form->getInput('targeta', 'urls'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('urlb', 'urls'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('urlb', 'urls'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('urlbtext', 'urls'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('urlbtext', 'urls'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="controls">
							<?php echo $this->form->getInput('targetb', 'urls'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('urlc', 'urls'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('urlc', 'urls'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('urlctext', 'urls'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('urlctext', 'urls'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="controls">
							<?php echo $this->form->getInput('targetc', 'urls'); ?>
						</div>
					</div>
				</div>
				<?php endif; ?>
				<div class="tab-pane" id="publishing">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('catid'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('catid'); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('tags'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('tags'); ?>
						</div>
					</div>
					<?php if ($params->get('save_history', 0)) : ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('version_note'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('version_note'); ?>
						</div>
					</div>
					<?php endif; ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('created_by_alias'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('created_by_alias'); ?>
						</div>
					</div>
					<?php if ($this->item->params->get('access-change')) : ?>
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
							<div class="control-label">
							</div>
							<div class="controls">
								<?php echo JText::_('COM_CONTENT_ORDERING'); ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
				<div class="tab-pane" id="language">
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('language'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('language'); ?>
						</div>
					</div>
				</div>
				<div class="tab-pane" id="metadata">
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
					<input type="hidden" name="return" value="<?php echo $this->return_page; ?>" />
					<?php if ($this->params->get('enable_category', 0) == 1) :?>
					<input type="hidden" name="jform[catid]" value="<?php echo $this->params->get('catid', 1); ?>" />
					<?php endif; ?>
				</div>
			</div>
			<?php echo JHtml::_('form.token'); ?>
		</fieldset>
	</form>
</div>
