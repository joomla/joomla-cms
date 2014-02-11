<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'language.cancel' || document.formvalidator.isValid(document.id('language-form')))
		{
			Joomla.submitform(task, document.getElementById('language-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_languages&layout=edit&lang_id='.(int) $this->item->lang_id); ?>" method="post" name="adminForm" id="language-form" class="form-validate form-horizontal">

	<?php echo JLayoutHelper::render('joomla.edit.item_title', $this); ?>

	<fieldset>
	<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('JDETAILS', true)); ?>
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
						<span id="flag">
							<?php echo JHtml::_('image', 'mod_languages/' . $this->form->getValue('image') . '.gif', $this->form->getValue('image'), array('title' => $this->form->getValue('image')), true); ?>
						</span>
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
			<?php if ($this->canDo->get('core.edit.state')) : ?>
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
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'metadata', JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS', true)); ?>
			<?php foreach ($this->form->getFieldset('metadata') as $field) : ?>
				<div class="control-group">
					<?php if (!$field->hidden) : ?>
						<div class="control-label">
							<?php echo $field->label; ?>
						</div>
					<?php endif; ?>
					<div class="controls">
						<?php echo $field->input; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'site_name', JText::_('COM_LANGUAGES_FIELDSET_SITE_NAME_LABEL', true)); ?>
			<?php foreach ($this->form->getFieldset('site_name') as $field) : ?>
				<div class="control-group">
					<?php if (!$field->hidden) : ?>
						<div class="control-label">
							<?php echo $field->label; ?>
						</div>
					<?php endif; ?>
					<div class="controls">
						<?php echo $field->input; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

	<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</fieldset>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<script type="text/javascript">
	jQuery('#jform_image').on('change', function() {
		var flag = this.value;
		if (!jQuery('#flag img').attr('src'))
		{
			jQuery('#flag img').attr('src', '<?php echo JUri::root(true);?>' + '/media/mod_languages/images/' + flag + '.gif');
		}
		else
		{
			jQuery('#flag img').attr('src', function(index, attr) {
				return attr.replace(jQuery('#flag img').attr('title') + '.gif', flag + '.gif')
			})
		}
		jQuery('#flag img').attr('title', flag).attr('alt', flag);
	});
</script>
