<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Twofactorauth.totp.tmpl
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<input type="hidden" name="jform[twofactor][totp][key]" value="<?php echo $secret ?>">

<div class="card mb-2">
	<div class="card-body">
		<?php echo JText::_('PLG_TWOFACTORAUTH_TOTP_INTRO') ?>
	</div>
</div>

<fieldset>
	<legend>
		<?php echo JText::_('PLG_TWOFACTORAUTH_TOTP_STEP1_HEAD') ?>
	</legend>
	<p>
		<?php echo JText::_('PLG_TWOFACTORAUTH_TOTP_STEP1_TEXT') ?>
	</p>
	<ul>
		<li>
			<a href="<?php echo JText::_('PLG_TWOFACTORAUTH_TOTP_STEP1_ITEM1_LINK') ?>" target="_blank" rel="noopener noreferrer">
				<?php echo JText::_('PLG_TWOFACTORAUTH_TOTP_STEP1_ITEM1') ?>
			</a>
		</li>
		<li>
			<a href="<?php echo JText::_('PLG_TWOFACTORAUTH_TOTP_STEP1_ITEM2_LINK') ?>" target="_blank" rel="noopener noreferrer">
				<?php echo JText::_('PLG_TWOFACTORAUTH_TOTP_STEP1_ITEM2') ?>
			</a>
		</li>
	</ul>
	<joomla-alert type="warning"><?php echo JText::_('PLG_TWOFACTORAUTH_TOTP_STEP1_WARN'); ?></joomla-alert>
</fieldset>

<fieldset>
	<legend>
		<?php echo JText::_('PLG_TWOFACTORAUTH_TOTP_STEP2_HEAD') ?>
	</legend>

	<div class="col-md-6">
		<p>
			<?php echo JText::_('PLG_TWOFACTORAUTH_TOTP_STEP2_TEXT') ?>
		</p>
		<table class="table table-striped">
			<tr>
				<td>
					<?php echo JText::_('PLG_TWOFACTORAUTH_TOTP_STEP2_ACCOUNT') ?>
				</td>
				<td>
					<?php echo $username ?>@<?php echo $hostname ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('PLG_TWOFACTORAUTH_TOTP_STEP2_KEY') ?>
				</td>
				<td>
					<?php echo $secret ?>
				</td>
			</tr>
		</table>
	</div>

	<div class="col-md-6">
		<p>
			<?php echo JText::_('PLG_TWOFACTORAUTH_TOTP_STEP2_ALTTEXT') ?>
			<br>
			<img src="<?php echo $url ?>" style="float: none;">
		</p>
	</div>

	<joomla-alert type="info"><?php echo JText::_('PLG_TWOFACTORAUTH_TOTP_STEP2_RESET'); ?></joomla-alert>
</fieldset>

<?php if ($new_totp): ?>
<fieldset>
	<legend>
		<?php echo JText::_('PLG_TWOFACTORAUTH_TOTP_STEP3_HEAD') ?>
	</legend>
	<p>
		<?php echo JText::_('PLG_TWOFACTORAUTH_TOTP_STEP3_TEXT') ?>
	</p>
	<div class="control-group">
		<label class="control-label" for="totpsecuritycode">
			<?php echo JText::_('PLG_TWOFACTORAUTH_TOTP_STEP3_SECURITYCODE') ?>
		</label>
		<div class="controls">
			<input type="text" class="form-control" name="jform[twofactor][totp][securitycode]" id="totpsecuritycode" autocomplete="0">
		</div>
	</div>
</fieldset>
<?php endif; ?>
