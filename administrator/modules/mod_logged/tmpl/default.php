<?php
/** $Id$ */
defined( '_JEXEC' ) or die( 'Restricted access' );
?>

<form method="post" action="index.php?option=com_users">
	<table class="adminlist">
		<thead>
			<tr>
				<td class="title">
					<strong><?php echo '#' ?></strong>
				</td>
				<td class="title">
					<strong><?php echo JText::_( 'Name' ); ?></strong>
				</td>
				<td class="title">
					<strong><?php echo JText::_( 'Group' ); ?></strong>
				</td>
				<td class="title">
					<strong><?php echo JText::_( 'Client' ); ?></strong>
				</td>
				<td class="title">
					<strong><?php echo JText::_( 'Last Activity' ); ?></strong>
				</td>
				<td class="title">
					<strong><?php echo JText::_( 'Logout' ); ?></strong>
				</td>
			</tr>
		</thead>
		<tbody>
	<?php
		$i		= 0;
		$now	= time();
		foreach ($rows as $row) :
			$auth = $user->authorize( 'com_users', 'manage' );
			if ($auth) :
				$link 	= 'index.php?option=com_users&amp;task=edit&amp;cid[]='. $row->userid;
				$name 	= '<a href="'. $link .'" title="'. JText::_( 'Edit User' ) .'">'. $row->username .'</a>';
			else :
				$name 	= $row->username;
			endif;

			$clientInfo =& JApplicationHelper::getClientInfo($row->client_id);
			?>
			<tr>
				<td width="5%">
					<?php echo $pageNav->getRowOffset( $i ); ?>
				</td>
				<td>
					<?php echo $name;?>
				</td>
				<td>
					<?php echo $row->usertype;?>
				</td>
				<td>
					<?php echo $clientInfo->name;?>
				</td>
				<td>
					<?php echo JText::sprintf( 'activity hours', ($now - $row->time)/3600.0 );?>
				</td>
				<td>
				<?php if ($auth && $user->get('gid') > 24 && $row->userid != $user->get('id')) : ?>
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
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
