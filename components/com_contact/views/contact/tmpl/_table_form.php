<?php if(isset($this->error)) : ?>
<tr>
	<p><?php echo $this->error; ?></p>
</tr>
<?php endif; ?>
<tr>
	<td colspan="2">
	<br />
	<?php echo $this->contact->params->get( 'email_description_text' ) ?>
	<br /><br />
	<form action="index.php" method="post" name="emailForm" target="_top" id="emailForm">
		<div class="contact_email<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
			<label for="contact_name">
				&nbsp;<?php echo JText::_( 'Enter your name' );?>:
			</label>
			<br />
			<input type="text" name="name" id="contact_name" size="30" class="inputbox" value="" />
			<br />
			<label for="contact_email">
				&nbsp;<?php echo JText::_( 'Email address' );?>:
			</label>
			<br />
			<input type="text" name="email" id="contact_email" size="30" class="inputbox" value="" />
			<br />
			<label for="contact_subject">
				&nbsp;<?php echo JText::_( 'Message subject' );?>:
			</label>
			<br />
			<input type="text" name="subject" id="contact_subject" size="30" class="inputbox" value="" />
			<br /><br />
			<label for="contact_text">
				&nbsp;<?php echo JText::_( 'Enter your message' );?>:
			</label>
			<br />
			<textarea cols="50" rows="10" name="text" id="body" class="inputbox"></textarea>
			<?php if ($this->contact->params->get( 'email_copy' )) : ?>
			<br />
				<input type="checkbox" name="email_copy" id="contact_email_copy" value="1"  />
				<label for="contact_email_copy">
					<?php echo JText::_( 'EMAIL_A_COPY' ); ?>
				</label>
			<?php endif; ?>
			<br />
			<br />
			<input type="button" name="send" value="<?php echo JText::_( 'Send' ); ?>" class="button" onclick="validate()" />
		</div>

	<input type="hidden" name="option" value="com_contact" />
	<input type="hidden" name="view" value="contact" />
	<input type="hidden" name="contact_id" value="<?php echo $this->contact->id; ?>" />
	<input type="hidden" name="task" value="sendmail" />
	<input type="hidden" name="Itemid" value="<?php echo $Itemid ?>" />
	<input type="hidden" name="<?php echo JUtility::spoofKey(); ?>" value="1" />
	</form>
	<br />
	</td>
</tr>