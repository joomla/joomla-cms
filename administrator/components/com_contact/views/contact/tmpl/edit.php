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
$pane = &JPane::getInstance('sliders', array('allowAllClose' => true));

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
<div class="col width-50">

		<fieldset>
			<legend><?php echo empty($this->item->id) ? JText::_('Contact_New_Contact') : JText::sprintf('Contact_Edit_Contact', $this->item->id); ?></legend>


				<div>
					<?php echo $this->form->getLabel('name'); ?><br />
					<?php echo $this->form->getInput('name'); ?>
				</div>
				<div>
					<?php echo $this->form->getLabel('alias'); ?><br />
					<?php echo $this->form->getInput('alias'); ?>
				</div>
				<div>
					<?php echo $this->form->getLabel('user_id'); ?><br />
					<?php echo $this->form->getInput('user_id'); ?>
				</div>
				<div>
					<?php echo $this->form->getLabel('access'); ?><br />
					<?php echo $this->form->getInput('access'); ?>
				</div>

				<div>
					<?php echo $this->form->getLabel('published'); ?><br />
					<?php echo $this->form->getInput('published'); ?>
				</div>
				<div>
					<?php echo $this->form->getLabel('catid'); ?><br />
					<?php echo $this->form->getInput('catid'); ?>
				</div>
				<div>
					<?php echo $this->form->getLabel('ordering'); ?><br />
					<?php echo $this->form->getInput('ordering'); ?>
				</div>

		</fieldset>

</div>

<div class="col width-50">
		<div  style="float:left">
		<?php echo $pane->startPane('contact-pane'); ?>
			<?php echo $pane->startPanel(JText::_('Contact_Details'), 'basic-options'); ?>
			<legend><?php echo empty($this->item->id) ? JText::_('Contact_Contact_Details') : JText::sprintf('Contact_Edit_Details', $this->item->id); ?></legend>
				<ol>
					<li>
						<?php echo $this->form->getLabel('con_position'); ?><br />
						<?php echo $this->form->getInput('con_position'); ?>
					</li>
					<li>
						<?php echo $this->form->getLabel('email_to'); ?><br />
						<?php echo $this->form->getInput('email_to'); ?>
					</li>
					<li>
						<?php echo $this->form->getLabel('address'); ?><br />
						<?php echo $this->form->getInput('address'); ?>
					</li>
					<li>
						<?php echo $this->form->getLabel('suburb'); ?><br />
						<?php echo $this->form->getInput('suburb'); ?>
					</li>
					<li>
						<?php echo $this->form->getLabel('state'); ?><br />
						<?php echo $this->form->getInput('state'); ?>
					</li>
					<li>
						<?php echo $this->form->getLabel('postcode'); ?><br />
						<?php echo $this->form->getInput('postcode'); ?>
					</li>
					<li>
						<?php echo $this->form->getLabel('country'); ?><br />
						<?php echo $this->form->getInput('country'); ?>
					</li>
					<li>
						<?php echo $this->form->getLabel('telephone'); ?><br />
						<?php echo $this->form->getInput('telephone'); ?>
					</li>
					<li>
						<?php echo $this->form->getLabel('mobile'); ?><br />
						<?php echo $this->form->getInput('mobile'); ?>
					</li>
					<li>
						<?php echo $this->form->getLabel('webpage'); ?><br />
						<?php echo $this->form->getInput('webpage'); ?>
					</li>
					<li>
						<?php echo $this->form->getLabel('misc'); ?><br />
						<?php echo $this->form->getInput('misc'); ?>
					</li>
				</ol>

			<?php echo $pane->endPanel(); ?>
			<?php echo $pane->startPanel(JText::_('Contact_Fieldset_Options'), 'display-options'); ?>
						<legend><?php echo empty($this->item->id) ? JText::_('Contact_Display_Details') : JText::sprintf('Contact_Display_Details', $this->item->id); ?></legend>
				<table>
					<?php foreach($this->form->getFields('params') as $field): ?>
						<?php if ($field->hidden): ?>
							<?php echo $field->input; ?>
						<?php else: ?>
							<tr><td class="paramlist_key" width="40%">
									<?php echo $field->label; ?>
								</td>
								<td class="paramlist_value">
									<?php echo $field->input; ?>
								</td>
							</tr>
						<?php endif; ?>
					<?php endforeach; ?>
			</table>
			<?php echo $pane->endPanel(); ?>
			<?php echo $pane->startPanel(JText::_('Contact_Fieldset_Contact_Form'), 'email-options'); ?>
				<ol>
					<li>
						<?php echo $this->form->getLabel('show_email_form'); ?><br />
						<?php echo $this->form->getInput('show_email_form'); ?>
					</li>
					<li>
						<?php echo $this->form->getLabel('email_description'); ?><br />
						<?php echo $this->form->getInput('email_description'); ?>
					</li>
					<li>
						<?php echo $this->form->getLabel('show_email_copy'); ?><br />
						<?php echo $this->form->getInput('show_email_copy'); ?>
					</li>
								<li>
						<?php echo $this->form->getLabel('banned_email'); ?><br />
						<?php echo $this->form->getInput('banned_email'); ?>
					</li>
					<li>
						<?php echo $this->form->getLabel('banned_subject'); ?><br />
						<?php echo $this->form->getInput('banned_subject'); ?>
					</li>
								<li>
						<?php echo $this->form->getLabel('banned_text'); ?><br />
						<?php echo $this->form->getInput('banned_text'); ?>
					</li>
				<ol>

			<?php echo $pane->endPanel(); ?>

		<?php echo $pane->endPane(); ?>
</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
