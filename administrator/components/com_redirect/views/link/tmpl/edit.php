<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Include the HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

?>
<script type="text/javascript">
<!--
	function submitbutton(task)
	{
		if (task == 'link.cancel' || document.formvalidator.isValid(document.id('link-form'))) {
			submitform(task);
		}
	}
// -->
</script>

<form action="<?php echo JRoute::_('index.php?option=com_redirect'); ?>" method="post" name="adminForm" id="link-form" class="form-validate">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo empty($this->item->id) ? JText::_('Redir_New_Link') : JText::sprintf('Redir_Edit_Link', $this->item->id); ?></legend>

			<?php echo $this->form->getLabel('old_url'); ?>
			<?php echo $this->form->getInput('old_url'); ?>

			<?php echo $this->form->getLabel('new_url'); ?>
			<?php echo $this->form->getInput('new_url'); ?>

			<?php echo $this->form->getLabel('comment'); ?>
			<?php echo $this->form->getInput('comment'); ?>
		</fieldset>
	</div>

	<div class="width-40 fltrt">
		<fieldset class="adminform">
			<legend><?php echo JText::_('Details'); ?></legend>

				<?php echo $this->form->getLabel('published'); ?>
				<?php echo $this->form->getInput('published'); ?>

				<?php echo $this->form->getLabel('created_date'); ?>
				<?php echo $this->form->getInput('created_date'); ?>

				<?php echo $this->form->getLabel('updated_date'); ?>
				<?php echo $this->form->getInput('updated_date'); ?>
		</fieldset>
	</div>
	<div class="clr"></div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
