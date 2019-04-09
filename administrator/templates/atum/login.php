<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use OzdemirBurak\Iris\Color\Hex;

/** @var JDocumentHtml $this */

$app  = Factory::getApplication();
$lang = $app->getLanguage();

// Add JavaScript Frameworks
HTMLHelper::_('script', 'vendor/css-vars-ponyfill/css-vars-ponyfill.min.js', ['version' => 'auto', 'relative' => true]);

// Load template JS file
HTMLHelper::_('script', 'media/templates/' . $this->template . '/js/template.min.js', ['version' => 'auto']);

// Load template CSS file
HTMLHelper::_('stylesheet', 'bootstrap.min.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'font-awesome.min.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'template' . ($this->direction === 'rtl' ? '-rtl' : '') . '.min.css', ['version' => 'auto', 'relative' => true]);

// Load custom CSS file
HTMLHelper::_('stylesheet', 'user.css', ['version' => 'auto', 'relative' => true]);

// Load specific language related CSS
HTMLHelper::_('stylesheet', 'administrator/language/' . $lang->getTag() . '/' . $lang->getTag() . '.css', ['version' => 'auto']);

// Detecting Active Variables
$option   = $app->input->getCmd('option', '');
$view     = $app->input->getCmd('view', '');
$layout   = $app->input->getCmd('layout', 'default');
$sitename = $app->get('sitename');

// Template params
$siteLogo  = $this->params->get('siteLogo')
	? JUri::root() . $this->params->get('siteLogo')
	: $this->baseurl . '/templates/' . $this->template . '/images/logo-joomla-blue.svg';
$loginLogo = $this->params->get('loginLogo')
	? JUri::root() . $this->params->get('loginLogo')
	: $this->baseurl . '/templates/' . $this->template . '/images/logo-blue.svg';
$smallLogo = $this->params->get('smallLogo')
	? JUri::root() . $this->params->get('smallLogo')
	: $this->baseurl . '/templates/' . $this->template . '/images/logo-blue.svg';

// Set some meta data
$this->setMetaData('viewport', 'width=device-width, initial-scale=1');
// @TODO sync with _variables.scss
$this->setMetaData('theme-color', '#1c3d5c');
$this->addScriptDeclaration('cssVars();');

// Set page title
$this->setTitle(Text::sprintf('TPL_ATUM_LOGIN_SITE_TITLE', $sitename));

// Opacity must be set before displaying the DOM, so don't move to a CSS file
$css = '
	.container-main > * {
		opacity: 0;
	}
	.sidebar-wrapper > * {
		opacity: 0;
	}
';

$root = [];

$steps = 10;

if ($this->params->get('bg-dark'))
{
	$bgcolor = trim($this->params->get('bg-dark'), '#');

	list($red, $green, $blue) = str_split($bgcolor, 2);

	$root[] = '--atum-bg-dark: #' . $bgcolor . ';';

	try
	{
		$color    = new Hex($bgcolor);
		$colorHsl = $color->toHsl();

		$root[] = '--atum-contrast: ' . (clone $colorHsl)->lighten(-6)->spin(-30)->toHex() . ';';
		$root[] = '--atum-bg-dark-10: ' . (clone $colorHsl)->desaturate(86)->lighten(20.5)->spin(-6)->toHex() . ';';
		$root[] = '--atum-bg-dark-20: ' . (clone $colorHsl)->desaturate(76)->lighten(16.5)->spin(-6)->toHex() . ';';
		$root[] = '--atum-bg-dark-30: ' . (clone $colorHsl)->desaturate(60)->lighten(12)->spin(-5)->toHex() . ';';
		$root[] = '--atum-bg-dark-40: ' . (clone $colorHsl)->desaturate(41)->lighten(8)->spin(-3)->toHex() . ';';
		$root[] = '--atum-bg-dark-50: ' . (clone $colorHsl)->desaturate(19)->lighten(4)->spin(-1)->toHex() . ';';
		$root[] = '--atum-bg-dark-70: ' . (clone $colorHsl)->lighten(-6)->spin(4)->toHex() . ';';
		$root[] = '--atum-bg-dark-80: ' . (clone $colorHsl)->lighten(-11.5)->spin(7)->toHex() . ';';
		$root[] = '--atum-bg-dark-90: ' . (clone $colorHsl)->desaturate(1)->lighten(-17)->spin(10)->toHex() . ';';
	}
	catch (Exception $ex)
	{

	}
}

if ($this->params->get('bg-light'))
{
	$root[] = '--atum-bg-light: ' . $this->params->get('bg-light') . ';';
}

if ($this->params->get('text-dark'))
{
	$root[] = '--atum-text-dark: ' . $this->params->get('text-dark') . ';';
}

if ($this->params->get('text-light'))
{
	$root[] = '--atum-text-light: ' . $this->params->get('text-light') . ';';
}

if ($this->params->get('link-color'))
{
	$linkcolor = trim($this->params->get('link-color'), '#');

	list($red, $green, $blue) = str_split($linkcolor, 2);

	$root[] = '--atum-link-color: #' . $linkcolor . ';';

	try
	{
		$color = new Hex($linkcolor);

		$root[] = '--atum-link-hover-color: ' . (clone $color)->darken(20) . ';';
	}
	catch (Exception $ex)
	{

	}
}

if ($this->params->get('special-color'))
{
	$root[] = '--atum-special-color: ' . $this->params->get('special-color') . ';';
}

if (count($root))
{
	$css .= ':root {' . implode($root) . '}';
}

$this->addStyleDeclaration($css);
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="metas"/>
	<jdoc:include type="styles"/>
</head>

<body class="admin <?php echo $option . ' view-' . $view . ' layout-' . $layout; ?>">

<noscript>
	<div class="alert alert-danger" role="alert">
		<?php echo Text::_('JGLOBAL_WARNJAVASCRIPT'); ?>
	</div>
</noscript>

<header id="header" class="header">
	<div class="d-flex align-items-center">
		<div class="header-title mr-auto">
            <div class="logo">
                <img src="<?php echo $siteLogo; ?>" alt="">
                <img class="logo-small" src="<?php echo $smallLogo; ?>" alt="">
            </div>
		</div>
	</div>
</header>

<div id="wrapper" class="d-flex wrapper">

	<?php // Sidebar ?>
	<div id="sidebar-wrapper" class="sidebar-wrapper">
		<div id="main-brand" class="main-brand">
			<div class="h2"><?php echo $sitename; ?></div>
			<a href="<?php echo Uri::root(); ?>"><?php echo Text::_('TPL_ATUM_LOGIN_SIDEBAR_VIEW_WEBSITE'); ?></a>
		</div>
		<div id="sidebar">
			<jdoc:include type="modules" name="sidebar" style="body"/>
		</div>
	</div>

	<div class="container-fluid container-main">
		<section id="content" class="content h-100">
			<main class="d-flex justify-content-center align-items-center h-100">
				<div class="login">
					<div class="main-brand d-flex align-items-center justify-content-center">
						<img src="<?php echo $loginLogo; ?>" alt="">
					</div>
					<h1><?php echo Text::_('TPL_ATUM_BACKEND_LOGIN'); ?></h1>
					<jdoc:include type="message"/>
					<jdoc:include type="component"/>
				</div>
			</main>
		</section>
	</div>
</div>
<jdoc:include type="modules" name="debug" style="none"/>
<jdoc:include type="scripts"/>
</body>
</html>
