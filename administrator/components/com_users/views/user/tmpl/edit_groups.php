<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access'); ?>
<?php
	// Find the correct parent group for the filter list
	$acl = &JFactory::getACL();
	$parentId = $acl->get_group_id( 'USERS' );
?>
<fieldset class="adminform">
	<legend><?php echo JText::_( 'Groups' ); ?></legend>
	@todo Limit by ACL
	<table class="admintable">
		<tr>
			<td valign="top" class="key">
				<label for="gid">
					<?php echo JText::_( 'Group' ); ?>
				</label>
			</td>
			<td>
				<select name="gid" class="inputbox" size="10">
					<?php echo JHtml::_( 'user.groups', $this->item->get( 'gid' ), $parentId );  ?>
				</select>
			</td>
		</tr>
	</table>
</fieldset>