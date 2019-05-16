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
use Joomla\CMS\Uri\Uri;

/** @var JDocumentError $this */

$app  = Factory::getApplication();
$lang = $app->getLanguage();

// Add JavaScript Frameworks
HTMLHelper::_('script', 'vendor/focus-visible/focus-visible.min.js', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('script', 'vendor/css-vars-ponyfill/css-vars-ponyfill.min.js', ['version' => 'auto', 'relative' => true]);

// Load template CSS file
HTMLHelper::_('stylesheet', 'bootstrap.min.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'fontawesome.min.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'template' . ($this->direction === 'rtl' ? '-rtl' : '') . '.min.css', ['version' => 'auto', 'relative' => true]);

// Load custom CSS file
HTMLHelper::_('stylesheet', 'user.css', ['version' => 'auto', 'relative' => true]);

// Load specific language related CSS
HTMLHelper::_('stylesheet', 'administrator/language/' . $lang->getTag() . '/' . $lang->getTag() . '.css', ['version' => 'auto']);

// Detecting Active Variables
$option   = $app->input->getCmd('option', '');
$view     = $app->input->getCmd('view', '');
$layout   = $app->input->getCmd('layout', 'default');
$sitename = htmlspecialchars($app->get('sitename'), ENT_QUOTES, 'UTF-8');

// Logos (params are not available)
$siteLogo  = $this->baseurl . '/templates/' . $this->template . '/images/logo-joomla-blue.svg';
$loginLogo = $this->baseurl . '/templates/' . $this->template . '/images/logo-blue.svg';

// Set some meta data
$this->setMetaData('viewport', 'width=device-width, initial-scale=1');
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
			</div>
		</div>
	</div>
</header>

<div id="wrapper" class="d-flex wrapper">

	<?php // Sidebar ?>
	<div id="sidebar-wrapper" class="sidebar-wrapper">
		<div id="main-brand" class="main-brand">
			<h2><?php echo $sitename; ?></h2>
			<a href="<?php echo Uri::root(); ?>"><?php echo Text::_('TPL_ATUM_LOGIN_SIDEBAR_VIEW_WEBSITE'); ?></a>
		</div>
		<div id="sidebar">
			<jdoc:include type="modules" name="sidebar" style="body"/>
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
						<jdoc:include type="message"/>
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
<jdoc:include type="modules" name="debug" style="none"/>
<jdoc:include type="scripts"/>
</body>
</html>
