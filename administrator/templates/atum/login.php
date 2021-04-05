<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var JDocumentHtml $this */

$app   = Factory::getApplication();
$input = $app->input;
$wa    = $this->getWebAssetManager();

// Detecting Active Variables
$option = $input->get('option', '');
$view   = $input->get('view', '');
$layout = $input->get('layout', 'default');
$task   = $input->get('task', 'display');

// Browsers support SVG favicons
$this->addHeadLink(HTMLHelper::_('image', 'joomla-favicon.svg', '', [], true, 1), 'icon', 'rel', ['type' => 'image/svg+xml']);
$this->addHeadLink(HTMLHelper::_('image', 'favicon.ico', '', [], true, 1), 'alternate icon', 'rel', ['type' => 'image/vnd.microsoft.icon']);
$this->addHeadLink(HTMLHelper::_('image', 'joomla-favicon-pinned.svg', '', [], true, 1), 'mask-icon', 'rel', ['color' => '#000']);

// Template params
$logoBrandLarge  = $this->params->get('logoBrandLarge')
	? Uri::root() . htmlspecialchars($this->params->get('logoBrandLarge'), ENT_QUOTES)
	: $this->baseurl . '/templates/' . $this->template . '/images/logos/brand-large.svg';
$loginLogo = $this->params->get('loginLogo')
	? Uri::root() . $this->params->get('loginLogo')
	: $this->baseurl . '/templates/' . $this->template . '/images/logos/login.svg';
$logoBrandSmall = $this->params->get('logoBrandSmall')
	? Uri::root() . htmlspecialchars($this->params->get('logoBrandSmall'), ENT_QUOTES)
	: $this->baseurl . '/templates/' . $this->template . '/images/logos/brand-small.svg';

$logoBrandLargeAlt = empty($this->params->get('logoBrandLargeAlt')) && empty($this->params->get('emptyLogoBrandLargeAlt'))
	? ''
	: 'alt="' . htmlspecialchars($this->params->get('logoBrandLargeAlt'), ENT_COMPAT, 'UTF-8') . '"';
$logoBrandSmallAlt = empty($this->params->get('logoBrandSmallAlt')) && empty($this->params->get('emptyLogoBrandSmallAlt'))
	? ''
	: 'alt="' . htmlspecialchars($this->params->get('logoBrandSmallAlt'), ENT_COMPAT, 'UTF-8') . '"';
$loginLogoAlt = empty($this->params->get('loginLogoAlt')) && empty($this->params->get('emptyLoginLogoAlt'))
	? ''
	: 'alt="' . htmlspecialchars($this->params->get('loginLogoAlt'), ENT_COMPAT, 'UTF-8') . '"';

// Get the hue value
preg_match('#^hsla?\(([0-9]+)[\D]+([0-9]+)[\D]+([0-9]+)[\D]+([0-9](?:.\d+)?)?\)$#i', $this->params->get('hue', 'hsl(214,63%,20%)'), $matches);

// Enable assets
$wa->usePreset('template.atum.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr'))
		->useStyle('template.active.language')
		->useStyle('template.user')
		->addInlineStyle(':root {
		--hue: ' . $matches[1] . ';
		--atum-bg-light: ' . $this->params->get('bg-light', '--atum-bg-light') . ';
		--atum-text-dark: ' . $this->params->get('text-dark', '--atum-text-dark') . ';
		--atum-text-light: ' . $this->params->get('text-light', '--atum-text-light') . ';
		--atum-link-color: ' . $this->params->get('link-color', '--atum-link-color') . ';
		--atum-special-color: ' . $this->params->get('special-color', '--atum-special-color') . ';
		--atum-sidebar-link-color: ' . $this->params->get('sidebar-link-color', '--atum-sidebar-link-color') . ';
	}');

// Override 'template.active' asset to set correct ltr/rtl dependency
$wa->registerStyle('template.active', '', [], [], ['template.atum.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr')]);

// Set some meta data
$this->setMetaData('viewport', 'width=device-width, initial-scale=1');
// @TODO sync with _variables.scss
$this->setMetaData('theme-color', '#1c3d5c');

$monochrome = (bool) $this->params->get('monochrome');

// Add cookie alert message
Text::script('JGLOBAL_WARNCOOKIES');
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="metas" />
	<jdoc:include type="styles" />
</head>

