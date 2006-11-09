<fieldset class="adminform">
	<legend><?php echo JText::_( 'Mail Settings' ); ?></legend>
	<table class="admintable" cellspacing="1">

		<tbody>
		<tr>
			<td width="185" class="key">
				<?php echo JText::_( 'Mailer' ); ?>
			</td>
			<td>
				<?php echo $lists['mailer']; ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'Mail From' ); ?>
			</td>
			<td>
				<input class="text_area" type="text" name="mailfrom" size="30" value="<?php echo $row->mailfrom; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'From Name' ); ?>
			</td>
			<td>
				<input class="text_area" type="text" name="fromname" size="30" value="<?php echo $row->fromname; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'Sendmail Path' ); ?>
			</td>
			<td>
				<input class="text_area" type="text" name="sendmail" size="30" value="<?php echo $row->sendmail; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'SMTP Auth' ); ?>
			</td>
			<td>
				<?php echo $lists['smtpauth']; ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'SMTP User' ); ?>
			</td>
			<td>
				<input class="text_area" type="text" name="smtpuser" size="30" value="<?php echo $row->smtpuser; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'SMTP Pass' ); ?>
			</td>
			<td>
				<input class="text_area" type="password" name="smtppass" size="30" value="<?php echo $row->smtppass; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'SMTP Host' ); ?>
			</td>
			<td>
				<input class="text_area" type="text" name="smtphost" size="30" value="<?php echo $row->smtphost; ?>" />
			</td>
		</tr>
		</tbody>
	</table>
</fieldset>
