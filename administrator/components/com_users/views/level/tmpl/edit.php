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

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(task)
	{
		if (task == 'level.cancel' || document.formvalidator.isValid(document.getElementById('level-form')))
		{
			Joomla.submitform(task, document.getElementById('level-form'));
		}
	};
");
?>

<form action="<?php echo JRoute::_('index.php?option=com_users&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="level-form">
	<fieldset>
		<legend><?php echo JText::_('COM_USERS_LEVEL_DETAILS'); ?></legend>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('title'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('title'); ?>
			</div>
		</div>
	</fieldset>

	<fieldset>
		<legend><?php echo JText::_('COM_USERS_USER_GROUPS_HAVING_ACCESS'); ?></legend>
		<?php echo JHtml::_('access.usergroups', 'jform[rules]', $this->item->rules); ?>
	</fieldset>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
