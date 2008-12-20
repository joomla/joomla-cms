<?php /** $Id$ */ defined('_JEXEC') or die(); ?>

<table width="100%">
	<tbody>
		<tr valign="top">
			<td valign="top" width="33%">
				<fieldset>
					<legend><?php echo JText::_('ACL Apply User Groups');?></legend>
					<?php echo JHTML::_('acl.usergroups', $this->aroGroups, $this->item->references->getAroGroups()); ?>
				</fieldset>
			</td>
			<td valign="top" width="33%">
				<fieldset>
					<legend class="hasTip" title="Permissions::Select the permissions that this group will be allowed, or not allowed to do.">
					<?php echo JText::_('ACL Apply Permissions') ?>
					</legend>
					<?php echo JHTML::_('acl.actions', $this->acos, $this->item->references->getAcos()); ?>
				</fieldset>
			</td>
			<td valign="top" width="33%">
				<fieldset>
					<legend class="hasTip" title="Item Groups::These are the item groups that are associated with the permission">
					<?php echo JText::_('ACL Apply to Access Groups') ?>
					</legend>
					<?php echo JHTML::_('acl.assetgroups', $this->axoGroups, $this->item->references->getAxoGroups()); ?>
				</fieldset>
			</td>
	</tbody>
</table>
