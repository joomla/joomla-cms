<?php
/**
 * @package	Joomla.Installation
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var JDocumentError $this */

// Add required assets
$this->getWebAssetManager()
	->registerAndUseStyle('template.installation', 'template' . ($this->direction === 'rtl' ? '-rtl' : '') . '.css')
	->useScript('core')
	->registerAndUseScript('template.installation', 'installation/template/js/template.js', [], [], ['core']);

$this->getWebAssetManager()
	->useStyle('webcomponent.joomla-alert')
	->useScript('messages');

// Add script options
$this->addScriptOptions('system.installation', ['url' => Route::_('index.php')]);

// Set page title
$this->setTitle($this->error->getCode() . ' - ' . htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'));

$this->setMetaData('viewport', 'width=device-width, initial-scale=1');

?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
	<head>
		<jdoc:include type="metas" />
		<jdoc:include type="styles" />
	</head>
	<body>
		<div class="j-install">
			<!-- Header -->
			<header class="j-header" role="banner">
				<div class="j-header-logo">
					<img src="<?php echo $this->baseurl; ?>/template/images/logo.svg" alt="" class="logo"/>
				</div>
				<div class="j-header-help">
					<a href="https://docs.joomla.org/Special:MyLanguage/J4.x:Installing_Joomla">
						<span class="icon-lightbulb" aria-hidden="true"></span>
						<span class="visually-hidden"><?php echo Text::_('INSTL_HELP_LINK'); ?></span>
					</a>
				</div>
			</header>
			<!-- Container -->
			<section class="j-container">
				<jdoc:include type="message" />
				<div id="javascript-warning">
					<noscript>
						<?php echo Text::_('INSTL_WARNJAVASCRIPT'); ?>
					</noscript>
				</div>
				<div id="container-installation" class="container-installation flex">
					<div class="j-install-step active">
						<div class="j-install-step-header">
							<span class="icon-exclamation" aria-hidden="true"></span> <?php echo Text::_('INSTL_ERROR'); ?>
						</div>
						<div class="j-install-step-form">
							<div class="alert preinstall-alert">
								<div class="alert-icon">
									<span class="alert-icon icon-exclamation-triangle" aria-hidden="true"></span>
								</div>
								<div class="alert-text">
									<h2><?php echo Text::_('JERROR_LAYOUT_ERROR_HAS_OCCURRED_WHILE_PROCESSING_YOUR_REQUEST'); ?></h2>
									<p class="form-text small"><span class="badge bg-secondary"><?php echo $this->error->getCode(); ?></span> <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></p>
								</div>
							</div>
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
				</div>
			</section>
			<jdoc:include type="scripts" />
			<footer class="j-footer">
				<a href="https://www.joomla.org" target="_blank">Joomla!</a>
				is free software released under the
				<a href="https://www.gnu.org/licenses/old-licenses/gpl-2.0.html" target="_blank" rel="noopener noreferrer">GNU General Public License</a>
			</footer>
		</div>
	</body>
</html>
