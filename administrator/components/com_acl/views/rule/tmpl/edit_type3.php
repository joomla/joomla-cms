<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access'); ?>

		<table width="100%">
			<tbody>
				<tr valign="top">
					<td valign="top" width="33%">
						<fieldset>
							<legend><?php echo JText::_('ACL Apply User Groups');?></legend>
							<?php echo $this->loadTemplate('arogroups'); ?>
						</fieldset>
					</td>
					<td valign="top" width="33%">
						<fieldset>
							<legend class="hasTip" title="Permissions::Select the permissions that this group will be allowed, or not allowed to do.">
							<?php echo JText::_('ACL Apply Permissions') ?>
							</legend>
							<?php echo $this->loadTemplate('acos'); ?>
						</fieldset>
					</td>
					<td valign="top" width="33%">
						<fieldset>
							<legend class="hasTip" title="Item Groups::These are the item groups that are associated with the permission">
							<?php echo JText::_('ACL Apply to Access Groups') ?>
							</legend>
							<?php echo $this->loadTemplate('axogroups'); ?>
						</fieldset>
					</td>
			</tbody>
		</table>
