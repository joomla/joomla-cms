<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Twofactorauth.totp.tmpl
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

Factory::getDocument()->getWebAssetManager()->usePreset('qrcode');

$js = "
(function(document)
{
	document.addEventListener('DOMContentLoaded', function()
	{
		var qr = qrcode(0, 'H');
		qr.addData('" . $url . "');
		qr.make();

		document.getElementById('totp-qrcode').innerHTML = qr.createImgTag(4);
	});
})(document);
";

Factory::getDocument()->addScriptDeclaration($js);
?>
<input type="hidden" name="jform[twofactor][totp][key]" value="<?php echo $secret ?>">

<div class="card mb-2">
	<div class="card-body">
		<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_INTRO') ?>
	</div>
</div>

<fieldset>
	<legend>
		<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_STEP1_HEAD') ?>
	</legend>
	<p>
		<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_STEP1_TEXT') ?>
	</p>
	<ul>
		<li>
			<a href="<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_STEP1_ITEM1_LINK') ?>" target="_blank" rel="noopener noreferrer">
				<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_STEP1_ITEM1') ?>
			</a>
		</li>
		<li>
			<a href="<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_STEP1_ITEM2_LINK') ?>" target="_blank" rel="noopener noreferrer">
				<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_STEP1_ITEM2') ?>
			</a>
		</li>
	</ul>
	<div class="alert alert-warning">
		<span class="fas fa-exclamation-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('WARNING'); ?></span>
		<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_STEP1_WARN'); ?>
	</div>
</fieldset>

<fieldset>
	<legend>
		<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_STEP2_HEAD') ?>
	</legend>

	<div class="col-md-6">
		<p>
			<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_STEP2_TEXT') ?>
		</p>
		<table class="table">
			<tr>
				<td>
					<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_STEP2_ACCOUNT') ?>
				</td>
				<td>
					<?php echo $sitename ?>/<?php echo $username ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_STEP2_KEY') ?>
				</td>
				<td>
					<?php echo $secret ?>
				</td>
			</tr>
		</table>
	</div>

	<div class="col-md-6">
		<p>
			<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_STEP2_ALTTEXT') ?>
			<br>
			<div id="totp-qrcode"></div>
		</p>
	</div>

	<div class="alert alert-info">
		<span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
		<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_STEP2_RESET'); ?>
	</div>
</fieldset>

<?php if ($new_totp): ?>
<fieldset>
	<legend>
		<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_STEP3_HEAD') ?>
	</legend>
	<p>
		<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_STEP3_TEXT') ?>
	</p>
	<div class="control-group">
		<label class="control-label" for="totpsecuritycode">
			<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_STEP3_SECURITYCODE') ?>
		</label>
		<div class="controls">
			<input type="text" class="form-control" name="jform[twofactor][totp][securitycode]" id="totpsecuritycode" autocomplete="0">
		</div>
	</div>
</fieldset>
<?php endif; ?>
