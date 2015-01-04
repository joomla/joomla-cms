<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('behavior.formvalidation');

$app = JFactory::getApplication();
$input = $app->input;

$saveHistory = $this->state->get('params')->get('save_history', 0);

$assoc = JLanguageAssociations::isEnabled();

?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'contact.cancel' || document.formvalidator.isValid(document.id('contact-form')))
		{
			<?php echo $this->form->getField('misc')->save(); ?>
			Joomla.submitform(task, document.getElementById('contact-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_contact&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="contact-form" class="form-validate">
	<div class="col main-section">
		<fieldset class="adminform">
			<legend><?php echo empty($this->item->id) ? JText::_('COM_CONTACT_NEW_CONTACT') : JText::sprintf('COM_CONTACT_EDIT_CONTACT', $this->item->id); ?></legend>
			<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('name'); ?>
				<?php echo $this->form->getInput('name'); ?></li>

				<li><?php echo $this->form->getLabel('alias'); ?>
				<?php echo $this->form->getInput('alias'); ?></li>

				<li><?php echo $this->form->getLabel('user_id'); ?>
				<?php echo $this->form->getInput('user_id'); ?></li>

				<li><?php echo $this->form->getLabel('catid'); ?>
				<?php echo $this->form->getInput('catid'); ?></li>

				<li><?php echo $this->form->getLabel('published'); ?>
				<?php echo $this->form->getInput('published'); ?></li>

				<li><?php echo $this->form->getLabel('access'); ?>
				<?php echo $this->form->getInput('access'); ?></li>

				<li><?php echo $this->form->getLabel('ordering'); ?>
				<?php echo $this->form->getInput('ordering'); ?></li>

				<li><?php echo $this->form->getLabel('featured'); ?>
				<?php echo $this->form->getInput('featured'); ?></li>

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
			<div class="clr"></div>
			<?php echo $this->form->getLabel('misc'); ?>
			<div class="clr"></div>
			<?php echo $this->form->getInput('misc'); ?>
		</fieldset>
	</div>
    <div class="col options-section">
		<?php echo  JHtml::_('sliders.start', 'contact-slider'); ?>
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

				</ul>
			</fieldset>
			<?php echo JHtml::_('sliders.panel', JText::_('COM_CONTACT_CONTACT_DETAILS'), 'basic-options'); ?>

			<fieldset class="panelform">
			<legend class="element-invisible"><?php echo JText::_('COM_CONTACT_CONTACT_DETAILS'); ?></legend>
				<p><?php echo empty($this->item->id) ? JText::_('COM_CONTACT_DETAILS') : JText::sprintf('COM_CONTACT_EDIT_DETAILS', $this->item->id); ?></p>

				<ul class="adminformlist">
					<li><?php echo $this->form->getLabel('image'); ?>
					<?php echo $this->form->getInput('image'); ?></li>

					<li><?php echo $this->form->getLabel('con_position'); ?>
					<?php echo $this->form->getInput('con_position'); ?></li>

					<li><?php echo $this->form->getLabel('email_to'); ?>
					<?php echo $this->form->getInput('email_to'); ?></li>

					<li><?php echo $this->form->getLabel('address'); ?>
					<?php echo $this->form->getInput('address'); ?></li>

					<li><?php echo $this->form->getLabel('suburb'); ?>
					<?php echo $this->form->getInput('suburb'); ?></li>

					<li><?php echo $this->form->getLabel('state'); ?>
					<?php echo $this->form->getInput('state'); ?></li>

					<li><?php echo $this->form->getLabel('postcode'); ?>
					<?php echo $this->form->getInput('postcode'); ?></li>

					<li><?php echo $this->form->getLabel('country'); ?>
					<?php echo $this->form->getInput('country'); ?></li>

					<li><?php echo $this->form->getLabel('telephone'); ?>
					<?php echo $this->form->getInput('telephone'); ?></li>

					<li><?php echo $this->form->getLabel('mobile'); ?>
					<?php echo $this->form->getInput('mobile'); ?></li>

					<li><?php echo $this->form->getLabel('fax'); ?>
					<?php echo $this->form->getInput('fax'); ?></li>

					<li><?php echo $this->form->getLabel('webpage'); ?>
					<?php echo $this->form->getInput('webpage'); ?></li>

					<li><?php echo $this->form->getLabel('sortname1'); ?>
					<?php echo $this->form->getInput('sortname1'); ?></li>

					<li><?php echo $this->form->getLabel('sortname2'); ?>
					<?php echo $this->form->getInput('sortname2'); ?></li>

					<li><?php echo $this->form->getLabel('sortname3'); ?>
					<?php echo $this->form->getInput('sortname3'); ?></li>
				</ul>
			</fieldset>

			<?php echo $this->loadTemplate('params'); ?>

			<?php echo JHtml::_('sliders.panel', JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'), 'meta-options'); ?>
			<fieldset class="panelform">
			<legend class="element-invisible"><?php echo JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'); ?></legend>
				<?php echo $this->loadTemplate('metadata'); ?>
			</fieldset>

			<?php if ($assoc) : ?>
				<?php echo JHtml::_('sliders.panel', JText::_('COM_CONTACT_ITEM_ASSOCIATIONS_FIELDSET_LABEL'), '-options');?>
				<?php echo $this->loadTemplate('associations'); ?>
			<?php endif; ?>

		<?php echo JHtml::_('sliders.end'); ?>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
