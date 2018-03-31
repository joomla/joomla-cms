<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.isis
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var JDocumentHtml $this */

$app  = JFactory::getApplication();
$lang = JFactory::getLanguage();

// Output as HTML5
$this->setHtml5(true);

// Gets the FrontEnd Main page Uri
$frontEndUri = JUri::getInstance(JUri::root());
$frontEndUri->setScheme(((int) $app->get('force_ssl', 0) === 2) ? 'https' : 'http');

// Color Params
$background_color = $this->params->get('loginBackgroundColor') ?: '';
$color_is_light   = $background_color && colorIsLight($background_color);

// Add JavaScript Frameworks
JHtml::_('bootstrap.framework');
JHtml::_('bootstrap.tooltip');

// Add html5 shiv
JHtml::_('script', 'jui/html5.js', array('version' => 'auto', 'relative' => true, 'conditional' => 'lt IE 9'));

// Add Stylesheets
JHtml::_('stylesheet', 'template' . ($this->direction === 'rtl' ? '-rtl' : '') . '.css', array('version' => 'auto', 'relative' => true));

// Load optional RTL Bootstrap CSS
JHtml::_('bootstrap.loadCss', false, $this->direction);

// Load specific language related CSS
JHtml::_('stylesheet', 'administrator/language/' . $lang->getTag() . '/' . $lang->getTag() . '.css', array('version' => 'auto'));

// Load custom.css
JHtml::_('stylesheet', 'custom.css', array('version' => 'auto', 'relative' => true));

// Detecting Active Variables
$option   = $app->input->getCmd('option', '');
$view     = $app->input->getCmd('view', '');
$layout   = $app->input->getCmd('layout', '');
$task     = $app->input->getCmd('task', '');
$itemid   = $app->input->getCmd('Itemid', '');
$sitename = htmlspecialchars($app->get('sitename', ''), ENT_QUOTES, 'UTF-8');

function colorIsLight($color)
{
	$r = hexdec(substr($color, 1, 2));
	$g = hexdec(substr($color, 3, 2));
	$b = hexdec(substr($color, 5, 2));

	$yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

	return $yiq >= 200;
}

// Background color
if ($background_color)
{
	$this->addStyleDeclaration('
	.view-login {
		background-color: ' . $background_color . ';
	}');
}

// Responsive Styles
$this->addStyleDeclaration('
	@media (max-width: 480px) {
		.view-login .container {
			margin-top: -170px;
		}
		.btn {
			font-size: 13px;
			padding: 4px 10px 4px;
		}
	}');

// Check if debug is on
if (JPluginHelper::isEnabled('system', 'debug') && ($app->get('debug_lang', 0) || $app->get('debug', 0)))
{
	$this->addStyleDeclaration('
	.view-login .container {
		position: static;
		margin-top: 20px;
		margin-left: auto;
		margin-right: auto;
	}
	.view-login .navbar-fixed-bottom {
		position: relative;
	}');
}
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<jdoc:include type="head" />
</head>
<body class="site <?php echo $option . ' view-' . $view . ' layout-' . $layout . ' task-' . $task . ' itemid-' . $itemid . ' '; ?>">
	<!-- Container -->
	<div class="container">
		<div id="content">
			<!-- Begin Content -->
			<div id="element-box" class="login well">
				<?php if ($loginLogoFile = $this->params->get('loginLogoFile')) : ?>
					<img src="<?php echo JUri::root() . $loginLogoFile; ?>" alt="<?php echo $sitename; ?>" />
				<?php else: ?>
					<img src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/images/joomla.png" alt="<?php echo $sitename; ?>" />
				<?php endif; ?>
				<hr />
				<jdoc:include type="message" />
				<jdoc:include type="component" />
			</div>
			<noscript>
				<?php echo JText::_('JGLOBAL_WARNJAVASCRIPT'); ?>
			</noscript>
			<!-- End Content -->
		</div>
	</div>
	<div class="navbar<?php echo $color_is_light ? ' navbar-inverse' : ''; ?> navbar-fixed-bottom hidden-phone">
		<p class="pull-right">
			&copy; <?php echo date('Y'); ?> <?php echo $sitename; ?>
		</p>
		<a class="login-joomla hasTooltip" href="https://www.joomla.org" target="_blank"  rel="noopener noreferrer" title="<?php echo JHtml::_('tooltipText', 'TPL_ISIS_ISFREESOFTWARE'); ?>"><span class="icon-joomla"></span></a>
		<a href="<?php echo $frontEndUri->toString(); ?>" target="_blank" class="pull-left"><span class="icon-out-2"></span><?php echo JText::_('COM_LOGIN_RETURN_TO_SITE_HOME_PAGE'); ?></a>
	</div>
	<jdoc:include type="modules" name="debug" style="none" />
</body>
</html>
