<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
?>

<form action="<?php echo JRoute::_('index.php?option=com_users&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="group-form">
	<fieldset>
		<legend><?php echo JText::_('COM_USERS_USERGROUP_DETAILS'); ?></legend>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('title'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('title'); ?>
			</div>
		</div>
		<div class="control-group">
			<?php $parent_id = $this->form->getField('parent_id'); ?>
			<?php if (!$parent_id->hidden) : ?>
				<div class="control-label">
					<?php echo $parent_id->label; ?>
				</div>
			<?php endif; ?>
			<div class="controls">
				<?php echo $parent_id->input; ?>
			</div>
		</div>
	</fieldset>
	<input type="hidden" name="task" value="">
	<?php echo JHtml::_('form.token'); ?>
</form>
