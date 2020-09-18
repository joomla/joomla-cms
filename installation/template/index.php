<?php
/**
 * @package	Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;

/** @var JDocumentHtml $this */

// Add required assets
$this->getWebAssetManager()
	->registerAndUseStyle('template.installation', 'template' . ($this->direction === 'rtl' ? '-rtl' : '') . '.css')
	->useScript('core')
	->useScript('keepalive')
	->useScript('form.validate')
	->registerAndUseScript('template.installation', 'installation/template/js/template.js', [], [], ['core', 'form.validate']);

$this->getWebAssetManager()
	->useStyle('webcomponent.joomla-alert')
	->useScript('webcomponent.joomla-alert')
	->useScript('webcomponent.core-loader');


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
Text::script('INSTL_DATABASE_RESPONSE_ERROR');

// Load the JavaScript translated messages
Text::script('INSTL_PROCESS_BUSY');
Text::script('INSTL_FTP_SETTINGS_CORRECT');

// Load strings for translated messages (directory removal)
Text::script('INSTL_REMOVE_INST_FOLDER');
Text::script('INSTL_COMPLETE_REMOVE_FOLDER');
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<jdoc:include type="metas" />
		<jdoc:include type="styles" />
	</head>
	<body data-basepath="<?php echo Uri::root(true); ?>">
		<div class="j-install">
			<?php // Header ?>
			<header id="header" class="header">
				<div class="d-flex ">
					<div class="logo d-none d-md-block">
						<img src="<?php echo $this->baseurl; ?>/template/images/logo-joomla-blue.svg" alt="">
					</div>
					<div class="mx-2 my-3 d-flex d-md-none">
						<img class="logo-small d-flex d-md-none" src="<?php echo $this->baseurl; ?>/template/images/logo-blue.svg" alt="">
					</div>
					<div class="d-flex flex-wrap align-items-center mx-auto">
						<h1 class="h2 mx-1 d-flex align-items-baseline">
							<span class="fas fa-cogs d-none d-md-block mx-2 alig-items-center" aria-hidden="true"></span>
							<?php echo Text::_('INSTL_PAGE_TITLE'); ?>
						</h1>
						<span class="small mx-1">
							Joomla! <?php echo (new Version)->getShortVersion(); ?>
						</span>
					</div>
					<div class="m-2 d-flex align-items-center">
						<a href="https://docs.joomla.org/Special:MyLanguage/J4.x:Installing_Joomla" target="_blank">
							<span class="fas fa-question" aria-hidden="true"></span>
							<span class="sr-only"><?php echo Text::_('INSTL_HELP_LINK'); ?></span>
						</a>
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
								<div id="container-installation" class="container-installation flex no-js hidden" data-base-url="<?php echo Uri::root(); ?>">
									<jdoc:include type="component" />
								</div>
							</div>
						</main>
						<footer class="footer text-center small w-100 p-3">
							<?php echo Version::URL; ?>
						</footer>
					</div>
				</div>
			</div>
			<jdoc:include type="scripts" />
		</div>
	</body>
</html>
