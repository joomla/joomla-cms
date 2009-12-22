<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_messages
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
		if (task == 'config.cancel' || document.formvalidator.isValid(document.id('config-form'))) {
			submitform(task);
		}
	}
// -->
</script>
<form action="<?php echo JRoute::_('index.php?option=com_messages'); ?>" method="post" name="adminForm" id="newsfeed-form" class="form-validate">
	<fieldset>
		<div class="fltrt">
			<button type="button" onclick="Joomla.submitform('config.save', this.form);window.top.setTimeout('window.parent.SqueezeBox.close()', 1400);">
				<?php echo JText::_('Save');?></button>
			<button type="button" onclick="window.parent.SqueezeBox.close();">
				<?php echo JText::_('Cancel');?></button>
		</div>
		<div class="configuration" >
			<?php echo JText::_('Messages_My_Settings') ?>
		</div>
	</fieldset>

	<fieldset class="adminform">

		<?php echo $this->form->getLabel('lock'); ?>
		<?php echo $this->form->getInput('lock'); ?>

		<?php echo $this->form->getLabel('mail_on_new'); ?>
		<?php echo $this->form->getInput('mail_on_new'); ?>

		<?php echo $this->form->getLabel('auto_purge'); ?>
		<?php echo $this->form->getInput('auto_purge'); ?>

	</fieldset>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
