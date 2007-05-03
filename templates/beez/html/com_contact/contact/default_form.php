<?php
defined('_JEXEC') or die('Restricted access');
?>

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
<?php
if(isset($this->error))
{

        echo '<p class="error">'. $this->error.'</p>';
}
echo '<form action="'.JRoute::_('index.php').'"  class="form-validate" method="post" name="emailForm" target="_top" id="emailForm">';
echo '<div class="contact_email'.$this->params->get( 'pageclass_sfx' ).'">';
echo '<label for="contact_name">';
echo JText::_( 'Enter your name' ).'*:</label>';
echo '<input type="text" name="name" id="contact_name" size="30" class="inputbox" value="" />';
echo '</div>';
echo '<div class="contact_email'. $this->params->get( 'pageclass_sfx' ).'"><label id="contact_emailmsg" for="contact_email">';
echo JText::_( 'Email address' ).'*:</label>';
echo '<input type="text" id="contact_email" name="email" size="30" value="" class="inputbox validate required email contact_emailmsg" maxlength="100" />';
echo '</div>';
echo '<div class="contact_email'. $this->params->get( 'pageclass_sfx' ).'"><label for="contact_subject">';
echo JText::_( 'Message subject' ).'*:</label>';
echo '<input type="text" name="subject" id="contact_subject" size="30" class="inputbox" value="" />';
echo '</div>';
echo '<div class="contact_email'.$this->params->get( 'pageclass_sfx' ).'"><label id="contact_textmsg" for="contact_text" class="textarea">';
echo JText::_( 'Enter your message' ).'*:</label>';
echo '<textarea name="text" id="contact_text" class="inputbox validate required none contact_textmsg"></textarea>';
echo '</div>';
if ($this->contact->params->get( 'show_email_copy' ))
{
	echo '<div class="contact_email_checkbox'.$this->params->get( 'pageclass_sfx' ).'">';
	echo '<input type="checkbox" name="email_copy" id="contact_email_copy" value="1"  />';
	echo '<label for="contact_email_copy" class="copy">';
	echo JText::_( 'EMAIL_A_COPY' );
	echo '</label>';
	echo '</div>';
}
echo '<button class="button validate" type="submit">'.JText::_('Send').'</button>';
echo '<input type="hidden" name="view" value="contact" />';
echo '<input type="hidden" name="id" value="'.$this->contact->id.'" />';
echo '<input type="hidden" name="task" value="sendmail" />';
echo '<input type="hidden" name="'.JUtility::getToken().'" value="1" />';
echo '</form>';
?>