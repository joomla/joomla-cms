<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<script type="text/javascript">
<!--
	function validateForm( frm ) {
		var valid = document.formvalidator.isValid(frm);
		if (valid == false) {
			// do field validation
			if (frm.email.invalid) {
				alert( "<?php echo JText::_( 'Please enter a valid e-mail address.', true );?>" );
			} else if (frm.text.invalid) {
				alert( "<?php echo JText::_( 'CONTACT_FORM_NC', true ); ?>" );
			}
			return false;
		} else {
			frm.submit();
		}
	}
// -->
</script>
<?php if(isset($this->error)) : ?>
<tr>
	<p><?php echo $this->error; ?></p>
</tr>
<?php endif; ?>
<tr>
	<td colspan="2">
	<br /><br />
	<form action="index.php" method="post" name="emailForm" target="_top" id="emailForm" class="form-validate">
		<div class="contact_email<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
			<label for="contact_name">
				&nbsp;<?php echo JText::_( 'Enter your name' );?>:
			</label>
			<br />
			<input type="text" name="name" id="contact_name" size="30" class="inputbox" value="" />
			<br />
			<label id="contact_emailmsg" for="contact_email">
				&nbsp;<?php echo JText::_( 'Email address' );?>:
			</label>
			<br />
			<input type="text" id="contact_email" name="email" size="30" value="" class="inputbox required validate-email" maxlength="100" />
			<br />
			<label for="contact_subject">
				&nbsp;<?php echo JText::_( 'Message subject' );?>:
			</label>
			<br />
			<input type="text" name="subject" id="contact_subject" size="30" class="inputbox" value="" />
			<br /><br />
			<label id="contact_textmsg" for="contact_text">
				&nbsp;<?php echo JText::_( 'Enter your message' );?>:
			</label>
			<br />
			<textarea cols="50" rows="10" name="text" id="contact_text" class="inputbox required"></textarea>
			<?php if ($this->contact->params->get( 'show_email_copy' )) : ?>
			<br />
				<input type="checkbox" name="email_copy" id="contact_email_copy" value="1"  />
				<label for="contact_email_copy">
					<?php echo JText::_( 'EMAIL_A_COPY' ); ?>
				</label>
			<?php endif; ?>
			<br />
			<br />
			<button class="button validate" type="submit"><?php echo JText::_('Send'); ?></button>
		</div>

	<input type="hidden" name="option" value="com_contact" />
	<input type="hidden" name="view" value="contact" />
	<input type="hidden" name="id" value="<?php echo $this->contact->id; ?>" />
	<input type="hidden" name="task" value="submit" />
	<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
	</form>
	<br />
	</td>
</tr>