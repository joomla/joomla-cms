<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
$script = "\t".'Joomla.submitbutton = function(pressbutton) {'."\n";
$script .= "\t\t".'var form = document.adminForm;'."\n";
$script .= "\t\t".'if (pressbutton == \'mail.cancel\') {'."\n";
$script .= "\t\t\t".'Joomla.submitform(pressbutton);'."\n";
$script .= "\t\t\t".'return;'."\n";
$script .= "\t\t".'}'."\n";
$script .= "\t\t".'// do field validation'."\n";
$script .= "\t\t".'if (form.jform_subject.value == ""){'."\n";
$script .= "\t\t\t".'alert("'.JText::_('COM_USERS_MAIL_PLEASE_FILL_IN_THE_SUBJECT', true).'");'."\n";
$script .= "\t\t".'} else if (getSelectedValue(\'adminForm\',\'jform[group]\') < 0){'."\n";
$script .= "\t\t\t".'alert("'.JText::_('COM_USERS_MAIL_PLEASE_SELECT_A_GROUP', true).'");'."\n";
$script .= "\t\t".'} else if (form.jform_message.value == ""){'."\n";
$script .= "\t\t\t".'alert("'.JText::_('COM_USERS_MAIL_PLEASE_FILL_IN_THE_MESSAGE', true).'");'."\n";
$script .= "\t\t".'} else {'."\n";
$script .= "\t\t\t".'Joomla.submitform(pressbutton);'."\n";
$script .= "\t\t".'}'."\n";
$script .= "\t\t".'}'."\n";

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');

JFactory::getDocument()->addScriptDeclaration($script);
?>

<form action="<?php echo JRoute::_('index.php?option=com_users&view=mail'); ?>" name="adminForm" method="post" id="adminForm">

	<div class="width-30 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_USERS_MAIL_DETAILS'); ?></legend>
			<ul class="adminformlist">
			<li><?php echo $this->form->getLabel('recurse'); ?>
			<?php echo $this->form->getInput('recurse'); ?></li>

			<li><?php echo $this->form->getLabel('mode'); ?>
			<?php echo $this->form->getInput('mode'); ?></li>
			
			<li><?php echo $this->form->getLabel('disabled'); ?>
			<?php echo $this->form->getInput('disabled'); ?></li>

			<li><?php echo $this->form->getLabel('group'); ?>
			<?php echo $this->form->getInput('group'); ?></li>

			<li><?php echo $this->form->getLabel('bcc'); ?>
			<?php echo $this->form->getInput('bcc'); ?></li>
			</ul>
		</fieldset>
	</div>

	<div class="width-70 fltrt">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_USERS_MAIL_MESSAGE'); ?></legend>
			<ul class="adminformlist">
			<li><?php echo $this->form->getLabel('subject'); ?>
			<?php echo $this->form->getInput('subject'); ?></li>

			<li><?php echo $this->form->getLabel('message'); ?>
			<?php echo $this->form->getInput('message'); ?></li>
			</ul>
		</fieldset>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>

	<div class="clr"></div>
</form>