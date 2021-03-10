<?php
/**
 * @package	Joomla.Installation
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
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
				<div class="d-flex align-items-center">
					<div class="logo d-none d-md-block col">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 376.3 74.8" x="0" y="0" width="114px" height="20px" transform="translate(10 0) scale(1.3)" role="presentation" focusable="false">
							<g>
								<path fill="#fff" d="M116.4 14.8v31.3c0 2.8.2 5.4-2.3 7.3-2.3 1.9-6.2 2.5-10.4 2.5-6.4 0-13.2-1.5-13.2-1.5l-1.5 4s9.5 2 16.4 2.1c5.8.1 10.9-1.2 13.7-4.4 2.3-2.6 3-5.6 2.9-10.7V14.8h-5.6M163.1 32.1c-4.2-2.3-9.3-3.5-15.1-3.5-5.7 0-10.8 1.2-15.1 3.5-5.4 2.9-8.1 7.2-8.1 12.6 0 5.4 2.7 9.7 8.1 12.6 4.3 2.3 9.3 3.5 15.1 3.5 5.7 0 10.8-1.2 15-3.5 5.4-2.9 8.1-7.2 8.1-12.6.1-5.5-2.7-9.7-8-12.6m-3.3 22c-3.3 1.9-7.2 2.8-11.8 2.8-4.7 0-8.7-.9-11.9-2.7-3.9-2.2-5.8-5.3-5.8-9.5 0-4.1 2-7.3 5.8-9.5 3.2-1.8 7.2-2.7 11.9-2.7 4.6 0 8.6.9 11.9 2.7 3.8 2.2 5.8 5.4 5.8 9.5 0 4-2 7.2-5.9 9.4zM212.3 32.1c-4.2-2.3-9.3-3.5-15.1-3.5-5.7 0-10.8 1.2-15.1 3.5-5.4 2.9-8.1 7.2-8.1 12.6 0 5.4 2.7 9.7 8.1 12.6 4.3 2.3 9.3 3.5 15.1 3.5 5.7 0 10.8-1.2 15-3.5 5.4-2.9 8.1-7.2 8.1-12.6.1-5.5-2.6-9.7-8-12.6m-3.2 22c-3.3 1.9-7.2 2.8-11.8 2.8-4.7 0-8.7-.9-11.9-2.7-3.9-2.2-5.8-5.3-5.8-9.5 0-4.1 2-7.3 5.8-9.5 3.2-1.8 7.2-2.7 11.9-2.7 4.6 0 8.6.9 11.9 2.7 3.8 2.2 5.8 5.4 5.8 9.5-.1 4-2.1 7.2-5.9 9.4zM280.2 31.3c-3-1.8-6.9-2.7-11.5-2.7-5.9 0-10.6 1.8-14.2 5.4-3.4-3.6-8.2-5.4-14.1-5.4-4.8 0-8.7 1-11.7 2.9V29h-5.3v31.1h5.3V38.9c.4-1.5 1.4-2.9 3-4.1 2.2-1.5 5-2.3 8.5-2.3 3.1 0 5.7.6 7.9 1.9 2.6 1.5 3.8 3.5 3.8 6.3v19.6h5.2V40.6c0-2.8 1.2-4.8 3.8-6.3 2.2-1.2 4.9-1.9 8-1.9 3.1 0 5.8.6 8 1.9 2.6 1.5 3.8 3.5 3.8 6.3v19.6h5.3V41.3c-.1-4.4-2-7.8-5.8-10M290.2 14.8v45.4h5.3V14.8h-5.3M354.5 14.8v35.1h5.3V14.8h-5.3M340.7 29v5.3c-4.5-3.8-10.5-5.8-17.9-5.8-5.9 0-11 1.1-15.2 3.4-5.2 2.9-7.9 7.1-7.9 12.7 0 5.5 2.7 9.8 8.1 12.6 4.2 2.3 9.3 3.4 15.2 3.4 2.9 0 5.8-.3 8.4-1 3.7-1 6.8-2.4 9.2-4.3v4.8h5.3V29h-5.2m-35.5 15.7c0-4.1 2-7.3 5.8-9.5 3.2-1.8 7.3-2.7 12-2.7 5.8 0 10.3 1.4 13.5 4.2 2.8 2.5 4.2 5.7 4.2 9.6V50c-2.2 2.5-5.5 4.4-9.7 5.7-2.5.8-5.2 1.2-8 1.2-4.8 0-8.8-.9-12-2.6-3.9-2.3-5.8-5.4-5.8-9.6zM357.2 54.3c-3.7 0-4.2 1.9-4.2 3.1 0 1.2.6 3.1 4.2 3.1 3.7 0 4.2-2 4.2-3.1s-.5-3.1-4.2-3.1zM376.3 20.4c0 3.1-2 5.7-5.6 5.7-3.6 0-5.6-2.6-5.6-5.7s2-5.7 5.6-5.7c3.6 0 5.6 2.6 5.6 5.7zm-10 0c0 2.6 1.6 4.6 4.4 4.6 2.8 0 4.4-2 4.4-4.6 0-2.6-1.6-4.6-4.4-4.6-2.8 0-4.4 2-4.4 4.6zm5.5.7c2.2-.4 2-3.7-.5-3.7h-2.7v5.8h1.1v-2h1l1.6 2h1.2V23l-1.7-1.9zm-.6-2.7c1.3 0 1.3 1.9 0 1.9h-1.6v-1.9h1.6z"></path>
							</g>
							<g>
								<path fill="#fff" d="M13.5 37.7L12 36.3c-4.5-4.5-5.8-10.8-4.2-16.5-4.5-1-7.8-5-7.8-9.8C0 4.5 4.5 0 10 0c5 0 9.1 3.6 9.9 8.4 5.4-1.3 11.3.2 15.5 4.4l.6.6-7.4 7.4-.6-.6c-2.4-2.4-6.3-2.4-8.7 0-2.4 2.4-2.4 6.3 0 8.7l1.4 1.4 7.4 7.4 7.8 7.8-7.4 7.4-7.8-7.8-7.2-7.4z"></path>
								<path fill="#fff" d="M21.8 29.5l7.8-7.8 7.4-7.4 1.4-1.4C42.9 8.4 49.2 7 54.8 8.6c.7-4.8 4.9-8.6 10-8.6 5.5 0 10 4.5 10 10 0 5.1-3.8 9.3-8.7 9.9 1.6 5.6.2 11.9-4.2 16.3l-.6.6-7.4-7.4.6-.6c2.4-2.4 2.4-6.3 0-8.7-2.4-2.4-6.3-2.4-8.7 0l-1.4 1.4L37 29l-7.8 7.8-7.4-7.3z"></path>
								<path fill="#fff" d="M55 66.8c-5.7 1.7-12.1.4-16.6-4.1l-.6-.6 7.4-7.4.6.6c2.4 2.4 6.3 2.4 8.7 0 2.4-2.4 2.4-6.3 0-8.7L53 45.1l-7.4-7.4-7.8-7.8 7.4-7.4 7.8 7.8 7.4 7.4 1.5 1.5c4.2 4.2 5.7 10.2 4.4 15.7 4.9.7 8.6 4.9 8.6 9.9 0 5.5-4.5 10-10 10-4.9 0-8.9-3.5-9.9-8z"></path>
								<path fill="#fff" d="M52.2 46l-7.8 7.8-7.4 7.4-1.4 1.4c-4.3 4.3-10.3 5.7-15.7 4.4-1 4.5-5 7.8-9.8 7.8-5.5 0-10-4.5-10-10C0 60 3.3 56.1 7.7 55c-1.4-5.5.1-11.5 4.3-15.8l.6-.6L20 46l-.6.6c-2.4 2.4-2.4 6.3 0 8.7 2.4 2.4 6.3 2.4 8.7 0l1.4-1.4 7.4-7.4 7.8-7.8 7.5 7.3z"></path>
							</g>
						</svg>
					</div>
					<div class="mx-2 my-3 d-flex d-md-none">
						<img class="logo-small d-flex d-md-none" src="<?php echo $this->baseurl; ?>/template/images/Joomla-brandmark-monochrome-white-RGB.svg" alt="">
					</div>
					<div class="d-flex flex-wrap align-items-center mx-auto col-md-auto justify-content-center">
						<h1 class="h2 mx-1 d-flex align-items-baseline">
							<span class="icon-cogs d-none d-md-block mx-2 align-items-center" aria-hidden="true"></span>
							<?php echo Text::_('INSTL_PAGE_TITLE'); ?>
						</h1>
						<span class="small mx-1">
							Joomla! <?php echo (new Version)->getShortVersion(); ?>
						</span>
					</div>
					<div class="m-2 d-flex align-items-center col justify-content-end">
						<a href="https://docs.joomla.org/Special:MyLanguage/J4.x:Installing_Joomla" target="_blank">
							<span class="icon-question" aria-hidden="true"></span>
							<span class="visually-hidden"><?php echo Text::_('INSTL_HELP_LINK'); ?></span>
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
