<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
?>
<form action="<?php echo JRoute::_('index.php?option=com_messages'); ?>" method="post" name="adminForm" id="message-form" class="form-validate form-horizontal">
	<fieldset class="adminform">
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('user_id_to'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('user_id_to'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('subject'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('subject'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('message'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('message'); ?>
			</div>
		</div>
	</fieldset>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