<body class="admin <?php echo $option . ' view-' . $view . ' layout-' . $layout . ($task ? ' task-' . $task : '') . ($monochrome ? ' monochrome' : ''); ?>">

<noscript>
	<div class="alert alert-danger" role="alert">
		<?php echo Text::_('JGLOBAL_WARNJAVASCRIPT'); ?>
	</div>
</noscript>
<div class="ie11 alert alert-warning" role="alert">
	<?php echo Text::_('JGLOBAL_WARNIE'); ?>
</div>

<header id="header" class="header">
	<div class="d-flex">
		<div class="header-title d-flex">
			<div class="d-flex align-items-center">
				<div class="logo">
					<img src="<?php echo $logoBrandLarge; ?>" <?php echo $logoBrandLargeAlt; ?>>
					<img class="logo-collapsed" src="<?php echo $logoBrandSmall; ?>" <?php echo $logoBrandSmallAlt; ?>>
				</div>
			</div>
			<jdoc:include type="modules" name="title" />
		</div>
		<div class="header-items d-flex">
			<jdoc:include type="modules" name="status" style="header-item" />
		</div>
	</div>
</header>

<div id="wrapper" class="d-flex wrapper">

	<div class="container-fluid container-main order-1">
		<section id="content" class="content h-100">
			<div class="login_message">
				<jdoc:include type="message" />
			</div>
			<main class="d-flex justify-content-center align-items-center h-100">
				<div class="login">
					<div class="main-brand logo text-center">
						<img src="<?php echo $loginLogo; ?>" <?php echo $loginLogoAlt; ?>>
					</div>
					<jdoc:include type="component" />
				</div>
				<div class="login-watermark">
					<svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="white" d="M.6 92.1C.6 58.8 27.4 32 60.4 32c30 0 54.5 21.9 59.2 50.2 32.6-7.6 67.1.6 96.5 30l-44.3 44.3c-20.5-20.5-42.6-16.3-55.4-3.5-14.3 14.3-14.3 37.9 0 52.2l99.5 99.5-44 44.3c-87.7-87.2-49.7-49.7-99.8-99.7-26.8-26.5-35-64.8-24.8-98.9C20.4 144.6.6 120.7.6 92.1zm129.5 116.4l44.3 44.3c10-10 89.7-89.7 99.7-99.8 14.3-14.3 37.6-14.3 51.9 0 12.8 12.8 17 35-3.5 55.4l44 44.3c31.2-31.2 38.5-67.6 28.9-101.2 29.2-4.1 51.9-29.2 51.9-59.5 0-33.2-26.8-60.1-59.8-60.1-30.3 0-55.4 22.5-59.5 51.6-33.8-9.9-71.7-1.5-98.3 25.1-18.3 19.1-71.1 71.5-99.6 99.9zm266.3 152.2c8.2-32.7-.9-68.5-26.3-93.9-11.8-12.2 5 4.7-99.5-99.7l-44.3 44.3 99.7 99.7c14.3 14.3 14.3 37.6 0 51.9-12.8 12.8-35 17-55.4-3.5l-44 44.3c27.6 30.2 68 38.8 102.7 28 5.5 27.4 29.7 48.1 58.9 48.1 33 0 59.8-26.8 59.8-60.1 0-30.2-22.5-55-51.6-59.1zm-84.3-53.1l-44-44.3c-87 86.4-50.4 50.4-99.7 99.8-14.3 14.3-37.6 14.3-51.9 0-13.1-13.4-16.9-35.3 3.2-55.4l-44-44.3c-30.2 30.2-38 65.2-29.5 98.3-26.7 6-46.2 29.9-46.2 58.2C0 453.2 26.8 480 59.8 480c28.6 0 52.5-19.8 58.6-46.7 32.7 8.2 68.5-.6 94.2-26 32.1-32 12.2-12.4 99.5-99.7z"></path></svg>
			</main>
		</section>
	</div>

	<?php // Sidebar ?>
	<div id="sidebar-wrapper" class="sidebar-wrapper order-0 px-3">
		<div id="main-brand" class="main-brand">
			<h1><?php echo $app->get('sitename'); ?></h1>
			<h2><?php echo Text::_('TPL_ATUM_BACKEND_LOGIN'); ?></h2>
		</div>
		<div id="sidebar">
			<jdoc:include type="modules" name="sidebar" style="body" />
		</div>
	</div>
</div>
<jdoc:include type="modules" name="debug" style="none" />
<jdoc:include type="scripts" />
</body>
</html>
