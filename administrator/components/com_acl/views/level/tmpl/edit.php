<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access');

	JHTML::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
	JHTML::_('behavior.tooltip');
	JHTML::_('behavior.formvalidation');

	$state	= $this->get('State');
	$type	= strtoupper($state->get('type'));

	// Find the correct parent group for the filter list
	$acl = &JFactory::getACL();
	$parentId = $acl->get_group_id(-1, 'ROOT', 'AXO');
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
						<?php echo JText::_('ACL Level Name'); ?>
					</label>
				</td>
				<td>
					<input type="text" name="name" id="name" class="inputbox validate required" size="40" value="<?php echo $this->item->get('name'); ?>" />
				</td>
			</tr>
		</table>

	</fieldset>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="parent_id" value="<?php echo $parentId; ?>" />
	<input type="hidden" name="group_type" value="<?php echo $this->state->get('group_type');?>" />
	<input type="hidden" name="<?php echo JUtility::getToken();?>" value="1" />
</form>
