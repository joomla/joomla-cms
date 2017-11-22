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
		<title>
			<?php echo $this->title; ?>
			<?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?>
		</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<link href="<?php echo $this->baseurl; ?>/template/css/template.css" rel="stylesheet" />
	</head>
	<body>
		<div class="j-install">
			<header class="j-header" role="banner">
				<div class="j-header-logo">
					<img src="<?php echo $this->baseurl; ?>/template/images/logo.svg" alt="<?php echo JText::_('INSTL_JOOMLA_LOGO'); ?>" class="logo" />
				</div>
				<div class="j-header-help">
					<a href="#">
						<span class="fa fa-lightbulb-o" aria-hidden="true"></span>
					</a>
				</div>
			</header>
			<section class="j-container">
				<div id="system-message-container">
					<jdoc:include type="message" />
				</div>
				<noscript>
					<div id="javascript-warning">
						<joomla-alert level="danger text-center">
							<?php echo JText::_('INSTL_WARNJAVASCRIPT'); ?>
						</joomla-alert>
					</div>
				</noscript>
				<div class="container-installation flex">
					<div class="j-install-step active">
						<div class="j-install-step-header">
							<span class="fa fa-exclamation" aria-hidden="true"></span>
							<?php echo JText::_('INSTL_ERROR'); ?>
						</div>
						<div class="j-install-step-form">
							<div class="alert preinstall-alert">
								<div class="alert-icon">
									<span class="alert-icon fa fa-exclamation-triangle"></span>
								</div>
								<div class="alert-text">
									<h2>
										<?php echo JText::_('JERROR_LAYOUT_ERROR_HAS_OCCURRED_WHILE_PROCESSING_YOUR_REQUEST'); ?>
									</h2>
									<p class="form-text text-muted small">
										<?php echo $this->error->getCode(); ?>
										<?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?>
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
			<footer class="j-footer">
				<?php echo JText::sprintf('INSTL_JOOMLA_IS_FREE_SOFTWARE', 'https://www.joomla.org', 'https://www.gnu.org/licenses/old-licenses/gpl-2.0.html'); ?>
			</footer>
		</div>
	</body>
</html>
