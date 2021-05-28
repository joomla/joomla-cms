<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Twofactorauth.yubikey.tmpl
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="well">
	<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEY_INTRO') ?>
</div>

<?php if ($new_totp): ?>
<fieldset>
	<legend>
		<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEY_STEP1_HEAD') ?>
	</legend>

	<p>
		<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEY_STEP1_TEXT') ?>
	</p>

	<div class="control-group">
		<label class="control-label" for="yubikeysecuritycode">
			<?php echo JText::_('PLG_TWOFACTORAUTH_YUBIKEY_SECURITYCODE') ?>
		</label>
		<div class="controls">
			<input type="text" class="input-medium" name="jform[twofactor][yubikey][securitycode]" id="yubikeysecuritycode" autocomplete="0">
		</div>
	</div>
</fieldset>
<?php else: ?>
<fieldset>
	<legend>
		<?php echo JText::_('PLG_TWOFACTORAUTH_TOTP_RESET_HEAD') ?>
	</legend>

	<p>
		<?php echo JText::_('PLG_TWOFACTORAUTH_TOTP_RESET_TEXT') ?>
	</p>
</fieldset>
<?php endif; ?>
