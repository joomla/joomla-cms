<?php
/**
 * @package     Joomla.Site
 * @subpackage  Template.system
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\AuthenticationHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\Document\HtmlDocument $this */

$app = Factory::getApplication();
$wa  = $this->getWebAssetManager();

// Styles
$wa->registerAndUseStyle('template.system.offline', 'templates/system/css/offline.css');

if ($this->direction === 'rtl')
{
	$wa->registerAndUseStyle('template.system.offline_rtl', 'templates/system/css/offline_rtl.css');
}

$wa->registerAndUseStyle('template.system.general', 'templates/system/css/general.css');

$twofactormethods = AuthenticationHelper::getTwoFactorMethods();

?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<jdoc:include type="head" />
</head>
<body>
	<jdoc:include type="message" />
	<div id="frame" class="outline">
		<?php if ($app->get('offline_image') && file_exists($app->get('offline_image'))) : ?>
			<img src="<?php echo $app->get('offline_image'); ?>" alt="<?php echo htmlspecialchars($app->get('sitename'), ENT_COMPAT, 'UTF-8'); ?>" />
		<?php endif; ?>
		<h1>
			<?php echo htmlspecialchars($app->get('sitename'), ENT_COMPAT, 'UTF-8'); ?>
		</h1>
	<?php if ($app->get('display_offline_message', 1) == 1 && str_replace(' ', '', $app->get('offline_message')) !== '') : ?>
		<p>
			<?php echo $app->get('offline_message'); ?>
		</p>
	<?php elseif ($app->get('display_offline_message', 1) == 2 && str_replace(' ', '', Text::_('JOFFLINE_MESSAGE')) !== '') : ?>
		<p>
			<?php echo Text::_('JOFFLINE_MESSAGE'); ?>
		</p>
	<?php endif; ?>
	<form action="<?php echo Route::_('index.php', true); ?>" method="post" id="form-login">
	<fieldset class="input">
		<p id="form-login-username">
			<label for="username"><?php echo Text::_('JGLOBAL_USERNAME'); ?></label>
			<input name="username" id="username" type="text" class="inputbox" alt="<?php echo Text::_('JGLOBAL_USERNAME'); ?>" autocomplete="off" autocapitalize="none" />
		</p>
		<p id="form-login-password">
			<label for="passwd"><?php echo Text::_('JGLOBAL_PASSWORD'); ?></label>
			<input type="password" name="password" class="inputbox" alt="<?php echo Text::_('JGLOBAL_PASSWORD'); ?>" id="passwd" />
		</p>
		<?php if (count($twofactormethods) > 1) : ?>
			<p id="form-login-secretkey">
				<label for="secretkey"><?php echo Text::_('JGLOBAL_SECRETKEY'); ?></label>
				<input type="text" name="secretkey" autocomplete="one-time-code" class="inputbox" alt="<?php echo Text::_('JGLOBAL_SECRETKEY'); ?>" id="secretkey" />
			</p>
		<?php endif; ?>
		<p id="submit-button">
			<input type="submit" name="Submit" class="button login" value="<?php echo Text::_('JLOGIN'); ?>" />
		</p>
		<input type="hidden" name="option" value="com_users" />
		<input type="hidden" name="task" value="user.login" />
		<input type="hidden" name="return" value="<?php echo base64_encode(Uri::base()); ?>" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</fieldset>
	</form>
	</div>
</body>
</html>
