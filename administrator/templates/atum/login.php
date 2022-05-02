<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;

/** @var \Joomla\CMS\Document\HtmlDocument $this */

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
	: Uri::root() . 'media/templates/administrator/atum/images/logos/brand-large.svg';
$loginLogo = $this->params->get('loginLogo')
	? Uri::root() . $this->params->get('loginLogo')
	: Uri::root() . 'media/templates/administrator/atum/images/logos/login.svg';
$logoBrandSmall = $this->params->get('logoBrandSmall')
	? Uri::root() . htmlspecialchars($this->params->get('logoBrandSmall'), ENT_QUOTES)
	: Uri::root() . 'media/templates/administrator/atum/images/logos/brand-small.svg';

$logoBrandLargeAlt = empty($this->params->get('logoBrandLargeAlt')) && empty($this->params->get('emptyLogoBrandLargeAlt'))
	? 'alt=""'
	: 'alt="' . htmlspecialchars($this->params->get('logoBrandLargeAlt'), ENT_COMPAT, 'UTF-8') . '"';
$logoBrandSmallAlt = empty($this->params->get('logoBrandSmallAlt')) && empty($this->params->get('emptyLogoBrandSmallAlt'))
	? 'alt=""'
	: 'alt="' . htmlspecialchars($this->params->get('logoBrandSmallAlt'), ENT_COMPAT, 'UTF-8') . '"';
$loginLogoAlt = empty($this->params->get('loginLogoAlt')) && empty($this->params->get('emptyLoginLogoAlt'))
	? 'alt=""'
	: 'alt="' . htmlspecialchars($this->params->get('loginLogoAlt'), ENT_COMPAT, 'UTF-8') . '"';

// Get the hue value
preg_match('#^hsla?\(([0-9]+)[\D]+([0-9]+)[\D]+([0-9]+)[\D]+([0-9](?:.\d+)?)?\)$#i', $this->params->get('hue', 'hsl(214, 63%, 20%)'), $matches);

// Enable assets
$wa->usePreset('template.atum.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr'))
	->useStyle('template.active.language')
	->useStyle('template.user')
	->addInlineStyle(':root {
		--hue: ' . $matches[1] . ';
		--template-bg-light: ' . $this->params->get('bg-light', '#f0f4fb') . ';
		--template-text-dark: ' . $this->params->get('text-dark', '#495057') . ';
		--template-text-light: ' . $this->params->get('text-light', '#ffffff') . ';
		--template-link-color: ' . $this->params->get('link-color', '#2a69b8') . ';
		--template-special-color: ' . $this->params->get('special-color', '#001B4C') . ';
	}');

// Override 'template.active' asset to set correct ltr/rtl dependency
$wa->registerStyle('template.active', '', [], [], ['template.atum.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr')]);

// Set some meta data
$this->setMetaData('viewport', 'width=device-width, initial-scale=1');

$monochrome = (bool) $this->params->get('monochrome');

// Add cookie alert message
Text::script('JGLOBAL_WARNCOOKIES');

// @see administrator/templates/atum/html/layouts/status.php
$statusModules = LayoutHelper::render('status', ['modules' => 'status']);

HTMLHelper::_('bootstrap.dropdown');

?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="metas" />
	<jdoc:include type="styles" />
	<jdoc:include type="scripts" />
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

	<header id="header" class="header d-flex">
		<div class="header-title d-flex">
			<div class="d-flex align-items-center">
				<div class="logo">
					<img src="<?php echo $logoBrandLarge; ?>" <?php echo $logoBrandLargeAlt; ?>>
					<img class="logo-collapsed" src="<?php echo $logoBrandSmall; ?>" <?php echo $logoBrandSmallAlt; ?>>
				</div>
			</div>
			<jdoc:include type="modules" name="title" />
		</div>
		<?php echo $statusModules; ?>
	</header>

	<div id="wrapper" class="wrapper flex-grow-1">
		<div class="container-fluid container-main">
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
				</main>
			</section>
		</div>

		<div id="sidebar-wrapper" class="sidebar-wrapper px-3 pb-3">
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
</body>
</html>
