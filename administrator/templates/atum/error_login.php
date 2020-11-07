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
use Joomla\CMS\Uri\Uri;

/** @var \Joomla\CMS\Document\ErrorDocument $this */

$app   = Factory::getApplication();
$input = $app->input;
$wa    = $this->getWebAssetManager();

// Detecting Active Variables
$option = $input->get('option', '');
$view   = $input->get('view', '');
$layout = $input->get('layout', 'default');
$task   = $input->get('task', 'display');

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

$htmlHelperRegistry = HTMLHelper::getServiceRegistry();

// We may have registered this trying to load the main login page - so check before registering again
if (!$htmlHelperRegistry->hasService('atum'))
{
	$htmlHelperRegistry->register('atum', 'JHtmlAtum');
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

<header id="header" class="header">
	<div class="d-flex">
		<div class="header-title d-flex mr-auto">
			<div class="d-flex align-items-center">
				<?php // No home link in edit mode (so users can not jump out) and control panel (for a11y reasons) ?>
				<div class="logo">
					<img src="<?php echo $logoBrandLarge; ?>" alt="<?php echo $logoBrandLargeAlt; ?>">
					<img class="logo-collapsed" src="<?php echo $logoBrandSmall; ?>" alt="<?php echo $logoBrandSmallAlt; ?>">
				</div>
			</div>
			<jdoc:include type="modules" name="title"/>
		</div>
		<div class="header-items d-flex ml-auto">
			<jdoc:include type="modules" name="status" style="header-element"/>
		</div>
	</div>
</header>

<div id="wrapper" class="d-flex wrapper">

	<?php // Sidebar ?>
	<div id="sidebar-wrapper" class="sidebar-wrapper">
		<div id="main-brand" class="main-brand">
			<h2><?php echo $app->get('sitename'); ?></h2>
			<a href="<?php echo Uri::root(); ?>"><?php echo Text::_('TPL_ATUM_LOGIN_SIDEBAR_VIEW_WEBSITE'); ?></a>
		</div>
		<div id="sidebar">
			<jdoc:include type="modules" name="sidebar" style="body" />
		</div>
	</div>

	<div class="container-fluid container-main">
		<section id="content" class="content h-100">
			<?php // Begin Content ?>
			<main class="d-flex justify-content-center align-items-center h-100">
				<div id="element-box" class="card">
					<div class="card-body">
						<div class="main-brand d-flex align-items-center justify-content-center">
							<img src="<?php echo $loginLogo; ?>" alt="">
						</div>
						<h1><?php echo Text::_('JERROR_AN_ERROR_HAS_OCCURRED'); ?></h1>
						<jdoc:include type="message" />
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
					</div>
				</div>
			</main>
			<?php // End Content ?>
		</section>
	</div>
</div>
<jdoc:include type="modules" name="debug" style="none" />
<jdoc:include type="scripts" />
</body>
</html>
