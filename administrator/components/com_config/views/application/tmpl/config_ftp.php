<fieldset class="adminform">
	<legend><?php echo JText::_( 'FTP Settings' ); ?></legend>
	<table class="admintable" cellspacing="1">

		<tbody>
		<tr>
			<td width="185" class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Enable FTP' ); ?>::<?php echo JText::_( 'TIPENABLEFTP' ); ?>">
						<?php echo JText::_( 'Enable FTP' ); ?>
					</span>
			</td>
			<td>
					<?php echo $lists['enable_ftp']; ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'FTP Host' ); ?>::<?php echo JText::_( 'TIPFTPHOST' ); ?>">
						<?php echo JText::_( 'FTP Host' ); ?>
					</span>
			</td>
			<td>
				<input class="text_area" type="text" name="ftp_host" size="25" value="<?php echo $row->ftp_host; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'FTP Port' ); ?>::<?php echo JText::_( 'TIPFTPPORT' ); ?>">
						<?php echo JText::_( 'FTP Port' ); ?>
					</span>
			</td>
			<td>
				<input class="text_area" type="text" name="ftp_port" size="25" value="<?php echo $row->ftp_port; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'FTP Username' ); ?>::<?php echo JText::_( 'TIPFTPUSERNAME' ); ?>">
						<?php echo JText::_( 'FTP Username' ); ?>
					</span>
			</td>
			<td>
				<input class="text_area" type="text" name="ftp_user" size="25" value="<?php echo $row->ftp_user; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'FTP Password' ); ?>::<?php echo JText::_( 'TIPFTPPASSWORD' ); ?>">
						<?php echo JText::_( 'FTP Password' ); ?>
					</span>
			</td>
			<td>
				<input class="text_area" type="password" name="ftp_pass" size="25" value="<?php echo $row->ftp_pass; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'FTP Root' ); ?>::<?php echo JText::_( 'TIPFTPROOT' ); ?>">
						<?php echo JText::_( 'FTP Root' ); ?>
					</span>
			</td>
			<td>
				<input class="text_area" type="text" name="ftp_root" size="50" value="<?php echo $row->ftp_root; ?>" />
			</td>
		</tr>
		</tbody>
	</table>
</fieldset>
