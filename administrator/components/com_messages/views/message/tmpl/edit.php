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
	function submitbutton(task) {
		if (task == 'config.cancel' || document.formvalidator.isValid(document.id('config-form'))) {
			submitform(task);
		}
	}
// -->
</script>
<form action="<?php echo JRoute::_('index.php?option=com_messages'); ?>" method="post" name="adminForm" id="newsfeed-form" class="form-validate">
	<div class="width-100">
		<fieldset class="adminform">

			<?php echo $this->form->getLabel('user_id_to'); ?>
			<?php echo $this->form->getInput('user_id_to'); ?>

			<?php echo $this->form->getLabel('subject'); ?>
			<?php echo $this->form->getInput('subject'); ?>

			<?php echo $this->form->getLabel('message'); ?>
			<?php echo $this->form->getInput('message'); ?>

		</fieldset>
	</div>
	<input type="hidden" name="task" value="">
	<?php echo JHtml::_('form.token'); ?>
</form>