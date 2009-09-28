<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

// Load the default stylesheet.
JHtml::stylesheet('default.css', 'administrator/components/com_users/media/css/');
?>

<script type="text/javascript">
<!--
	function submitbutton(task)
	{
		if (task == 'group.cancel' || document.formvalidator.isValid($('group-form'))) {
			submitform(task);
		}
	}
// -->
</script>

<form action="<?php JRoute::_('index.php?option=com_users'); ?>" method="post" name="adminForm" id="group-form" class="form-validate">
	<div class="width-100">
		<fieldset>
			<legend><?php echo JText::_('Users_Usergroup_Details');?></legend>
			<?php echo $this->form->getLabel('title'); ?>
			<?php echo $this->form->getInput('title'); ?>
				
			<?php echo $this->form->getLabel('parent_id'); ?>
			<?php echo $this->form->getInput('parent_id'); ?>
		</fieldset>
	</div>
	
	<div class="width-50">
		<fieldset id="user-groups">
			<legend><?php echo JText::_('Users_Actions_Available');?></legend>
			<?php echo JHtml::_('access.actions', 'jform[actions]', $this->item->actions); ?>
		</fieldset>
	</div>
	
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<div class="clr"></div>
