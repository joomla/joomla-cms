<?php /** $Id$ */ defined( '_JEXEC' ) or die(); ?>

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
		if ($user->authorize( 'com_users', 'manage' )) :
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
			<?php if ( $user->authorize( 'com_users', 'manage' ) && $user->get('gid') > 24 && $row->userid != $user->get('id')) : ?>
				<a href="index.php?option=com_users&amp;task=logout&amp;cid[]=<?php echo $row->userid ?>&amp;client=<?php echo $row->client_id; ?>">
					<img src="images/publish_x.png" width="16" height="16" border="0" alt="<?php echo JText::_( 'Logout' ); ?>" title="<?php echo JText::_( 'Force Logout User' ); ?>" /></a>
			<?php endif; ?>
			</td>
		</tr>
		<?php
		$i++;
	endforeach;
	?>
	</tbody>
</table>
<input type="hidden" name="option" value="com_admin" />
