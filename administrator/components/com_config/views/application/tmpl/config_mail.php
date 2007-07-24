<fieldset class="adminform">
	<legend><?php echo JText::_( 'Mail Settings' ); ?></legend>
	<table class="admintable" cellspacing="1">

		<tbody>
		<tr>
			<td width="185" class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Mailer' ); ?>::<?php echo JText::_( 'TIPMAILER' ); ?>">
						<?php echo JText::_( 'Mailer' ); ?>
					</span>
			</td>
			<td>
				<?php echo $lists['mailer']; ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Mail From' ); ?>::<?php echo JText::_( 'TIPMAILFROM' ); ?>">
						<?php echo JText::_( 'Mail From' ); ?>
					</span>
			</td>
			<td>
				<input class="text_area" type="text" name="mailfrom" size="30" value="<?php echo $row->mailfrom; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'From Name' ); ?>::<?php echo JText::_( 'TIPFROMNAME' ); ?>">
						<?php echo JText::_( 'From Name' ); ?>
					</span>
			</td>
			<td>
				<input class="text_area" type="text" name="fromname" size="30" value="<?php echo $row->fromname; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'Sendmail Path' ); ?>::<?php echo JText::_( 'TIPSENDMAILPATH' ); ?>">
						<?php echo JText::_( 'Sendmail Path' ); ?>
					</span>
			</td>
			<td>
				<input class="text_area" type="text" name="sendmail" size="30" value="<?php echo $row->sendmail; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'SMTP Auth' ); ?>::<?php echo JText::_( 'TIPSMTPAUTH' ); ?>">
						<?php echo JText::_( 'SMTP Auth' ); ?>
					</span>
			</td>
			<td>
				<?php echo $lists['smtpauth']; ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'SMTP User' ); ?>::<?php echo JText::_( 'TIPSMTPUSER' ); ?>">
						<?php echo JText::_( 'SMTP User' ); ?>
					</span>
			</td>
			<td>
				<input class="text_area" type="text" name="smtpuser" size="30" value="<?php echo $row->smtpuser; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'SMTP Pass' ); ?>::<?php echo JText::_( 'TIPSMTPPASS' ); ?>">
						<?php echo JText::_( 'SMTP Pass' ); ?>
					</span>
			</td>
			<td>
				<input class="text_area" type="password" name="smtppass" size="30" value="<?php echo $row->smtppass; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_( 'SMTP Host' ); ?>::<?php echo JText::_( 'TIPSMTPHOST' ); ?>">
						<?php echo JText::_( 'SMTP Host' ); ?>
					</span>
			</td>
			<td>
				<input class="text_area" type="text" name="smtphost" size="30" value="<?php echo $row->smtphost; ?>" />
			</td>
		</tr>
		</tbody>
	</table>
</fieldset>
