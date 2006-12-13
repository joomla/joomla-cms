<fieldset class="adminform">
	<legend><?php echo JText::_( 'System Settings' ); ?></legend>
	<table class="admintable" cellspacing="1">

		<tbody>
			<tr>
				<td width="185" class="key">
					<?php echo JText::_( 'Secret Word' ); ?>
				</td>
				<td>
					<strong><?php echo $row->secret; ?></strong>
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="editlinktip">
					<?php
					$tip = 'TIPAUTOLOGOUTTIMEOF';
					echo mosToolTip( $tip, '', 280, 'tooltip.png', 'Login Session Lifetime', '', 0 );
					?>
					</span>
				</td>
				<td>
					<input class="text_area" type="text" name="lifetime" size="10" value="<?php echo $row->lifetime; ?>" />
					&nbsp;<?php echo JText::_('minutes'); ?>&nbsp;
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo JText::_( 'Enable Legacy Mode' ); ?>
				</td>
				<td>
					<?php echo $lists['legacy']; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo JText::_( 'Enable XML-RPC' ); ?>
				</td>
				<td>
					<?php echo $lists['xmlrpc_server']; ?>
				</td>
			</tr>
			<tr>
			<td class="key">
				<?php echo JText::_( 'Help Server' ); ?>
			</td>
			<td>
				<?php echo $lists['helpsites']; ?>
				<button onclick="submitbutton('refreshhelp')"><?php echo JText::_( 'Refresh' ); ?></button>
			</td>
		</tr>
		</tbody>
	</table>
</fieldset>
