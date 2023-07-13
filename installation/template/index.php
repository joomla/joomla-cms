<?php

/**
 * @package Joomla.Installation
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;

/** @var \Joomla\CMS\Document\HtmlDocument $this */
// Add required assets
$this->getWebAssetManager()
    ->registerAndUseStyle('template.installation', 'installation/template/css/template' . ($this->direction === 'rtl' ? '-rtl' : '') . '.css', ['version' => 'auto'], [], [])
    ->useScript('core')
    ->useScript('keepalive')
    ->useScript('form.validate')
    ->registerAndUseScript('template.installation', 'installation/template/js/template.js', ['version' => 'auto'], ['defer' => true], ['core', 'form.validate']);

$this->getWebAssetManager()
    ->useStyle('webcomponent.joomla-alert')
    ->useScript('messages')
    ->useScript('webcomponent.core-loader')
    ->addInlineStyle(':root {
		--hue: 214;
		--template-bg-light: #f0f4fb;
		--template-text-dark: #495057;
		--template-text-light: #ffffff;
		--template-link-color: #2a69b8;
		--template-special-color: #001b4c;
	}');

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
            <?php // Header?>
            <header id="header" class="header">
                <div class="row me-0">
                    <div class="col">
                        <div class="logo d-none d-md-block col">
                            <img src="<?php echo $this->baseurl; ?>/template/images/Joomla-logo-monochrome-horizontal-white.svg" alt="">
                        </div>
                        <div class="mx-2 my-3 d-flex d-md-none">
                            <img class="logo-small d-flex d-md-none" src="<?php echo $this->baseurl; ?>/template/images/Joomla-brandmark-monochrome-white-RGB.svg" alt="">
                        </div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center col justify-content-center">
                        <h1 class="h2 mx-1 d-flex align-items-baseline text-white">
                            <span class="icon-cogs d-none d-md-block mx-2 align-items-center" aria-hidden="true"></span>
                            <?php echo Text::_('INSTL_PAGE_TITLE'); ?>
                        </h1>
                        <span class="small mx-1">
                            Joomla! <?php echo (new Version())->getShortVersion(); ?>
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
            <?php // Container?>
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
