<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.aurora
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var JDocumentHtml $this */

$twofactormethods = JAuthenticationHelper::getTwoFactorMethods();
$app              = JFactory::getApplication();

$fullWidth = 1;

// Add JavaScript Frameworks
JHtml::_('behavior.core');

// Add template js
JHtml::_('script', 'template.js', ['version' => 'auto', 'relative' => true]);

// Add Stylesheets
JHtml::_('stylesheet', 'template.css', ['version' => 'auto', 'relative' => true]);
JHtml::_('stylesheet', 'offline.css', ['version' => 'auto', 'relative' => true]);

// Alerts progressive enhancement
JHtml::_('webcomponent', ['joomla-alert' => 'system/joomla-alert.min.js'], ['relative' => true, 'version' => 'auto', 'detectBrowser' => false, 'detectDebug' => false]);

// Template color
if ($this->params->get('templateColor'))
{
	$this->addStyleDeclaration('
	body.site {
		border-top: 3px solid ' . $this->params->get('templateColor') . ';
		background-color: ' . $this->params->get('templateBackgroundColor') . ';
	}
	a {
		color: ' . $this->params->get('templateColor') . ';
	}
	.nav-list > .active > a,
	.nav-list > .active > a:hover,
	.dropdown-menu li > a:hover,
	.dropdown-menu .active > a,
	.dropdown-menu .active > a:hover,
	.nav-pills > .active > a,
	.nav-pills > .active > a:hover,
	.btn-primary {
		background: ' . $this->params->get('templateColor') . ';
	}');
}

// Check for a custom CSS file
JHtml::_('stylesheet', 'user.css', ['version' => 'auto', 'relative' => true]);

// Check for a custom js file
JHtml::_('script', 'user.js', ['version' => 'auto', 'relative' => true]);

// Load optional RTL Bootstrap CSS
JHtml::_('bootstrap.loadCss', false, $this->direction);

// Logo file or site title param
$sitename = $app->get('sitename');

if ($this->params->get('logoFile'))
{
	$logo = '<img src="' . JUri::root() . $this->params->get('logoFile') . '" alt="' . $sitename . '" />';
}
elseif ($this->params->get('siteTitle'))
{
	$logo = '<span class="site-title" title="' . $sitename . '">' . htmlspecialchars($this->params->get('siteTitle')) . '</span>';
}
else
{
	$logo = '<span class="site-title" title="' . $sitename . '">' . $sitename . '</span>';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<jdoc:include type="head" />
</head>
<body class="site">
	<div class="outer">
		<div class="offline-card">
			<div class="header">
			<?php if (!empty($logo)) : ?>
				<h1><?php echo $logo; ?></h1>
			<?php else : ?>
				<h1><?php echo htmlspecialchars($app->get('sitename')); ?></h1>
			<?php endif; ?>
			<?php if ($app->get('offline_image') && file_exists($app->get('offline_image'))) : ?>
				<img src="<?php echo $app->get('offline_image'); ?>" alt="<?php echo htmlspecialchars($app->get('sitename')); ?>">
			<?php endif; ?>
			<?php if ($app->get('display_offline_message', 1) == 1 && str_replace(' ', '', $app->get('offline_message')) != '') : ?>
				<p><?php echo $app->get('offline_message'); ?></p>
			<?php elseif ($app->get('display_offline_message', 1) == 2) : ?>
				<p><?php echo JText::_('JOFFLINE_MESSAGE'); ?></p>
			<?php endif; ?>
			<div class="logo-icon">
				<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
				 viewBox="0 0 74.8 74.8" enable-background="new 0 0 74.8 74.8" xml:space="preserve">
					<g id="brandmark">
						<path id="j-green" fill="#1C3D5C" d="M13.5,37.7L12,36.3c-4.5-4.5-5.8-10.8-4.2-16.5c-4.5-1-7.8-5-7.8-9.8c0-5.5,4.5-10,10-10 c5,0,9.1,3.6,9.9,8.4c5.4-1.3,11.3,0.2,15.5,4.4l0.6,0.6l-7.4,7.4l-0.6-0.6c-2.4-2.4-6.3-2.4-8.7,0c-2.4,2.4-2.4,6.3,0,8.7l1.4,1.4 l7.4,7.4l7.8,7.8l-7.4,7.4l-7.8-7.8L13.5,37.7L13.5,37.7z"/>
						<path id="j-orange" fill="#1C3D5C" d="M21.8,29.5l7.8-7.8l7.4-7.4l1.4-1.4C42.9,8.4,49.2,7,54.8,8.6C55.5,3.8,59.7,0,64.8,0 c5.5,0,10,4.5,10,10c0,5.1-3.8,9.3-8.7,9.9c1.6,5.6,0.2,11.9-4.2,16.3l-0.6,0.6l-7.4-7.4l0.6-0.6c2.4-2.4,2.4-6.3,0-8.7 c-2.4-2.4-6.3-2.4-8.7,0l-1.4,1.4L37,29l-7.8,7.8L21.8,29.5L21.8,29.5z"/>
						<path id="j-red" fill="#1C3D5C" d="M55,66.8c-5.7,1.7-12.1,0.4-16.6-4.1l-0.6-0.6l7.4-7.4l0.6,0.6c2.4,2.4,6.3,2.4,8.7,0 c2.4-2.4,2.4-6.3,0-8.7L53,45.1l-7.4-7.4l-7.8-7.8l7.4-7.4l7.8,7.8l7.4,7.4l1.5,1.5c4.2,4.2,5.7,10.2,4.4,15.7 c4.9,0.7,8.6,4.9,8.6,9.9c0,5.5-4.5,10-10,10C60,74.8,56,71.3,55,66.8L55,66.8z"/>
						<path id="j-blue" fill="#1C3D5C" d="M52.2,46l-7.8,7.8L37,61.2l-1.4,1.4c-4.3,4.3-10.3,5.7-15.7,4.4c-1,4.5-5,7.8-9.8,7.8 c-5.5,0-10-4.5-10-10C0,60,3.3,56.1,7.7,55C6.3,49.5,7.8,43.5,12,39.2l0.6-0.6L20,46l-0.6,0.6c-2.4,2.4-2.4,6.3,0,8.7 c2.4,2.4,6.3,2.4,8.7,0l1.4-1.4l7.4-7.4l7.8-7.8L52.2,46L52.2,46z"/>
					</g>
				</svg>
			</div>
			</div>
			<div class="login">
				<jdoc:include type="message" />
				<form action="<?php echo JRoute::_('index.php', true); ?>" method="post" id="form-login">
					<fieldset>
						<label for="username"><?php echo JText::_('JGLOBAL_USERNAME'); ?></label>
						<input name="username" class="form-control" id="username" type="text" title="<?php echo JText::_('JGLOBAL_USERNAME'); ?>">

						<label for="password"><?php echo JText::_('JGLOBAL_PASSWORD'); ?></label>
						<input name="password" class="form-control" id="password" type="password" title="<?php echo JText::_('JGLOBAL_PASSWORD'); ?>">

						<?php if (count($twofactormethods) > 1) : ?>
						<label for="secretkey"><?php echo JText::_('JGLOBAL_SECRETKEY'); ?></label>
						<input name="secretkey" class="form-control" id="secretkey" type="text" title="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>">
						<?php endif; ?>

						<input type="submit" name="Submit" class="btn btn-primary" value="<?php echo JText::_('JLOGIN'); ?>">

						<input type="hidden" name="option" value="com_users">
						<input type="hidden" name="task" value="user.login">
						<input type="hidden" name="return" value="<?php echo base64_encode(JUri::base()); ?>">
						<?php echo JHtml::_('form.token'); ?>
					</fieldset>
				</form>
			</div>
		</div>
	</div>
</body>
</html>
