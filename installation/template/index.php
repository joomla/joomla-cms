<?php
/**
 * @package	Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Version;

/** @var JDocumentHtml $this */

// Add Stylesheets
// Load the template CSS file
HTMLHelper::_('stylesheet', 'template' . ($this->direction === 'rtl' ? '-rtl' : '') . '.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'installation/template/css/joomla-alert.min.css', ['version' => 'auto']);

// Add scripts
HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('script', 'installation/template/js/template.js', ['version' => 'auto']);
HTMLHelper::_('webcomponent', 'vendor/joomla-custom-elements/joomla-alert.min.js', ['version' => 'auto', 'relative' => true]);

// Add script options
$this->addScriptOptions('system.installation', ['url' => Route::_('index.php')]);

// Load JavaScript message titles
Text::script('ERROR');
Text::script('WARNING');
Text::script('NOTICE');
Text::script('MESSAGE');

// Add strings for JavaScript error translations.
Text::script('JLIB_JS_AJAX_ERROR_CONNECTION_ABORT');
Text::script('JLIB_JS_AJAX_ERROR_NO_CONTENT');
Text::script('JLIB_JS_AJAX_ERROR_OTHER');
Text::script('JLIB_JS_AJAX_ERROR_PARSE');
Text::script('JLIB_JS_AJAX_ERROR_TIMEOUT');

// Load the JavaScript translated messages
Text::script('INSTL_PROCESS_BUSY');
Text::script('INSTL_FTP_SETTINGS_CORRECT');

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
			<header id="header" class="header">
				<div class="d-flex align-items-center">
					<div class="header-title d-flex mr-auto">
						<div class="d-flex">
							<a class="logo" href="<?php echo Route::_('index.php'); ?>">
								<img src="<?php echo $this->baseurl; ?>/template/images/logo-joomla-blue.svg" alt=""/>
							</a>
						</div>
						<div class="d-flex px-2">
							<div class="container-title">
								<h1 class="page-title">
									<span class="fa fa-cogs"></span>
									<?php echo Text::_('INSTL_PAGE_TITLE'); ?>
								</h1>
							</div>
						</div>
					</div>
					<div class="header-items d-flex ml-auto">
						<div class="d-flex align-items-center">
							<p class="joomla-version w-100 text-right">
								Joomla! <?php echo (new Version)->getShortVersion(); ?>
							</p>
						</div>
						<div class="ml-auto">
							<ul class="nav text-center">
								<li class="nav-item">
									<a class="nav-link" href="https://docs.joomla.org/Special:MyLanguage/J4.x:Installing_Joomla" target="_blank">
										<span class="fa fa-question" aria-hidden="true"></span>
										<span class="sr-only"><?php echo Text::_('INSTL_HELP_LINK'); ?></span>
									</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</header>
			<?php // Container ?>
			<div id="wrapper" class="d-flex wrapper flex-wrap">
				<div class="container-fluid container-main">
					<div id="content" class="content h-100">
						<main class="d-flex justify-content-center align-items-center h-100">
							<div class="j-container">
								<jdoc:include type="message" />
								<div id="javascript-warning">
									<noscript>
										<?php echo Text::_('INSTL_WARNJAVASCRIPT'); ?>
									</noscript>
								</div>
								<div id="container-installation" class="container-installation flex no-js" data-base-url="<?php echo JUri::root(); ?>" style="display:none">
									<jdoc:include type="component" />
								</div>
							</div>
						</main>
						<footer class="text-center small w-100">
							<?php echo Version::URL; ?>
						</footer>
					</div>
				</div>
			</div>
			<jdoc:include type="scripts" />
		</div>
	</body>
</html>
