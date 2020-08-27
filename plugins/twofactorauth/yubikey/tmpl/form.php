<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Twofactorauth.yubikey.tmpl
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<div class="card mb-2">
	<div class="card-body">
		<?php echo Text::_('PLG_TWOFACTORAUTH_YUBIKEY_INTRO') ?>
	</div>
</div>

<?php if ($new_totp): ?>
<fieldset>
	<legend>
		<?php echo Text::_('PLG_TWOFACTORAUTH_YUBIKEY_STEP1_HEAD') ?>
	</legend>

	<p>
		<?php echo Text::_('PLG_TWOFACTORAUTH_YUBIKEY_STEP1_TEXT') ?>
	</p>

	<div class="control-group">
		<label class="control-label" for="yubikeysecuritycode">
			<?php echo Text::_('PLG_TWOFACTORAUTH_YUBIKEY_SECURITYCODE') ?>
		</label>
		<div class="controls">
			<input type="text" class="form-control" name="jform[twofactor][yubikey][securitycode]" id="yubikeysecuritycode" autocomplete="0">
		</div>
	</div>
</fieldset>
<?php else: ?>
<fieldset>
	<legend>
		<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_RESET_HEAD') ?>
	</legend>

	<p>
		<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_RESET_TEXT') ?>
	</p>
</fieldset>
<?php endif; ?>
