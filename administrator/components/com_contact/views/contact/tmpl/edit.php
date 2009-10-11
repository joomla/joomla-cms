<?php
/**
 * @version		$Id: edit.php 12295 2009-06-22 11:10:18Z eddieajau $
 * @package		Joomla.Administrator
 * @subpackage	com_contact
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;


jimport('joomla.html.pane');
$pane = &JPane::getInstance('sliders');

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
			<legend><?php echo empty($this->item->id) ? JText::_('Contact_New_Contact') : JText::sprintf('Contact_Edit_Contact', $this->item->id); ?></legend>
			
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
					
					<div class="clr"> </div>
					<?php echo $this->form->getLabel('misc'); ?>
					<div class="clr"> </div>
					<?php echo $this->form->getInput('misc'); ?>

		</fieldset>

</div>
	

<div class="width-50 fltrt">
		<?php echo $pane->startPane('contact-pane'); ?>
			<?php echo $pane->startPanel(JText::_('Contact_Details'), 'basic-options'); ?>
			<fieldset class="panelform">
			<p><?php echo empty($this->item->id) ? JText::_('Contact_Contact_Details') : JText::sprintf('Contact_Edit_Details', $this->item->id); ?></p>
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
			</fieldset>

			<?php echo $pane->endPanel(); ?>
			<?php echo $pane->startPanel(JText::_('Contact_Fieldset_Options'), 'display-options'); ?>
			<fieldset class="panelform">
				<p><?php echo empty($this->item->id) ? JText::_('Contact_Display_Details') : JText::sprintf('Contact_Display_Details', $this->item->id); ?></p>
					<?php foreach($this->form->getFields('params') as $field): ?>
						<?php if ($field->hidden): ?>
							<?php echo $field->input; ?>
						<?php else: ?>
							<div class="paramrow">
								<?php echo $field->label; ?>
								<?php echo $field->input; ?>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
			</fieldset>
			<?php echo $pane->endPanel(); ?>
			<?php echo $pane->startPanel(JText::_('Contact_Fieldset_Contact_Form'), 'email-options'); ?>
				<fieldset class="panelform">
				<div class="paramrow">
						<?php echo $this->form->getLabel('show_email_form'); ?>
						<?php echo $this->form->getInput('show_email_form'); ?>
					</div>
						<?php echo $this->form->getLabel('email_description'); ?>
						<?php echo $this->form->getInput('email_description'); ?>
					<div class="paramrow">
						<?php echo $this->form->getLabel('show_email_copy'); ?>
						<?php echo $this->form->getInput('show_email_copy'); ?>
					</div>
						<?php echo $this->form->getLabel('banned_email'); ?>
						<?php echo $this->form->getInput('banned_email'); ?>
			
						<?php echo $this->form->getLabel('banned_subject'); ?>
						<?php echo $this->form->getInput('banned_subject'); ?>
					
						<?php echo $this->form->getLabel('banned_text'); ?>
						<?php echo $this->form->getInput('banned_text'); ?>
				</fieldset>

			<?php echo $pane->endPanel(); ?>

		<?php echo $pane->endPane(); ?>
</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
