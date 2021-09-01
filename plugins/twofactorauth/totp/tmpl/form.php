<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Twofactorauth.totp.tmpl
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
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
		var totpQrCodeElement = document.getElementById('totp-qrcode');

		// There's no QR Code element on the view profile page so ensure we don't get any errors
		if (totpQrCodeElement) {
			var qr = qrcode(0, 'H');
			qr.addData('" . $url . "');
			qr.make();
	
			totpQrCodeElement.innerHTML = qr.createImgTag(4);
		}
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
<hr>

<h3>
	<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_STEP1_HEAD') ?>
</h3>

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
	<span class="icon-exclamation-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
	<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_STEP1_WARN'); ?>
</div>
<hr>

<h3>
	<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_STEP2_HEAD') ?>
</h3>

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
	<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
	<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_STEP2_RESET'); ?>
</div>

<?php if ($new_totp): ?>
<hr>
<h3>
	<?php echo Text::_('PLG_TWOFACTORAUTH_TOTP_STEP3_HEAD') ?>
</h3>

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
<?php endif; ?>
