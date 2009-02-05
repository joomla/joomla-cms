<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_members
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Invalid Request.');

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

// Load the default stylesheet.
JHtml::stylesheet('default.css', 'administrator/components/com_members/media/css/');
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

<form action="<?php JRoute::_('index.php?option=com_members'); ?>" method="post" name="adminForm" id="group-form">
	<fieldset style="width:45%;float:left">
		<legend><?php echo JText::_('Members_Usergroup_Details');?></legend>
		<ol>
			<li>
				<?php echo $this->form->getLabel('parent_id'); ?><br />
				<?php echo $this->form->getInput('parent_id'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('title'); ?><br />
				<?php echo $this->form->getInput('title'); ?>
			</li>
		</ol>
	</fieldset>

	<fieldset id="member-groups">
		<legend><?php echo JText::_('Members_Actions_Available');?></legend>
		@TODO Grey out inherited actions
		<?php echo JHtml::_('access.actions', 'jform[actions]', $this->item->actions ); ?>
	</fieldset>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<div class="clr"></div>
