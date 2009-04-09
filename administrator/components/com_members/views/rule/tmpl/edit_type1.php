<?php /** $Id: edit_type1.php 11362 2008-12-22 12:19:58Z eddieajau $ */ defined('_JEXEC') or die(); ?>

<table width="100%">
	<tbody>
		<tr valign="top">
			<td valign="top" width="50%">
				<fieldset>
					<legend><?php echo JText::_('ACL Apply User Groups');?></legend>
					<?php echo JHTML::_('acladmin.usergroups', $this->aroGroups, $this->item->references->getAroGroups()); ?>
				</fieldset>
			</td>
			<td valign="top" width="50%">
				<fieldset>
					<legend class="hasTip" title="Permissions::Select the permissions that this group will be allowed, or not allowed to do.">
					<?php echo JText::_('ACL Apply Permissions') ?>
					</legend>
					<?php echo JHTML::_('acladmin.actions', $this->acos, $this->item->references->getAcos(), true); ?>
				</fieldset>
			</td>
	</tbody>
</table>
