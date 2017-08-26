<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.core');
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('script', 'system/fields/passwordview.min.js', array('version' => 'auto', 'relative' => true));

JText::script('JSHOW');
JText::script('JHIDE');

$spacing = 0;

// Load chosen if we have language selector, ie, more than one administrator language installed and enabled.
if ($langs)
{
	$spacing += 33;
}

if (count($twofactormethods) > 1)
{
	$spacing += 33;
}

if ($spacing > 0)
{
	$marginTop = 240 + $spacing;

	JFactory::getDocument()->addStyleDeclaration('
		.view-login .container {
			margin-top: -' . $marginTop . 'px;
		}
	');
}
?>
<form class="login-initial form-validate" action="<?php echo JRoute::_('index.php', true); ?>" method="post" id="form-login">
	<fieldset>

		<div class="form-group">
			<label for="mod-login-username"><?php echo JText::_('JGLOBAL_USERNAME'); ?></label>
			<input
				name="username"
				id="mod-login-username"
				type="text"
				class="form-control input-full"
				required="true"
				autofocus
			>
		</div>

		<div class="form-group">
			<label for="mod-login-password"><?php echo JText::_('JGLOBAL_PASSWORD'); ?></label>
			<div class="input-group">
				<input
					name="passwd"
					id="mod-login-password"
					type="password"
					class="form-control input-full"
					required="true"
				>
				<span class="input-group-addon">
					<span class="fa fa-eye" aria-hidden="true"></span>
					<span class="sr-only"><?php echo JText::_('JSHOW'); ?></span>
				</span>
			</div>
		</div>

		<?php if (count($twofactormethods) > 1): ?>
			<label for="mod-login-secretkey"><?php echo JText::_('JGLOBAL_SECRETKEY'); ?></label>
			<div class="form-group">
				<input
					name="secretkey"
					autocomplete="off"
					id="mod-login-secretkey"
					type="text"
					class="form-control input-full"
				>
			</div>
		<?php endif; ?>

		<?php if (!empty($langs)) : ?>
			<div class="form-group">
				<label for="lang" class="sr-only"><?php echo JText::_('JDEFAULTLANGUAGE'); ?></label>
				<?php echo $langs; ?>
			</div>
		<?php endif; ?>

		<div class="form-group">
			<button class="btn btn-success btn-block btn-lg" id="btn-login-submit">
				<span class="fa fa-lock icon-white"></span> <?php echo JText::_('JLOGIN'); ?>
			</button>
		</div>

		<div class="forgot">
			<div><a href="<?php echo JUri::root(); ?>index.php?option=com_users&view=remind"><?php echo JText::_('MOD_LOGIN_REMIND'); ?></a></div>
			<div><a href="<?php echo JUri::root(); ?>index.php?option=com_users&view=reset"><?php echo JText::_('MOD_LOGIN_RESET'); ?></a></div>
		</div>

		<input type="hidden" name="option" value="com_login">
		<input type="hidden" name="task" value="login">
		<input type="hidden" name="return" value="<?php echo $return; ?>">
		<?php echo JHtml::_('form.token'); ?>
	</fieldset>
</form>
<script>
	(function(){
		document.addEventListener('DOMContentLoadded', function() {
			var btn = document.getElementById('btn-login-submit');

			if(btn) {
				btn.addEventListener('click', function(e) {
					e.preventDefault();
					var form = document.getElementById('form-login');
					if (form && document.formvalidator.isValid(form)) {
						Joomla.submitbutton('login')
					}
				});
			}
		});
	})();
</script>