<?php // @version $Id$
defined('_JEXEC') or die('Restricted access');
?>

<script type="text/javascript">
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
</script>

<form action="<?php echo JRoute::_('index.php'); ?>" class="form-validate" method="post" name="emailForm" id="emailForm">
	<div class="contact_email<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
		<label for="contact_name">
		<?php echo JText::_( 'Enter your name' ); ?>:</label>
		<input type="text" name="name" id="contact_name" size="30" class="inputbox" value="" />
	</div>
	<div class="contact_email<?php echo  $this->params->get( 'pageclass_sfx' ); ?>"><label id="contact_emailmsg" for="contact_email">
		<?php echo JText::_( 'Email address' ); ?>*:</label>
		<input type="text" id="contact_email" name="email" size="30" value="" class="inputbox required validate-email" maxlength="100" />
	</div>
	<div class="contact_email<?php echo  $this->params->get( 'pageclass_sfx' ); ?>"><label for="contact_subject">
		<?php echo JText::_( 'Message subject' ); ?>:</label>
		<input type="text" name="subject" id="contact_subject" size="30" class="inputbox" value="" />
	</div>
		<div class="contact_email<?php echo $this->params->get( 'pageclass_sfx' ); ?>"><label id="contact_textmsg" for="contact_text" class="textarea">
		<?php echo JText::_( 'Enter your message' ); ?>*:</label>
		<textarea name="text" id="contact_text" class="inputbox required"></textarea>
	</div>
	<?php if ($this->contact->params->get( 'show_email_copy' )): ?>
	<div class="contact_email_checkbox<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<input type="checkbox" name="email_copy" id="contact_email_copy" value="1"  />
	<label for="contact_email_copy" class="copy">
	<?php echo JText::_( 'EMAIL_A_COPY' ); ?>
	</label>
	</div>
	<?php endif; ?>
	<button class="button validate" type="submit"><?php echo JText::_('Send'); ?></button>
	<input type="hidden" name="view" value="contact" />
	<input type="hidden" name="id" value="<?php echo $this->contact->id; ?>" />
	<input type="hidden" name="task" value="submit" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>