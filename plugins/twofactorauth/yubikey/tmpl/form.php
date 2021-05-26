<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Twofactorauth.yubikey.tmpl
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
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
<hr>

<?php if ($new_totp): ?>
<h3>
	<?php echo Text::_('PLG_TWOFACTORAUTH_YUBIKEY_STEP1_HEAD') ?>
</h3>

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
<?php else: ?>
<h3>
	<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_RESET_HEAD') ?>
</h3>

<p>
	<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_RESET_TEXT') ?>
</p>
<?php endif; ?>
