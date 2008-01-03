<?php // @version $Id: default.php  $
defined('_JEXEC') or die('Restricted access');
?>

<h1 class="componentheading"><?php echo JText::_('FORGOT_YOUR_PASSWORD'); ?></h1>

<form action="index.php?option=com_user&amp;task=requestreset" method="post" class="josForm form-validate">
	<p><?php echo JText::_('RESET_PASSWORD_REQUEST_DESCRIPTION'); ?></p>

	<label for="email" class="hasTip" title="<?php echo JText::_('RESET_PASSWORD_EMAIL_TIP_TITLE'); ?>::<?php echo JText::_('RESET_PASSWORD_EMAIL_TIP_TEXT'); ?>"><?php echo JText::_('Email Address'); ?>:</label>
	<input id="email" name="email" type="text" class="required validate-email" />

	<button type="submit" class="validate"><?php echo JText::_('Submit'); ?></button>
	<?php echo JHTML::_( 'form.token' ); ?>
</form>