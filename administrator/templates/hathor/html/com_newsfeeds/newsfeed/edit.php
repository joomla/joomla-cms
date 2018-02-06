<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');

$app = JFactory::getApplication();
$input = $app->input;

$saveHistory = $this->state->get('params')->get('save_history', 0);

$assoc = JLanguageAssociations::isEnabled();

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(task)
	{
		if (task == 'newsfeed.cancel' || document.formvalidator.isValid(document.getElementById('newsfeed-form')))
		{
			Joomla.submitform(task, document.getElementById('newsfeed-form'));
		}
	}
");
?>

<form action="<?php echo JRoute::_('index.php?option=com_newsfeeds&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="newsfeed-form" class="form-validate">
	<div class="col main-section">
		<fieldset class="adminform">
			<legend><?php echo empty($this->item->id) ? JText::_('COM_NEWSFEEDS_NEW_NEWSFEED') : JText::sprintf('COM_NEWSFEEDS_EDIT_NEWSFEED', $this->item->id); ?></legend>
			<ul class="adminformlist">
			<li><?php echo $this->form->getLabel('name'); ?>
			<?php echo $this->form->getInput('name'); ?></li>

   			<li><?php echo $this->form->getLabel('alias'); ?>
			<?php echo $this->form->getInput('alias'); ?></li>

			<li><?php echo $this->form->getLabel('link'); ?>
			<?php echo $this->form->getInput('link'); ?></li>

			<li><?php echo $this->form->getLabel('catid'); ?>
			<?php echo $this->form->getInput('catid'); ?></li>

			<li><?php echo $this->form->getLabel('published'); ?>
			<?php echo $this->form->getInput('published'); ?></li>

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
		</fieldset>
	</div>

	<div class="col options-section">
		<?php echo JHtml::_('sliders.start', 'newsfeed-sliders-' . $this->item->id, array('useCookie' => 1)); ?>

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

				<li><?php echo $this->form->getLabel('numarticles'); ?>
				<?php echo $this->form->getInput('numarticles'); ?></li>

				<li><?php echo $this->form->getLabel('cache_time'); ?>
				<?php echo $this->form->getInput('cache_time'); ?></li>

				<li><?php echo $this->form->getLabel('rtl'); ?>
				<?php echo $this->form->getInput('rtl'); ?></li>
			</ul>
			</fieldset>

			<?php echo $this->loadTemplate('params'); ?>

			<?php echo JHtml::_('sliders.panel', JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'), 'meta-options'); ?>
			<fieldset class="panelform">
			<legend class="element-invisible"><?php echo JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'); ?></legend>
				<?php echo $this->loadTemplate('metadata'); ?>
			</fieldset>

			<?php if ($assoc) : ?>
				<?php echo JHtml::_('sliders.panel', JText::_('JGLOBAL_FIELDSET_ASSOCIATIONS'), '-options');?>
				<?php echo $this->loadTemplate('associations'); ?>
			<?php endif; ?>

		<?php echo JHtml::_('sliders.end'); ?>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>

	<div class="clr"></div>
</form>
