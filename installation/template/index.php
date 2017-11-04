<?php
/**
 * @package	Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var JDocumentHtml $this */

// Add Stylesheets
JHtml::_('stylesheet', 'installation/template/css/template.css', ['version' => 'auto']);
JHtml::_('stylesheet', 'media/vendor/font-awesome/css/font-awesome.min.css', ['version' => 'auto']);
JHtml::_('stylesheet', 'installation/template/css/joomla-alert.min.css', ['version' => 'auto']);

// Add scripts
JHtml::_('behavior.core');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');
JHtml::_('script', 'installation/template/js/template.js', ['version' => 'auto']);
JHtml::_('webcomponent', ['joomla-alert' => 'system/joomla-alert.min.js'], ['version' => 'auto', 'relative' => true]);

// Add script options
$this->addScriptOptions('system.installation', ['url' => JRoute::_('index.php')]);

// Load JavaScript message titles
JText::script('ERROR');
JText::script('WARNING');
JText::script('NOTICE');
JText::script('MESSAGE');

// Add strings for JavaScript error translations.
JText::script('JLIB_JS_AJAX_ERROR_CONNECTION_ABORT');
JText::script('JLIB_JS_AJAX_ERROR_NO_CONTENT');
JText::script('JLIB_JS_AJAX_ERROR_OTHER');
JText::script('JLIB_JS_AJAX_ERROR_PARSE');
JText::script('JLIB_JS_AJAX_ERROR_TIMEOUT');

// Load the JavaScript translated messages
JText::script('INSTL_PROCESS_BUSY');
JText::script('INSTL_FTP_SETTINGS_CORRECT');
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
	<head>
		<jdoc:include type="metas" />
		<jdoc:include type="styles" />
	</head>
	<body data-basepath="<?php echo JUri::root(true); ?>">
		<div class="j-install">
			<?php // Header ?>
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
			<?php // Container ?>
			<section class="j-container" role="main">
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
				<div id="container-installation" class="container-installation flex no-js" data-base-url="<?php echo JUri::root(); ?>" style="display:none">
					<jdoc:include type="component" />
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
