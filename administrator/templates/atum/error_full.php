<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.Atum
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;

/** @var \Joomla\CMS\Document\ErrorDocument $this */

$app   = Factory::getApplication();
$input = $app->getInput();
$wa    = $this->getWebAssetManager();

// Detecting Active Variables
$option     = $input->get('option', '');
$view       = $input->get('view', '');
$layout     = $input->get('layout', 'default');
$task       = $input->get('task', 'display');
$cpanel     = $option === 'com_cpanel';
$hiddenMenu = $app->getInput()->get('hidemainmenu');

// Browsers support SVG favicons
$this->addHeadLink(HTMLHelper::_('image', 'joomla-favicon.svg', '', [], true, 1), 'icon', 'rel', ['type' => 'image/svg+xml']);
$this->addHeadLink(HTMLHelper::_('image', 'favicon.ico', '', [], true, 1), 'alternate icon', 'rel', ['type' => 'image/vnd.microsoft.icon']);
$this->addHeadLink(HTMLHelper::_('image', 'joomla-favicon-pinned.svg', '', [], true, 1), 'mask-icon', 'rel', ['color' => '#000']);

// Template params
$logoBrandLarge  = $this->params->get('logoBrandLarge')
    ? Uri::root(false) . htmlspecialchars($this->params->get('logoBrandLarge'), ENT_QUOTES)
    : Uri::root(false) . 'media/templates/administrator/atum/images/logos/brand-large.svg';
$logoBrandSmall = $this->params->get('logoBrandSmall')
    ? Uri::root(false) . htmlspecialchars($this->params->get('logoBrandSmall'), ENT_QUOTES)
    : Uri::root(false) . 'media/templates/administrator/atum/images/logos/brand-small.svg';

$logoBrandLargeAlt = empty($this->params->get('logoBrandLargeAlt')) && empty($this->params->get('emptyLogoBrandLargeAlt'))
    ? ''
    : htmlspecialchars($this->params->get('logoBrandLargeAlt', ''), ENT_COMPAT, 'UTF-8');
$logoBrandSmallAlt = empty($this->params->get('logoBrandSmallAlt')) && empty($this->params->get('emptyLogoBrandSmallAlt'))
    ? ''
    : htmlspecialchars($this->params->get('logoBrandSmallAlt', ''), ENT_COMPAT, 'UTF-8');


    // Get the hue value
    preg_match('#^hsla?\(([0-9]+)[\D]+([0-9]+)[\D]+([0-9]+)[\D]+([0-9](?:.\d+)?)?\)$#i', $this->params->get('hue', 'hsl(214, 63%, 20%)'), $matches);

    $linkColor = $this->params->get('link-color', '#2a69b8');
    list($r, $g, $b) = sscanf($linkColor, "#%02x%02x%02x");

    // Enable assets
    $wa->usePreset('template.atum.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr'))
        ->useStyle('template.active.language')
        ->useStyle('template.user')
        ->addInlineStyle(':root {
			--hue: ' . $matches[1] . ';
			--template-bg-light: ' . $this->params->get('bg-light', '#f0f4fb') . ';
			--template-text-dark: ' . $this->params->get('text-dark', '#495057') . ';
			--template-text-light: ' . $this->params->get('text-light', '#ffffff') . ';
			--link-color: ' . $linkColor . ';
    		--link-color-rgb: ' . $r . ',' . $g . ',' . $b . ';
			--template-special-color: ' . $this->params->get('special-color', '#001B4C') . ';
		}');

// Override 'template.active' asset to set correct ltr/rtl dependency
    $wa->registerStyle('template.active', '', [], [], ['template.atum.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr')]);

// Set some meta data
    $this->setMetaData('viewport', 'width=device-width, initial-scale=1');

    $monochrome    = (bool) $this->params->get('monochrome');
    $colorScheme   = $this->params->get('colorScheme', 'os');
    $themeModeAttr = '';

    if ($colorScheme) {
        $themeModes   = ['os' => ' data-color-scheme-os', 'light' => ' data-bs-theme="light" data-color-scheme="light"', 'dark' => ' data-bs-theme="dark" data-color-scheme="dark"'];
        // Check for User choose, for now this have a priority over the parameters
        $userColorScheme = $app->getInput()->cookie->get('userColorScheme', '');
        if ($userColorScheme && !empty($themeModes[$userColorScheme])) {
            $themeModeAttr = $themeModes[$userColorScheme];
        } else {
            // Check parameters first (User and Template), then look if we have detected the OS color scheme (if it set to 'os')
            $colorScheme   = $app->getIdentity()->getParam('colorScheme', $colorScheme);
            $osColorScheme = $colorScheme === 'os' ? $app->getInput()->cookie->get('osColorScheme', '') : '';
            $themeModeAttr = ($themeModes[$colorScheme] ?? '') . ($themeModes[$osColorScheme] ?? '');
        }
    }

