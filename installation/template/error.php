<?php
/**
 * @package	Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
		<link href="<?php echo JUri::root(true); ?>/media/jui/css/bootstrap.min.css" rel="stylesheet" />
		<?php if ($this->direction == 'rtl') : ?>
			<link href="<?php echo JUri::root(true); ?>/media/jui/css/bootstrap-rtl.css" rel="stylesheet" />
		<?php endif; ?>
		<link href="<?php echo $this->baseurl; ?>/template/css/template.css" rel="stylesheet" />
		<!--[if lt IE 9]><script src="<?php echo JUri::root(true); ?>/media/jui/js/html5.js"></script><![endif]-->
	</head>
	<body>
		<!-- Header -->
		<div class="header">
			<img src="<?php echo $this->baseurl; ?>/template/images/joomla.png" alt="Joomla">
			<hr>
			<h5>
				<?php // Fix wrong display of Joomla!® in RTL language ?>
				<?php $joomla  = '<a href="https://www.joomla.org" target="_blank">Joomla!</a><sup>' . ($this->direction === 'rtl' ? '&#x200E;' : '') . '</sup>'; ?>
				<?php $license = '<a href="http://www.gnu.org/licenses/old-licenses/gpl-2.0.html" target="_blank">' . JText::_('INSTL_GNU_GPL_LICENSE') . '</a>'; ?>
				<?php echo JText::sprintf('JGLOBAL_ISFREESOFTWARE', $joomla, $license); ?>
			</h5>
		</div>
		<!-- Container -->
		<div class="container">
			<jdoc:include type="message" />
			<div id="javascript-warning">
				<noscript>
					<div class="alert alert-error">
						<?php echo JText::_('INSTL_WARNJAVASCRIPT'); ?>
					</div>
				</noscript>
			</div>
			<div id="container-installation">
				<h1 class="page-header"><?php echo JText::_('JERROR_LAYOUT_ERROR_HAS_OCCURRED_WHILE_PROCESSING_YOUR_REQUEST'); ?></h1>
				<div class="well">
					<blockquote>
						<span class="label label-inverse"><?php echo $this->error->getCode(); ?></span> <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8');?>
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
			<hr>
		</div>
	</body>
</html>
