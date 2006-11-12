<fieldset class="adminform">
	<legend><?php echo JText::_( 'Registration Settings' ); ?></legend>

	<table class="admintable" cellspacing="1">
		<tbody>
		<tr>
			<td width="150" class="key">
				<span class="editlinktip">
				<?php
				$tip = 'If yes, allows users to self-register';
				echo mosToolTip( $tip, '', 280, 'tooltip.png', 'Allow User Registration', '', 0 );
				?>
				</span>
			</td>
			<td>
				<?php echo $lists['allowUserRegistration']; ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<span class="editlinktip">
				<?php
				$tip = 'TIPNEWUSERTYPE';
				echo mosToolTip( $tip, '', 280, 'tooltip.png', 'New User Registration Type', '', 0 );
				?>
				</span>
			</td>
			<td>
				<?php echo $lists['new_usertype']; ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<span class="editlinktip">
				<?php
				$tip = 'TIPIFYESUSERMAILEDLINK';
				echo mosToolTip( $tip, '', 280, 'tooltip.png', 'Use New Account Activation', '', 0 );
				?>
				</span>
			</td>
			<td>
				<?php echo $lists['useractivation']; ?>
			</td>
		</tr>
		</tbody>
	</table>
</fieldset>