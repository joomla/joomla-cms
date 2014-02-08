<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

$saveHistory = $this->state->get('params')->get('save_history', 0);

JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'weblink.cancel' || document.formvalidator.isValid(document.id('weblink-form')))
		{
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('weblink-form'));
		}
	}
</script>
<div class="weblink-edit">

<form action="<?php echo JRoute::_('index.php?option=com_weblinks&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="weblink-form" class="form-validate">
	<div class="col main-section">
		<fieldset class="adminform">
			<legend><?php echo empty($this->item->id) ? JText::_('COM_WEBLINKS_NEW_WEBLINK') : JText::sprintf('COM_WEBLINKS_EDIT_WEBLINK', $this->item->id); ?></legend>
			<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('title'); ?>
				<?php echo $this->form->getInput('title'); ?></li>

				<li><?php echo $this->form->getLabel('alias'); ?>
				<?php echo $this->form->getInput('alias'); ?></li>

				<li><?php echo $this->form->getLabel('url'); ?>
				<?php echo $this->form->getInput('url'); ?></li>

				<li><?php echo $this->form->getLabel('catid'); ?>
				<?php echo $this->form->getInput('catid'); ?></li>

				<li><?php echo $this->form->getLabel('state'); ?>
				<?php echo $this->form->getInput('state'); ?></li>

				<li><?php echo $this->form->getLabel('access'); ?>
				<?php echo $this->form->getInput('access'); ?></li>

				<li><?php echo $this->form->getLabel('ordering'); ?>
				<?php echo $this->form->getInput('ordering'); ?></li>

				<li><?php echo $this->form->getLabel('language'); ?>
				<?php echo $this->form->getInput('language'); ?></li>

				<!-- Tag field -->
				<li><?php echo $this->form->getLabel('tags'); ?>
					<div class="is-tagbox">
						<?php echo $this->form->getInput('tags'); ?>
					</div>
				</li>

				<?php if ($saveHistory) : ?>
					<li><?php echo $this->form->getLabel('version_note'); ?>
					<?php echo $this->form->getInput('version_note'); ?></li>
				<?php endif; ?>

				<li><?php echo $this->form->getLabel('id'); ?>
				<?php echo $this->form->getInput('id'); ?></li>
			</ul>

			<div>
				<?php echo $this->form->getLabel('description'); ?>
				<div class="clr"></div>
				<?php echo $this->form->getInput('description'); ?>
			</div>
		</fieldset>
	</div>

	<div class="col options-section">
		<?php echo JHtml::_('sliders.start', 'weblink-sliders-'.$this->item->id, array('useCookie' => 1)); ?>

		<?php echo JHtml::_('sliders.panel', JText::_('JGLOBAL_FIELDSET_PUBLISHING'), 'publishing-details'); ?>

		<fieldset class="panelform">
			<legend class="element-invisible"><?php echo JText::_('JGLOBAL_FIELDSET_PUBLISHING'); ?></legend>
			<ul class="adminformlist">

				<li><?php echo $this->form->getLabel('created_by'); ?>
				<?php echo $this->form->getInput('created_by'); ?></li>

				<li><?php echo $this->form->getLabel('created_by_alias'); ?>
				<?php echo $this->form->getInput('created_by_alias'); ?></li>

				<li><?php echo $this->form->getLabel('created'); ?>
				<?php echo $this->form->getInput('created'); ?></li>

				<li><?php echo $this->form->getLabel('publish_up'); ?>
				<?php echo $this->form->getInput('publish_up'); ?></li>

				<li><?php echo $this->form->getLabel('publish_down'); ?>
				<?php echo $this->form->getInput('publish_down'); ?></li>

				<?php if ($this->item->modified_by) : ?>
					<li><?php echo $this->form->getLabel('modified_by'); ?>
					<?php echo $this->form->getInput('modified_by'); ?></li>

					<li><?php echo $this->form->getLabel('modified'); ?>
					<?php echo $this->form->getInput('modified'); ?></li>
				<?php endif; ?>

				<?php if ($this->item->hits) : ?>
					<li><?php echo $this->form->getLabel('hits'); ?>
					<?php echo $this->form->getInput('hits'); ?></li>
				<?php endif; ?>

			</ul>
		</fieldset>

		<?php echo $this->loadTemplate('params'); ?>

		<?php echo JHtml::_('sliders.panel', JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'), 'meta-options'); ?>
		<fieldset class="panelform">
		<legend class="element-invisible"><?php echo JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'); ?></legend>
			<?php echo $this->loadTemplate('metadata'); ?>
		</fieldset>

		<?php echo JHtml::_('sliders.end'); ?>

		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
	<div class="clr"></div>
</form>
</div>
