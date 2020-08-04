<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var \Joomla\CMS\Document\ErrorDocument $this */

$app   = Factory::getApplication();
$input = $app->input;
$wa    = $this->getWebAssetManager();

// Detecting Active Variables
$option     = $input->get('option', '');
$view       = $input->get('view', '');
$layout     = $input->get('layout', 'default');
$task       = $input->get('task', 'display');
$cpanel     = $option === 'com_cpanel';
$hiddenMenu = $app->input->get('hidemainmenu');

require_once __DIR__ . '/Service/HTML/Atum.php';

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

$logoBrandLargeAlt = htmlspecialchars($this->params->get('logoBrandLargeAlt', ''), ENT_COMPAT, 'UTF-8');
$logoBrandSmallAlt = htmlspecialchars($this->params->get('logoBrandSmallAlt', ''), ENT_COMPAT, 'UTF-8');

// Enable assets
$wa->usePreset('template.atum.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr'))
	->useStyle('template.active.language')
	->useStyle('template.user');

// Override 'template.active' asset to set correct ltr/rtl dependency
$wa->registerStyle('template.active', '', [], [], ['template.atum.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr')]);

// Set some meta data
$this->setMetaData('viewport', 'width=device-width, initial-scale=1');
// @TODO sync with _variables.scss
$this->setMetaData('theme-color', '#1c3d5c');

$monochrome = (bool) $this->params->get('monochrome');

$htmlRegistry = HTMLHelper::getServiceRegistry();

// This may already be registered in main template file.
if (!$htmlRegistry->hasService('atum'))
{
	$htmlRegistry->register('atum', 'JHtmlAtum');
}

HTMLHelper::_('atum.rootcolors', $this->params);

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

<?php // Header ?>
<header id="header" class="header">
	<div class="d-flex">
		<div class="header-title d-flex">
			<div class="d-flex align-items-center">
				<a class="logo" href="<?php echo Route::_('index.php'); ?>"
				   aria-label="<?php echo Text::_('TPL_ATUM_BACK_TO_CONTROL_PANEL'); ?>">
					<img src="<?php echo $logoBrandLarge; ?>" alt="<?php echo $logoBrandLargeAlt; ?>">
					<img class="logo-collapsed" src="<?php echo $logoBrandSmall; ?>" alt="<?php echo $logoBrandSmallAlt; ?>">
				</a>
			</div>
			<jdoc:include type="modules" name="title" />
		</div>
		<div class="header-items d-flex">
			<jdoc:include type="modules" name="status" style="header-item" />
		</div>
	</div>
</header>

<?php // Wrapper ?>
<div id="wrapper" class="d-flex wrapper<?php echo $hiddenMenu ? '0' : ''; ?>">

	<?php // Sidebar ?>
	<?php if (!$hiddenMenu) : ?>
		<button class="navbar-toggler toggler-burger collapsed" type="button" data-toggle="collapse" data-target="#sidebar-wrapper" aria-controls="sidebar-wrapper" aria-expanded="false" aria-label="<?php echo Text::_('JTOGGLE_SIDEBAR_MENU'); ?>">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div id="sidebar-wrapper" class="sidebar-wrapper sidebar-menu" <?php echo $hiddenMenu ? 'data-hidden="' . $hiddenMenu . '"' : ''; ?>>
			<div id="sidebarmenu">
				<div class="sidebar-toggle item item-level-1">
					<a id="menu-collapse" href="#" aria-label="<?php echo Text::_('JTOGGLE_SIDEBAR_MENU'); ?>">
						<span id="menu-collapse-icon" class="fas fa-toggle-off fa-fw" aria-hidden="true"></span>
						<span class="sidebar-item-title"><?php echo Text::_('JTOGGLE_SIDEBAR_MENU'); ?></span>
					</a>
				</div>
				<jdoc:include type="modules" name="menu" style="none" />
			</div>
		</div>
	<?php endif; ?>

	<?php // container-fluid ?>
	<div class="container-fluid container-main">
		<?php if (!$cpanel) : ?>
			<?php // Subheader ?>
			<a class="btn btn-subhead d-md-none d-lg-none d-xl-none" data-toggle="collapse"
			   data-target=".subhead-collapse"><?php echo Text::_('TPL_ATUM_TOOLBAR'); ?>
				<span class="fas fa-wrench"></span></a>
			<div id="subhead" class="subhead mb-3">
				<div id="container-collapse" class="container-collapse"></div>
				<div class="row">
					<div class="col-md-12">
						<jdoc:include type="modules" name="toolbar" style="no" />
					</div>
				</div>
			</div>
		<?php endif; ?>
		<section id="content" class="content">
			<?php // Begin Content ?>
			<jdoc:include type="message" />
			<jdoc:include type="modules" name="top" style="xhtml" />
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
							<span class="fas fa-dashboard" aria-hidden="true"></span>
							<?php echo Text::_('JGLOBAL_TPL_CPANEL_LINK_TEXT'); ?></a>
					</p>
				</div>

				<?php if ($this->countModules('bottom')) : ?>
					<jdoc:include type="modules" name="bottom" style="xhtml" />
				<?php endif; ?>
			</div>
			<?php // End Content ?>
		</section>
	</div>
</div>
<jdoc:include type="modules" name="debug" style="none" />
<jdoc:include type="scripts" />
</body>
</html>
