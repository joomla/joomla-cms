<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'message.cancel' || document.formvalidator.isValid(document.id('message-form')))
		{
			Joomla.submitform(task, document.getElementById('message-form'));
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_messages'); ?>" method="post" name="adminForm" id="message-form" class="form-validate form-horizontal">
	<fieldset class="adminform">
		<ul class="adminformlist">
			<li><?php echo $this->form->getLabel('user_id_to'); ?>
				<?php echo $this->form->getInput('user_id_to'); ?></li>

			<li><?php echo $this->form->getLabel('subject'); ?>
				<?php echo $this->form->getInput('subject'); ?></li>
		</ul>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo $this->form->getLabel('message'); ?></legend>
		<ul class="adminformlist">
			<li><?php echo $this->form->getInput('message'); ?> </li>
		</ul>
	</fieldset>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
