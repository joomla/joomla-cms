<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_contact
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
<!--
	function submitbutton(task)
	{
		if (task == 'contact.cancel' || document.formvalidator.isValid(document.id('contact-form'))) {
		}
		// @todo Deal with the editor methods
		submitform(task);
	}
// -->
</script>

<form action="<?php JRoute::_('index.php?option=com_contact'); ?>" method="post" name="adminForm" id="contact-form" class="form-validate">
	<div class="width-50 fltlft">
		<fieldset class="adminform">
			<legend><?php echo empty($this->item->id) ? JText::_('COM_CONTACT_NEW_CONTACT') : JText::sprintf('COM_CONTACT_EDIT_CONTACT', $this->item->id); ?></legend>

				<?php echo $this->form->getLabel('name'); ?>
				<?php echo $this->form->getInput('name'); ?>

				<?php echo $this->form->getLabel('alias'); ?>
				<?php echo $this->form->getInput('alias'); ?>

				<?php echo $this->form->getLabel('user_id'); ?>
				<?php echo $this->form->getInput('user_id'); ?>

				<?php echo $this->form->getLabel('access'); ?>
				<?php echo $this->form->getInput('access'); ?>

				<?php echo $this->form->getLabel('published'); ?>
				<?php echo $this->form->getInput('published'); ?>

				<?php echo $this->form->getLabel('catid'); ?>
				<?php echo $this->form->getInput('catid'); ?>

				<?php echo $this->form->getLabel('ordering'); ?>
				<?php echo $this->form->getInput('ordering'); ?>

				<?php echo $this->form->getLabel('language'); ?>
				<?php echo $this->form->getInput('language'); ?>

				<?php echo $this->form->getLabel('id'); ?>
				<?php echo $this->form->getInput('id'); ?>

				<div class="clr"> </div>
				<?php echo $this->form->getLabel('misc'); ?>
				<div class="clr"> </div>
				<?php echo $this->form->getInput('misc'); ?>
			</fieldset>
		</div>


		<div class="width-50 fltrt">
			<?php echo  JHtml::_('sliders.start', 'contact-slider'); ?>
				<?php echo JHtml::_('sliders.panel',JText::_('COM_CONTACT_CONTACT_DETAILS'), 'basic-options'); ?>
					<fieldset class="panelform">
					<p><?php echo empty($this->item->id) ? JText::_('COM_CONTACT_DETAILS') : JText::sprintf('COM_CONTACT_EDIT_DETAILS', $this->item->id); ?></p>
						<?php echo $this->form->getLabel('image'); ?>
						<?php echo $this->form->getInput('image'); ?>


						<?php echo $this->form->getLabel('con_position'); ?>
						<?php echo $this->form->getInput('con_position'); ?>

						<?php echo $this->form->getLabel('email_to'); ?>
						<?php echo $this->form->getInput('email_to'); ?>

						<?php echo $this->form->getLabel('address'); ?>
						<?php echo $this->form->getInput('address'); ?>

						<?php echo $this->form->getLabel('suburb'); ?>
						<?php echo $this->form->getInput('suburb'); ?>

						<?php echo $this->form->getLabel('state'); ?>
						<?php echo $this->form->getInput('state'); ?>

						<?php echo $this->form->getLabel('postcode'); ?>
						<?php echo $this->form->getInput('postcode'); ?>

						<?php echo $this->form->getLabel('country'); ?>
						<?php echo $this->form->getInput('country'); ?>

						<?php echo $this->form->getLabel('telephone'); ?>
						<?php echo $this->form->getInput('telephone'); ?>

						<?php echo $this->form->getLabel('mobile'); ?>
						<?php echo $this->form->getInput('mobile'); ?>

						<?php echo $this->form->getLabel('webpage'); ?>
						<?php echo $this->form->getInput('webpage'); ?>

						<?php echo $this->form->getLabel('sortname1'); ?>
						<?php echo $this->form->getInput('sortname1'); ?>

						<?php echo $this->form->getLabel('sortname2'); ?>
						<?php echo $this->form->getInput('sortname2'); ?>

						<?php echo $this->form->getLabel('sortname3'); ?>
						<?php echo $this->form->getInput('sortname3'); ?>

			</fieldset>

		<?php echo JHtml::_('sliders.panel', JText::_('COM_CONTACT_FIELDSET_OPTIONS'), 'display-options'); ?>
			<fieldset class="panelform">
				<p><?php echo empty($this->item->id) ? JText::_('COM_CONTACT_CONTACT_DISPLAY_DETAILS') : JText::sprintf('COM_CONTACT_CONTACT_DISPLAY_DETAILS', $this->item->id); ?></p>
					<?php foreach($this->form->getGroup('params') as $field): ?>
						<?php if ($field->hidden): ?>
							<?php echo $field->input; ?>
						<?php else: ?>
							<?php echo $field->label; ?>
							<?php echo $field->input; ?>
						<?php endif; ?>
					<?php endforeach; ?>
			</fieldset>

		<?php echo JHtml::_('sliders.panel',JText::_('COM_CONTACT_FIELDSET_CONTACT_FORM'), 'email-options'); ?>

				<fieldset class="panelform">
				<p><?php echo JText::_('COM_CONTACT_EMAIL_FORM_DETAILS'); ?></p>
					<?php foreach($this->form->getGroup('email_form') as $field): ?>
						<?php if ($field->hidden): ?>
							<?php echo $field->input; ?>
						<?php else: ?>
							<?php echo $field->label; ?>
							<?php echo $field->input; ?>
						<?php endif; ?>
					<?php endforeach; ?>
				</fieldset>


		<?php echo JHtml::_('sliders.panel',JText::_('JGLOBAL_FIELDSET_PUBLISHING'), 'publishing-details'); ?>

		<fieldset class="panelform">

			<?php echo $this->form->getLabel('created_by'); ?>
			<?php echo $this->form->getInput('created_by'); ?>

			<?php echo $this->form->getLabel('created_by_alias'); ?>
			<?php echo $this->form->getInput('created_by_alias'); ?>

			<?php echo $this->form->getLabel('created'); ?>
			<?php echo $this->form->getInput('created'); ?>

			<?php echo $this->form->getLabel('publish_up'); ?>
			<?php echo $this->form->getInput('publish_up'); ?>

			<?php echo $this->form->getLabel('publish_down'); ?>
			<?php echo $this->form->getInput('publish_down'); ?>

			<?php echo $this->form->getLabel('modified'); ?>
			<?php echo $this->form->getInput('modified'); ?>

			<?php echo $this->form->getLabel('version'); ?>
			<?php echo $this->form->getInput('version'); ?>

		</fieldset>
		
		
		<?php echo JHtml::_('sliders.panel',JText::_('JGLOBAL_FIELDSET_METADATA'), 'meta-options'); ?>
				<fieldset class="panelform">

					<?php echo $this->form->getLabel('metadesc'); ?>
					<?php echo $this->form->getInput('metadesc'); ?>

					<?php echo $this->form->getLabel('metakey'); ?>
					<?php echo $this->form->getInput('metakey'); ?>

					<?php foreach($this->form->getGroup('metadata') as $field): ?>
						<?php echo $field->label; ?>
						<?php echo $field->input; ?>
					<?php endforeach; ?>

					<?php echo $this->form->getLabel('xreference'); ?>
					<?php echo $this->form->getInput('xreference'); ?>
				</fieldset>
	
					
	<?php echo JHtml::_('sliders.end'); ?>
</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>