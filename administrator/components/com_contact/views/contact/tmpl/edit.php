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
				
				<?php echo $this->form->getLabel('fax'); ?>
				<?php echo $this->form->getInput('fax'); ?>

				<?php echo $this->form->getLabel('webpage'); ?>
				<?php echo $this->form->getInput('webpage'); ?>

				<?php echo $this->form->getLabel('sortname1'); ?>
				<?php echo $this->form->getInput('sortname1'); ?>

				<?php echo $this->form->getLabel('sortname2'); ?>
				<?php echo $this->form->getInput('sortname2'); ?>

				<?php echo $this->form->getLabel('sortname3'); ?>
				<?php echo $this->form->getInput('sortname3'); ?>

			</fieldset>

			<?php echo $this->loadTemplate('params'); ?>

			<?php echo $this->loadTemplate('metadata'); ?>
		<?php echo JHtml::_('sliders.end'); ?>
	</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>