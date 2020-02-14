<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.tabstate');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', '#jform_catid', null, array('disable_search_threshold' => 0));
JHtml::_('formbehavior.chosen', 'select');
$this->tab_name = 'com-content-form';
$this->ignore_fieldsets = array('image-intro', 'image-full', 'jmetadata', 'sppostformats', 'item_associations');

// Create shortcut to parameters.
$params = $this->state->get('params');

// This checks if the editor config options have ever been saved. If they haven't they will fall back to the original settings.
$editoroptions = isset($params->show_publishing_options);
if (!$editoroptions)
{
	$params->show_urls_images_frontend = '0';
}

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(task)
	{
		if (task == 'article.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
		{
			" . $this->form->getField('articletext')->save() . "
			Joomla.submitform(task);
		}
	}
");
?>
<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">
	<?php if ($params->get('show_page_heading', 1)) { ?>
	<div class="page-header">
		<h1>
			<?php echo $this->escape($params->get('page_heading')); ?>
		</h1>
	</div>
	<?php } ?>

	<form action="<?php echo JRoute::_('index.php?option=com_content&a_id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-vertical com-content-adminForm">
		<div class="btn-toolbar">
			<button type="button" class="btn btn-success" onclick="Joomla.submitbutton('article.save')">
				<i class="fa fa-check"></i><?php echo JText::_('JSAVE') ?>
			</button>
			<?php if ($params->get('save_history', 0)) {
					$contenthistory = str_replace( 'btn', 'btn btn-primary', $this->form->getInput('contenthistory') );
					echo ( str_replace('icon-archive', 'fa fa-archive', $contenthistory) );
			} ?>
			<button type="button" class="btn btn-danger" onclick="Joomla.submitbutton('article.cancel')">
				<i class="fa fa-remove"></i><?php echo JText::_('JCANCEL') ?>
			</button>
		</div>

		<fieldset>
			<?php echo JHtml::_('bootstrap.startTabSet', $this->tab_name, array('active' => 'editor')); ?>

				<?php echo JHtml::_('bootstrap.addTab', $this->tab_name, 'editor', JText::_('COM_CONTENT_ARTICLE_CONTENT')); ?>
					<?php echo $this->form->renderField('title'); ?>

					<?php if (is_null($this->item->id)) : ?>
						<?php echo $this->form->renderField('alias'); ?>
					<?php endif; ?>

					<?php echo $this->form->getInput('articletext'); ?>

					<?php if ($this->captchaEnabled) : ?>
						<?php echo $this->form->renderField('captcha'); ?>
					<?php endif; ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>

				<?php if ($params->get('show_urls_images_frontend')): ?>
					<?php echo JHtml::_('bootstrap.addTab', $this->tab_name, 'images', JText::_('COM_CONTENT_IMAGES_AND_URLS')); ?>
						<?php echo $this->form->renderField('image_intro', 'images'); ?>
						<?php echo $this->form->renderField('image_intro_alt', 'images'); ?>
						<?php echo $this->form->renderField('image_intro_caption', 'images'); ?>
						<?php echo $this->form->renderField('float_intro', 'images'); ?>
						<?php echo $this->form->renderField('image_fulltext', 'images'); ?>
						<?php echo $this->form->renderField('image_fulltext_alt', 'images'); ?>
						<?php echo $this->form->renderField('image_fulltext_caption', 'images'); ?>
						<?php echo $this->form->renderField('float_fulltext', 'images'); ?>
						<?php echo $this->form->renderField('urla', 'urls'); ?>
						<?php echo $this->form->renderField('urlatext', 'urls'); ?>
						<div class="control-group">
							<div class="controls">
								<?php echo $this->form->getInput('targeta', 'urls'); ?>
							</div>
						</div>
						<?php echo $this->form->renderField('urlb', 'urls'); ?>
						<?php echo $this->form->renderField('urlbtext', 'urls'); ?>
						<div class="control-group">
							<div class="controls">
								<?php echo $this->form->getInput('targetb', 'urls'); ?>
							</div>
						</div>
						<?php echo $this->form->renderField('urlc', 'urls'); ?>
						<?php echo $this->form->renderField('urlctext', 'urls'); ?>
						<div class="control-group">
							<div class="controls">
								<?php echo $this->form->getInput('targetc', 'urls'); ?>
							</div>
						</div>
					<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php endif; ?>
				
				<?php echo JHtml::_('bootstrap.addTab', $this->tab_name, 'sppostformats', JText::_('BLOG_OPTIONS')); ?>
					<?php $attribs = json_decode($this->item->attribs); ?>
					<?php echo $this->form->renderField('spfeatured_image','attribs', (isset($attribs->spfeatured_image)? $attribs->spfeatured_image: '')); ?>
					<?php echo $this->form->renderField('post_format','attribs', (isset($attribs->post_format)? $attribs->post_format: '')); ?>
					<?php echo $this->form->renderField('gallery','attribs', (isset($attribs->gallery)? $attribs->gallery: '')); ?>
					<?php echo $this->form->renderField('audio','attribs', (isset($attribs->audio)? $attribs->audio: '')); ?>
					<?php echo $this->form->renderField('video','attribs', (isset($attribs->video)? $attribs->video: '')); ?>
					<?php echo $this->form->renderField('link_title','attribs', (isset($attribs->link_title)? $attribs->link_title: '')); ?>
					<?php echo $this->form->renderField('link_url','attribs', (isset($attribs->link_url)? $attribs->link_url: '')); ?>
					<?php echo $this->form->renderField('quote_text','attribs',(isset($attribs->quote_text)? $attribs->quote_text: '')); ?>
					<?php echo $this->form->renderField('quote_author','attribs',(isset($attribs->quote_author)? $attribs->quote_author: '')); ?>
					<?php echo $this->form->renderField('post_status','attribs',(isset($attribs->post_status)? $attribs->post_status: '')); ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>

				<?php echo JLayoutHelper::render('joomla.edit.params', $this); ?>
				
				<?php echo JHtml::_('bootstrap.addTab', $this->tab_name, 'publishing', JText::_('COM_CONTENT_PUBLISHING')); ?>
					<?php echo $this->form->renderField('catid'); ?>
					<?php echo $this->form->renderField('tags'); ?>
					<?php if ($params->get('save_history', 0)) : ?>
						<?php echo $this->form->renderField('version_note'); ?>
					<?php endif; ?>
					<?php if ($params->get('show_publishing_options', 1) == 1) : ?>
						<?php echo $this->form->renderField('created_by_alias'); ?>
					<?php endif; ?>
					<?php if ($this->item->params->get('access-change')) : ?>
						<?php echo $this->form->renderField('state'); ?>
						<?php echo $this->form->renderField('featured'); ?>
						<?php if ($params->get('show_publishing_options', 1) == 1) : ?>					
							<?php echo $this->form->renderField('publish_up'); ?>
							<?php echo $this->form->renderField('publish_down'); ?>
						<?php endif; ?>
					<?php endif; ?>
					<?php echo $this->form->renderField('access'); ?>
					<?php if (is_null($this->item->id)) : ?>
						<div class="control-group">
							<div class="control-label">
							</div>
							<div class="controls">
								<?php echo JText::_('COM_CONTENT_ORDERING'); ?>
							</div>
						</div>
					<?php endif; ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>

				<?php echo JHtml::_('bootstrap.addTab', $this->tab_name, 'language', JText::_('JFIELD_LANGUAGE_LABEL')); ?>
					<?php echo $this->form->renderField('language'); ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>

				<!-- <div class="tab-pane" id="metadata"> -->
				<?php if ($params->get('show_publishing_options', 1) == 1) : ?>	
					<?php echo JHtml::_('bootstrap.addTab', $this->tab_name, 'metadata', JText::_('COM_CONTENT_METADATA')); ?>
						<?php echo $this->form->renderField('metadesc'); ?>
						<?php echo $this->form->renderField('metakey'); ?>
					<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php endif; ?>

			<?php echo JHtml::_('bootstrap.endTabSet'); ?>

			<input type="hidden" name="task" value="" />
			<input type="hidden" name="return" value="<?php echo $this->return_page; ?>" />

			<?php if ($this->params->get('enable_category', 0) == 1) :?>
				<input type="hidden" name="jform[catid]" value="<?php echo $this->params->get('catid', 1); ?>" />
			<?php endif; ?>
			
			<?php echo JHtml::_('form.token'); ?>
		</fieldset>
	</form>
</div>
