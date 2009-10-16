<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	mod_logged
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
?>

<form method="post" action="index.php?option=com_users">
	<table class="adminlist">
		<thead>
			<tr>
				<th>
					<?php echo '#' ?>
				</th>
				<th class="left">
					<?php echo JText::_('Name'); ?>
				</th>
				<th>
					<?php echo JText::_('Group'); ?>
				</th>
				<th>
					<?php echo JText::_('Client'); ?>
				</th>
				<th>
					<?php echo JText::_('Last Activity'); ?>
				</th>
				<th>
					<?php echo JText::_('Logout'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
	<?php
		$i		= 0;
		$now	= time();
		foreach ($rows as $row) :
			$auth = $user->authorize('core.manage', 'com_users');
			if ($auth) :
				$link 	= 'index.php?option=com_users&amp;task=edit&amp;cid[]='. $row->userid;
				$name 	= '<a href="'. $link .'" title="'. JText::_('Edit User') .'">'. $row->username .'</a>';
			else :
				$name 	= $row->username;
			endif;

			$clientInfo = &JApplicationHelper::getClientInfo($row->client_id);
			?>
			<tr>
				<td width="5%" class="center">
					<?php echo $pageNav->getRowOffset($i); ?>
				</td>
				<td>
					<?php echo $name;?>
				</td>
				<td>
					<?php echo $row->usertype;?>
				</td>
				<td class="center">
					<?php echo $clientInfo->name;?>
				</td>
				<td class="center">
					<?php echo JText::sprintf('activity hours', ($now - $row->time)/3600.0);?>
				</td>
				<td class="center">
				<?php if ($auth && $row->userid != $user->get('id')) : ?>
					<input type="image" src="images/publish_x.png" onclick="f=this.form;f.task.value='flogout';f.client.value=<?php echo (int) $row->client_id; ?>;f.cid_value.value=<?php echo (int) $row->userid ?>" />
				<?php endif; ?>
				</td>
			</tr>
			<?php
			$i++;
		endforeach;
		?>
		</tbody>
	</table>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="client" value="" />
	<input type="hidden" name="cid[]" id="cid_value" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
