<?php
/**
 * @package	Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var JDocumentError $this */
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
	<head>
		<title><?php echo $this->title; ?> <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!--		<link href="--><?php //echo JUri::root(true); ?><!--/media/jui/css/bootstrap.min.css" rel="stylesheet">-->
		<?php if ($this->direction == 'rtl') : ?>
<!--			<link href="--><?php //echo JUri::root(true); ?><!--/media/jui/css/bootstrap-rtl.css" rel="stylesheet">-->
		<?php endif; ?>
		<link href="<?php echo $this->baseurl; ?>/template/css/template.css" rel="stylesheet">
	</head>
	<body>
		<div class="j-install">
			<!-- Header -->
			<header class="j-header" role="banner">
				<div class="j-header-logo">
					<img src="<?php echo $this->baseurl; ?>/template/images/logo.svg" alt="Joomla" class="logo"/>
				</div>
				<div class="j-header-help">
					<a href="#">
						<span class="fa fa-lightbulb-o" aria-hidden="true"></span>
					</a>
				</div>
			</header>
			<!-- Container -->
			<section class="j-container">
				<div id="system-message-container">
					<jdoc:include type="message" />
				</div>
				<div id="javascript-warning">
					<noscript>
						<joomla-alert level="danger text-center">
							<?php echo JText::_('INSTL_WARNJAVASCRIPT'); ?>
						</joomla-alert>
					</noscript>
				</div>
				<div class="container-installation flex">
					<div class="j-install-step active">
						<div class="j-install-step-header">
							<span class="fa fa-exclamation" aria-hidden="true"></span> <?php echo JText::_('INSTL_ERROR'); ?>
						</div>
						<div class="j-install-step-form">
							<div class="alert preinstall-alert">
								<div class="alert-icon">
									<span class="alert-icon fa fa-exclamation-triangle"></span>
								</div>
								<div class="alert-text">
									<h2><?php echo JText::_('JERROR_LAYOUT_ERROR_HAS_OCCURRED_WHILE_PROCESSING_YOUR_REQUEST'); ?></h2>
									<p class="form-text text-muted small"><?php echo $this->error->getCode(); ?></span> <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></p>
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
											<p><strong><?php echo JText::_('JERROR_LAYOUT_PREVIOUS_ERROR'); ?></strong></p>
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
			<footer class="j-footer">
				<a href="https://www.joomla.org" target="_blank">Joomla!</a>
				is free software released under the
				<a href="https://www.gnu.org/licenses/old-licenses/gpl-2.0.html" target="_blank" rel="noopener noreferrer">GNU General Public License</a>
			</footer>
		</div>
	</body>
</html>
