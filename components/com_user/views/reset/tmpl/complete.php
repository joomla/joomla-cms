<?php defined('_JEXEC') or die; ?>

<div class="componentheading">
	<?php echo JText::_('Reset your Password'); ?>
</div>

<form action="index.php?option=com_user&amp;task=completereset" method="post" class="josForm form-validate">
	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="contentpane">
		<tr>
			<td colspan="2" height="40">
				<p><?php echo JText::_('RESET_PASSWORD_COMPLETE_DESCRIPTION'); ?></p>
			</td>
		</tr>
		<tr>
			<td height="40">
				<label for="password1" class="hasTip" title="<?php echo JText::_('RESET_PASSWORD_PASSWORD1_TIP_TITLE'); ?>::<?php echo JText::_('RESET_PASSWORD_PASSWORD1_TIP_TEXT'); ?>"><?php echo JText::_('Password'); ?>:</label>
			</td>
			<td>
				<input id="password1" name="password1" type="password" class="required validate-password" />
			</td>
		</tr>
		<tr>
			<td height="40">
				<label for="password2" class="hasTip" title="<?php echo JText::_('RESET_PASSWORD_PASSWORD2_TIP_TITLE'); ?>::<?php echo JText::_('RESET_PASSWORD_PASSWORD2_TIP_TEXT'); ?>"><?php echo JText::_('Verify Password'); ?>:</label>
			</td>
			<td>
				<input id="password2" name="password2" type="password" class="required validate-password" />
			</td>
		</tr>
	</table>

	<button type="submit" class="validate"><?php echo JText::_('Submit'); ?></button>
	<?php echo JHTML::_( 'form.token' ); ?>
</form>