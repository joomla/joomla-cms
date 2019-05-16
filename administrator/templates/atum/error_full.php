<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use OzdemirBurak\Iris\Color\Hex;

/** @var JDocumentError $this */

$app   = Factory::getApplication();
$lang  = $app->getLanguage();
$input = $app->input;
$wa    = $this->getWebAssetManager();

// Detecting Active Variables
$option     = $input->get('option', '');
$view       = $input->get('view', '');
$layout     = $input->get('layout', 'default');
$task       = $input->get('task', 'display');
$itemid     = $input->get('Itemid', '');
$cpanel     = $option === 'com_cpanel';
$hiddenMenu = $app->input->get('hidemainmenu');
$joomlaLogo = $this->baseurl . '/templates/' . $this->template . '/images/logo.svg';

// Add JavaScript
HTMLHelper::_('script', 'vendor/focus-visible/focus-visible.min.js', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('script', 'vendor/css-vars-ponyfill/css-vars-ponyfill.min.js', ['version' => 'auto', 'relative' => true]);

// Logos (params are not available)
$siteLogo  = $this->baseurl . '/templates/' . $this->template . '/images/logo-joomla-blue.svg';
$smallLogo = $this->baseurl . '/templates/' . $this->template . '/images/logo-blue.svg';

// Load template CSS file
HTMLHelper::_('stylesheet', 'fontawesome.css', ['version' => 'auto', 'relative' => true]);

// Enable assets
$wa->enableAsset('template.atum.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr'));

// Load specific language related CSS
HTMLHelper::_('stylesheet', 'administrator/language/' . $lang->getTag() . '/' . $lang->getTag() . '.css', ['version' => 'auto']);

// Load specific template related JS
HTMLHelper::_('script', 'media/templates/' . $this->template . '/js/template.min.js', ['version' => 'auto']);

// Set some meta data
$this->setMetaData('viewport', 'width=device-width, initial-scale=1');
$this->setMetaData('theme-color', '#1c3d5c');
$this->addScriptDeclaration('cssVars();');

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
	<jdoc:include type="scripts"/>
</head>

<body class="admin <?php echo $option . ' view-' . $view . ' layout-' . $layout . ($task ? ' task-' . $task : ''); ?>">

<noscript>
	<div class="alert alert-danger" role="alert">
		<?php echo Text::_('JGLOBAL_WARNJAVASCRIPT'); ?>
	</div>
</noscript>

<?php // Header ?>
<header id="header" class="header">
	<div class="d-flex align-items-center">
		<div class="header-title d-flex mr-auto">
			<div class="d-flex">
				<a class="logo" href="<?php echo Route::_('index.php'); ?>"
				   aria-label="<?php echo Text::_('TPL_BACK_TO_CONTROL_PANEL'); ?>">
					<img src="<?php echo $siteLogo; ?>" alt="">
					<img class="logo-small" src="<?php echo $smallLogo; ?>" alt="">
				</a>
			</div>
			<jdoc:include type="modules" name="title"/>
		</div>
		<div class="header-items d-flex ml-auto">
			<jdoc:include type="modules" name="status" style="no"/>
		</div>
	</div>
</header>

<?php // Wrapper ?>
<div id="wrapper" class="d-flex wrapper<?php echo $hiddenMenu ? '0' : ''; ?>">

	<?php // Sidebar ?>
	<?php if (!$hiddenMenu) : ?>
		<div id="sidebar-wrapper" class="sidebar-wrapper" <?php echo $hiddenMenu ? 'data-hidden="' . $hiddenMenu . '"' : ''; ?>>
			<jdoc:include type="modules" name="menu" style="none"/>
			<div id="main-brand" class="main-brand d-flex align-items-center justify-content-center">
				<img src="<?php echo $joomlaLogo; ?>" alt="">
			</div>
		</div>
	<?php endif; ?>

	<?php // container-fluid ?>
	<div class="container-fluid container-main">
		<?php if (!$cpanel) : ?>
			<?php // Subheader ?>
			<a class="btn btn-subhead d-md-none d-lg-none d-xl-none" data-toggle="collapse"
			   data-target=".subhead-collapse"><?php echo Text::_('TPL_ATUM_TOOLBAR'); ?>
				<span class="icon-wrench"></span></a>
			<div id="subhead" class="subhead">
				<div id="container-collapse" class="container-collapse"></div>
				<div class="row">
					<div class="col-md-12">
						<jdoc:include type="modules" name="toolbar" style="no"/>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<section id="content" class="content">
			<?php // Begin Content ?>
			<jdoc:include type="modules" name="top" style="xhtml"/>
			<div class="row">
				<div class="col-md-12">
					<h1><?php echo Text::_('JERROR_AN_ERROR_HAS_OCCURRED'); ?></h1>
					<blockquote class="blockquote">
						<span class="badge badge-secondary"><?php echo $this->error->getCode(); ?></span>
						<?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?>
					</blockquote>
					<?php if ($this->debug) : ?>
						<div>
							<?php echo $this->renderBacktrace(); ?>
							<?php // Check if there are more Exceptions and render their data as well ?>
							<?php if ($this->error->getPrevious()) : ?>
								<?php $loop = true; ?>
								<?php // Reference $this->_error here and in the loop as setError() assigns errors to this property and we need this for the backtrace to work correctly ?>
								<?php // Make the first assignment to setError() outside the loop so the loop does not skip Exceptions ?>
								<?php $this->setError($this->_error->getPrevious()); ?>
								<?php while ($loop === true) : ?>
									<p><strong><?php echo Text::_('JERROR_LAYOUT_PREVIOUS_ERROR'); ?></strong></p>
									<p><?php echo htmlspecialchars($this->_error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></p>
									<?php echo $this->renderBacktrace(); ?>
									<?php $loop = $this->setError($this->_error->getPrevious()); ?>
								<?php endwhile; ?>
								<?php // Reset the main error object to the base error ?>
								<?php $this->setError($this->error); ?>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<p>
						<a href="<?php echo $this->baseurl; ?>" class="btn btn-secondary">
							<span class="fa fa-dashboard" aria-hidden="true"></span>
							<?php echo Text::_('JGLOBAL_TPL_CPANEL_LINK_TEXT'); ?></a>
					</p>
				</div>

				<?php if ($this->countModules('bottom')) : ?>
					<jdoc:include type="modules" name="bottom" style="xhtml"/>
				<?php endif; ?>
			</div>
			<?php // End Content ?>
		</section>

		<div class="notify-alerts">
			<jdoc:include type="message"/>
		</div>
	</div>
</div>
<jdoc:include type="modules" name="debug" style="none"/>
</body>
</html>
