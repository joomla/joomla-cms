<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access');

	JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
	JHtml::_('behavior.tooltip');
	JHtml::_('behavior.formvalidation');

	$state	= $this->get('State');
	$type	= strtoupper($state->get('type'));

	// Find the correct parent group for the filter list
	$acl = &JFactory::getACL();
	$parentId = $acl->get_group_id('USERS');
?>
<script language="javascript" type="text/javascript">
<!--
	function submitbutton(task)
	{
		var form = document.adminForm;
		if (task == 'group.cancel' || document.formvalidator.isValid(document.adminForm)) {
			submitform(task);
		}
	}
-->
</script>
<form action="<?php echo JRoute::_('index.php?option=com_acl'); ?>" method="post" name="adminForm" class="form-validate">
	<fieldset>
		<?php if ($id = $this->item->get('id')) : ?>
		<legend><?php echo JText::sprintf('Record #%d', $id); ?></legend>
		<?php endif; ?>
		<table class="admintable">
			<tr>
				<td width="150" class="key">
					<label for="name">
						<?php echo JText::_('ACL Parent Group'); ?>
					</label>
				</td>
				<td>
					<select name="parent_id" class="inputbox" size="1">
						<?php echo JHtml::_('acladmin.groups', $this->item->get('parent_id'), $parentId);  ?>
					</select>
				</td>
			</tr>
			<tr>
				<td width="150" class="key">
					<label for="name">
						<?php echo JText::_('ACL Group Name'); ?>
					</label>
				</td>
				<td>
					<input type="text" name="name" id="name" class="inputbox validate required" size="40" value="<?php echo $this->item->get('name'); ?>" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="username">
						<?php echo JText::_('ACL Group Alias'); ?>
					</label>
				</td>
				<td>
					<input type="text" name="value" id="value" class="inputbox validate required" size="40" value="<?php echo $this->item->get('value'); ?>" />
				</td>
			</tr>
		</table>

	</fieldset>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="group_type" value="<?php echo $this->state->get('group_type');?>" />
	<input type="hidden" name="<?php echo JUtility::getToken();?>" value="1" />
</form>

<script type="text/javascript">
// Attach the onblur event to auto-create the alias
e = document.getElementById('name');
e.onblur = function(){
	title = document.getElementById('name');
	alias = document.getElementById('value');
	if (alias.value=='') {
		alias.value = title.value.replace(/[\s\-]+/g,'-').replace(/&/g,'and').replace(/[^A-Z0-9\-\_]/ig,'').toLowerCase();
	}
}
</script>