// @see administrator/templates/atum/html/layouts/status.php
    $statusModules = LayoutHelper::render('status', ['modules' => 'status']);
    ?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>"<?php echo $themeModeAttr; ?>>
<head>
    <jdoc:include type="metas" />
    <jdoc:include type="styles" />
    <jdoc:include type="scripts" />
</head>

<body class="admin <?php echo $option . ' view-' . $view . ' layout-' . $layout . ($task ? ' task-' . $task : '') . ($monochrome ? ' monochrome' : ''); ?>">

    <noscript>
        <div class="alert alert-danger" role="alert">
            <?php echo Text::_('JGLOBAL_WARNJAVASCRIPT'); ?>
        </div>
    </noscript>

    <header id="header" class="header d-flex">
        <div class="header-title d-flex">
            <div class="d-flex align-items-center">
                <div class="logo">
                    <?php echo HTMLHelper::_('image', $logoBrandLarge, $logoBrandLargeAlt, ['loading' => 'eager', 'decoding' => 'async'], false, 0); ?>
                    <?php echo HTMLHelper::_('image', $logoBrandSmall, $logoBrandSmallAlt, ['class' => 'logo-collapsed', 'loading' => 'eager', 'decoding' => 'async'], false, 0); ?>
                </div>
            </div>
            <jdoc:include type="modules" name="title" />
        </div>
        <?php echo $statusModules; ?>
    </header>

    <div id="wrapper" class="d-flex wrapper<?php echo $hiddenMenu ? '0' : ''; ?>">
        <div class="container-fluid container-main">
            <?php if (!$cpanel) : ?>
                <a class="btn btn-subhead d-md-none d-lg-none d-xl-none" data-bs-toggle="collapse"
                   data-bs-target=".subhead-collapse"><?php echo Text::_('TPL_ATUM_TOOLBAR'); ?>
                    <span class="icon-wrench"></span></a>
                <div id="subhead" class="subhead mb-3">
                    <div id="container-collapse" class="container-collapse"></div>
                    <div class="row">
                        <div class="col-md-12">
                            <jdoc:include type="modules" name="toolbar" style="none" />
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <section id="content" class="content">
                <jdoc:include type="message" />
                <jdoc:include type="modules" name="top" style="html5" />
                <div class="row">
                    <div class="col-md-12">
                        <h1><?php echo Text::_('JERROR_AN_ERROR_HAS_OCCURRED'); ?></h1>
                        <blockquote class="blockquote">
                            <span class="badge bg-secondary"><?php echo $this->error->getCode(); ?></span>
                            <?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?>
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
                        <p>
                            <a href="<?php echo $this->baseurl; ?>" class="btn btn-secondary">
                                <span class="icon-dashboard" aria-hidden="true"></span>
                                <?php echo Text::_('JGLOBAL_TPL_CPANEL_LINK_TEXT'); ?></a>
                        </p>
                    </div>

                    <?php if ($this->countModules('bottom')) : ?>
                        <jdoc:include type="modules" name="bottom" style="html5" />
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <?php if (!$hiddenMenu) : ?>
            <button class="navbar-toggler toggler-burger collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-wrapper" aria-controls="sidebar-wrapper" aria-expanded="false" aria-label="<?php echo Text::_('JTOGGLE_SIDEBAR_MENU'); ?>">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div id="sidebar-wrapper" class="sidebar-wrapper sidebar-menu" <?php echo $hiddenMenu ? 'data-hidden="' . $hiddenMenu . '"' : ''; ?>>
                <div id="sidebarmenu">
                    <div class="sidebar-toggle item item-level-1">
                        <a id="menu-collapse" href="#" aria-label="<?php echo Text::_('JTOGGLE_SIDEBAR_MENU'); ?>">
                            <span id="menu-collapse-icon" class="icon-toggle-off icon-fw" aria-hidden="true"></span>
                            <span class="sidebar-item-title"><?php echo Text::_('JTOGGLE_SIDEBAR_MENU'); ?></span>
                        </a>
                    </div>
                    <jdoc:include type="modules" name="menu" style="none" />
                </div>
            </div>
        <?php endif; ?>
    </div>
    <jdoc:include type="modules" name="debug" style="none" />
</body>
</html>
