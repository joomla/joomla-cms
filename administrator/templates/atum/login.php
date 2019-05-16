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
use OzdemirBurak\Iris\Color\Hsl;

/** @var JDocumentHtml $this */
// here we go
$app  = Factory::getApplication();
$lang = $app->getLanguage();

// Add JavaScript Frameworks
HTMLHelper::_('script', 'vendor/css-vars-ponyfill/css-vars-ponyfill.min.js', ['version' => 'auto', 'relative' => true]);

// Load template JS file
HTMLHelper::_('script', 'media/templates/' . $this->template . '/js/template.min.js', ['version' => 'auto']);

// Load template CSS file
HTMLHelper::_('stylesheet', 'bootstrap.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'fontawesome.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'template' . ($this->direction === 'rtl' ? '-rtl' : '') . '.css', ['version' => 'auto', 'relative' => true]);

// Load custom CSS file
HTMLHelper::_('stylesheet', 'custom.css', ['version' => 'auto', 'relative' => true]);

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

//check if colors set to monochrome
if($this->params->get('monochrome'))
{
	if ($this->params->get('hue'))
	{
		$bgcolor = new Hsl("hsl(" . $this->params->get('hue') . ", 0, 26)");
		$root[] = '--atum-bg-dark: ' .$bgcolor->toHex() . ';';

		try
		{
			$root[] = '--atum-contrast: ' . (new Hsl("hsl(" . $this->params->get('hue') . ", 0, 42)"))->spin(30)->toHex() . ';';
			$root[] = '--atum-bg-dark-0: ' . (clone $bgcolor)->lighten(71.4)->spin(-6)->toHex() . ';';
			$root[] = '--atum-bg-dark-5: ' . (clone $bgcolor)->lighten(65.1)->spin(-6)->toHex() . ';';
			$root[] = '--atum-bg-dark-10: ' . (clone $bgcolor)->lighten(59.4)->spin(-6)->toHex() . ';';
			$root[] = '--atum-bg-dark-20: ' . (clone $bgcolor)->lighten(47.3)->spin(-6)->toHex() . ';';
			$root[] = '--atum-bg-dark-30: ' . (clone $bgcolor)->lighten(34.3)->spin(-5)->toHex() . ';';
			$root[] = '--atum-bg-dark-40: ' . (clone $bgcolor)->lighten(21.4)->spin(-3)->toHex() . ';';
			$root[] = '--atum-bg-dark-50: ' . (clone $bgcolor)->lighten(10)->spin(-1)->toHex() . ';';
			$root[] = '--atum-bg-dark-70: ' . (clone $bgcolor)->lighten(-6)->spin(4)->toHex() . ';';
			$root[] = '--atum-bg-dark-80: ' . (clone $bgcolor)->lighten(-11.5)->spin(7)->toHex() . ';';
			$root[] = '--atum-bg-dark-90: ' . (clone $bgcolor)->lighten(-17)->spin(10)->toHex() . ';';
		}
		catch (Exception $ex)
		{

		}
	}

	if ($this->params->get('bg-light'))
	{
		$bgcolor = trim($this->params->get('bg-light'), '#');
		list($red, $green, $blue) = str_split($bgcolor, 2);
		$color    = new Hex($bgcolor);
		$colorHsl = $color->toHsl();
		$root[] = '--atum-bg-light: ' . (clone $colorHsl)->grayscale()->toHex() . ';';

	}

	if ($this->params->get('text-dark'))
	{
		$bgcolor = trim($this->params->get('text-dark'), '#');
		list($red, $green, $blue) = str_split($bgcolor, 2);
		$color    = new Hex($bgcolor);
		$colorHsl = $color->toHsl();
		$root[] = '--atum-text-dark: ' . (clone $colorHsl)->grayscale()->toHex() . ';';
	}

	if ($this->params->get('text-light'))
	{
		$bgcolor = trim($this->params->get('text-light'), '#');
		list($red, $green, $blue) = str_split($bgcolor, 2);
		$color    = new Hex($bgcolor);
		$colorHsl = $color->toHsl();
		$root[] = '--atum-text-light: ' . (clone $colorHsl)->grayscale()->toHex() . ';';
	}

	if ($this->params->get('special-color'))
	{
		$bgcolor = trim($this->params->get('special-color'), '#');
		list($red, $green, $blue) = str_split($bgcolor, 2);
		$color    = new Hex($bgcolor);
		$colorHsl = $color->toHsl();
		$root[] = '--atum-special-color: ' . (clone $colorHsl)->grayscale()->toHex() . ';';
	}

	if ($this->params->get('link-color'))
	{
		$linkcolor = trim($this->params->get('link-color'), '#');
		list($red, $green, $blue) = str_split($linkcolor, 2);
		$color    = new Hex($linkcolor);
		$colorHsl = $color->toHsl();
		$linkcolororig = (clone $colorHsl)->grayscale()->toHex();
		$root[] = '--atum-link-color: ' . $linkcolororig . ';';

		try
		{
			$color = new Hex($linkcolororig);
			$root[] = '--atum-link-hover-color: ' . (clone $color)->darken(20) . ';';
		}
		catch (Exception $ex)
		{

		}
	}

//normal colors
}
else
{
	if ($this->params->get('hue'))
	{
		$bgcolor = new Hsl("hsl(" . $this->params->get('hue') . ", 61, 26)");
		$root[] = '--atum-bg-dark: ' .(new Hsl("hsl(" . $this->params->get('hue') . ", 61, 26)"))->toHex() . ';';

		try
		{
			$root[] = '--atum-contrast: ' . (new Hsl("hsl(" . $this->params->get('hue') . ", 61, 42)"))->spin(30)->toHex() . ';';
			$root[] = '--atum-bg-dark-0: ' . (clone $bgcolor)->desaturate(86)->lighten(71.4)->spin(-6)->toHex() . ';';
			$root[] = '--atum-bg-dark-5: ' . (clone $bgcolor)->desaturate(86)->lighten(65.1)->spin(-6)->toHex() . ';';
			$root[] = '--atum-bg-dark-10: ' . (clone $bgcolor)->desaturate(86)->lighten(59.4)->spin(-6)->toHex() . ';';
			$root[] = '--atum-bg-dark-20: ' . (clone $bgcolor)->desaturate(76)->lighten(47.3)->spin(-6)->toHex() . ';';
			$root[] = '--atum-bg-dark-30: ' . (clone $bgcolor)->desaturate(60)->lighten(34.3)->spin(-5)->toHex() . ';';
			$root[] = '--atum-bg-dark-40: ' . (clone $bgcolor)->desaturate(41)->lighten(21.4)->spin(-3)->toHex() . ';';
			$root[] = '--atum-bg-dark-50: ' . (clone $bgcolor)->desaturate(19)->lighten(10)->spin(-1)->toHex() . ';';
			$root[] = '--atum-bg-dark-70: ' . (clone $bgcolor)->lighten(-6)->spin(4)->toHex() . ';';
			$root[] = '--atum-bg-dark-80: ' . (clone $bgcolor)->lighten(-11.5)->spin(7)->toHex() . ';';
			$root[] = '--atum-bg-dark-90: ' . (clone $bgcolor)->desaturate(1)->lighten(-17)->spin(10)->toHex() . ';';
		}
		catch (Exception $ex)
		{

		}
	}

	if ($this->params->get('bg-light'))
	{
		$lightcolor = trim($this->params->get('bg-light'), '#');
		list($red, $green, $blue) = str_split($lightcolor, 2);
		$root[] = '--atum-bg-light: #' . $lightcolor . ';';

		try
		{
			$color = new Hex($lightcolor);
			$root[] = '--toolbar-bg: ' . (clone $color)->lighten(5) . ';';
		}
		catch (Exception $ex)
		{

		}

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

	/*if ($this->params->get('contrast-color'))
	{
		$root[] = '--atum-contrast: ' . $this->params->get('contrast-color') . ';';
	}*/
}//end of else for monochrome


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
                <img src="<?php echo $siteLogo; ?>"
			alt="<?php echo htmlspecialchars($this->params->get('altSiteLogo', ''), ENT_COMPAT, 'UTF-8'); ?>">
                <img class="logo-small" src="<?php echo $smallLogo; ?>"
			alt="<?php echo htmlspecialchars($this->params->get('altSmallLogo', ''), ENT_COMPAT, 'UTF-8'); ?>">
            </div>
			</div>
		<div class="site-name ml-auto mr-3">
			<a class="nav-link" href="<?php echo Uri::root(); ?>"
			   title="<?php echo Text::sprintf('MOD_STATUS_PREVIEW', $sitename); ?>"
			   target="_blank">
				<span class="sr-only"><?php echo HTMLHelper::_('string.truncate', $sitename, 28, false, false); ?></span>
				<?php echo htmlspecialchars($sitename, ENT_COMPAT, 'UTF-8'); ?>
			</a>
		</div>
	</div>
</header>

<div id="wrapper" class="d-flex wrapper">

	<div class="container-fluid container-main order-1">
		<section id="content" class="content h-100">
			<main class="d-flex justify-content-center align-items-center h-100">
				<div class="login">
					<div class="main-brand d-flex align-items-center justify-content-center">
						<img src="<?php echo $loginLogo; ?>"
							 alt="<?php echo htmlspecialchars($this->params->get('altLoginLogo', ''), ENT_COMPAT, 'UTF-8'); ?>">
					</div>
					<jdoc:include type="message"/>
					<jdoc:include type="component"/>
				</div>
			</main>
		</section>
	</div>

	<?php // Sidebar ?>
	<div id="sidebar-wrapper" class="sidebar-wrapper order-0">
		<div id="main-brand" class="main-brand">
			<h1><?php echo Text::_('TPL_ATUM_BACKEND_LOGIN'); ?></h1>
		</div>
		<div id="sidebar">
			<jdoc:include type="modules" name="sidebar" style="body"/>
		</div>
	</div>
</div>
<jdoc:include type="modules" name="debug" style="none"/>
<jdoc:include type="scripts"/>
</body>
</html>
