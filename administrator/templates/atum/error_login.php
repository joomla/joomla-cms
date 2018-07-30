<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Helper\ModuleHelper;

/** @var JDocumentError $this */

$app  = Factory::getApplication();
$lang = Factory::getLanguage();

// Add JavaScript Frameworks
HTMLHelper::_('script', 'vendor/focus-visible/focus-visible.min.js', ['version' => 'auto', 'relative' => true]);

// Load template CSS file
HTMLHelper::_('stylesheet', 'bootstrap.min.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'font-awesome.min.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'template' . ($this->direction === 'rtl' ? '-rtl' : '') . '.min.css', ['version' => 'auto', 'relative' => true]);

// Alerts
HTMLHelper::_('webcomponent', 'vendor/joomla-custom-elements/joomla-alert.min.js', ['relative' => true, 'version' => 'auto']);


// Load custom CSS file
HTMLHelper::_('stylesheet', 'user.css', array('version' => 'auto', 'relative' => true));

// Load specific language related CSS
HTMLHelper::_('stylesheet', 'administrator/language/' . $lang->getTag() . '/' . $lang->getTag() . '.css', array('version' => 'auto'));

// Detecting Active Variables
$option   = $app->input->getCmd('option', '');
$view     = $app->input->getCmd('view', '');
$layout   = $app->input->getCmd('layout', '');
$task     = $app->input->getCmd('task', '');
$itemid   = $app->input->getCmd('Itemid', '');
$sitename = $app->get('sitename');

// Set some meta data
$this->setMetaData('viewport', 'width=device-width, initial-scale=1');
// @TODO sync with _variables.scss
$this->setMetaData('theme-color', '#1c3d5c');

?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="metas" />
	<jdoc:include type="styles" />
</head>

<body class="admin <?php echo $option . ' view-' . $view . ' layout-' . $layout . ' task-' . $task . ' itemid-' . $itemid . ' '; ?>">
	<?php // Container ?>
	<div class="d-flex justify-content-center align-items-center h-100">
		<div class="login">
			<div class="login-logo">
				<img class="card-img-top" src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/images/logo.svg" alt="<?php echo $sitename; ?>">
			</div>
			<div id="content">
				<noscript>
					<div class="alert alert-danger" role="alert">
						<?php echo Text::_('JGLOBAL_WARNJAVASCRIPT'); ?>
					</div>
				</noscript>
				<?php // Begin Content ?>
				<div id="element-box" class="card">
					<div class="card-body">
						<h1 class="text-center mt-1 mb-4"><?php echo Text::_('JERROR_AN_ERROR_HAS_OCCURRED'); ?></h1>
						<jdoc:include type="message" />
						<blockquote class="blockquote">
							<span class="badge badge-secondary"><?php echo $this->error->getCode(); ?></span> <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?>
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
				<?php // End Content ?>
			</div>
		</div>
	</div>

	<div class="fixed-bottom px-3 mb-2 d-none d-md-block">
		<div class="row nav align-items-center">
			<div class="col">
				<a href="<?php echo Uri::root(); ?>" target="_blank"><span class="fa fa-external-link mr-1" aria-hidden="true"></span><?php echo Text::_('COM_LOGIN_RETURN_TO_SITE_HOME_PAGE'); ?></a>
			</div>
			<div class="col text-center">
				<a href="https://www.joomla.org" target="_blank" title="<?php echo Text::_('TPL_ATUM_ISFREESOFTWARE'); ?>">
					<span class="fa fa-2x fa-joomla" aria-hidden="true"></span>
					<span class="sr-only"><?php echo Text::_('TPL_ATUM_GOTO_JOOMLA_HOME_PAGE'); ?></span>
				</a>
			</div>
			<div class="col text-right">
				<span class="text-white">&nbsp;&copy; <?php echo date('Y'); ?> <?php echo $sitename; ?></span>
			</div>
		</div>
	</div>

	<jdoc:include type="modules" name="debug" style="none" />

	<jdoc:include type="scripts" />
</body>
</html>
