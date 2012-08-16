<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$canDo = LanguagesHelper::getActions();
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'language.cancel' || document.formvalidator.isValid(document.id('language-form'))) {
			Joomla.submitform(task, document.getElementById('language-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_languages&layout=edit&lang_id='.(int) $this->item->lang_id); ?>" method="post" name="adminForm" id="language-form" class="form-validate form-horizontal">
	<fieldset>
	<ul class="nav nav-tabs">
		<li class="active"><a href="#details" data-toggle="tab"><?php echo JText::_('JDETAILS');?></a></li>
		<li><a href="#metadata" data-toggle="tab"><?php echo JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS');?></a></li>
		<li><a href="#site_name" data-toggle="tab"><?php echo JText::_('COM_LANGUAGES_FIELDSET_SITE_NAME_LABEL');?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="details">
			<div class="control-group">
				<div class="controls">
					<?php if ($this->item->lang_id) : ?>
						<?php echo JText::sprintf('JGLOBAL_RECORD_NUMBER', $this->item->lang_id); ?>
					<?php else : ?>
						<?php echo JText::_('COM_LANGUAGES_VIEW_LANGUAGE_EDIT_NEW_TITLE'); ?>
					<?php endif; ?>
				</div>
			</div>

			<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('title'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('title'); ?>
					</div>
			</div>
			<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('title_native'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('title_native'); ?>
					</div>
			</div>
			<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('sef'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('sef'); ?>
					</div>
			</div>
			<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('image'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('image'); ?>
					</div>
			</div>
			<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('lang_code'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('lang_code'); ?>
					</div>
			</div>
			<?php if ($canDo->get('core.edit.state')) : ?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('published'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('published'); ?>
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
			<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('description'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('description'); ?>
					</div>
			</div>
			<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('lang_id'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('lang_id'); ?>
					</div>
			</div>
		</div>
		<div class="tab-pane" id="metadata">
			<?php foreach($this->form->getFieldset('metadata') as $field): ?>
				<div class="control-group">
					<?php if (!$field->hidden): ?>
						<div class="control-label">
							<?php echo $field->label; ?>
						</div>
					<?php endif; ?>
					<div class="controls">
						<?php echo $field->input; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="tab-pane" id="site_name">
			<?php foreach($this->form->getFieldset('site_name') as $field): ?>
				<div class="control-group">
					<?php if (!$field->hidden): ?>
						<div class="control-label">
							<?php echo $field->label; ?>
						</div>
					<?php endif; ?>
					<div class="controls">
						<?php echo $field->input; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	</fieldset>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
