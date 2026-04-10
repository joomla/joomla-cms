<?php

/**
 * @package     Joomla.Site
 * @subpackage  Templates.cassiopeia
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var Joomla\CMS\Document\HtmlDocument $this */

$app   = Factory::getApplication();
$input = $app->getInput();
$wa    = $this->getWebAssetManager();

// Browsers support SVG favicons
$this->addHeadLink(HTMLHelper::_('image', 'joomla-favicon.svg', '', [], true, 1), 'icon', 'rel', ['type' => 'image/svg+xml']);
$this->addHeadLink(HTMLHelper::_('image', 'favicon.ico', '', [], true, 1), 'alternate icon', 'rel', ['type' => 'image/vnd.microsoft.icon']);
$this->addHeadLink(HTMLHelper::_('image', 'joomla-favicon-pinned.svg', '', [], true, 1), 'mask-icon', 'rel', ['color' => '#000']);

// Detecting Active Variables
$option   = $input->getCmd('option', '');
$view     = $input->getCmd('view', '');
$layout   = $input->getCmd('layout', '');
$task     = $input->getCmd('task', '');
$itemid   = $input->getCmd('Itemid', '');
$sitename = htmlspecialchars($app->get('sitename'), ENT_QUOTES, 'UTF-8');
$menu     = $app->getMenu()->getActive();
$pageclass = $menu !== null ? $menu->getParams()->get('pageclass_sfx', '') : '';

// Color Theme
$paramsColorName = $this->params->get('colorName', 'colors_standard');
$assetColorName  = 'theme.' . $paramsColorName;

// Use a font scheme if set in the template style options
$paramsFontScheme = $this->params->get('useFontScheme', false);
$fontStyles       = '';

if ($paramsFontScheme) {
    if (stripos($paramsFontScheme, 'https://') === 0) {
        $this->getPreloadManager()->preconnect('https://fonts.googleapis.com/', ['crossorigin' => 'anonymous']);
        $this->getPreloadManager()->preconnect('https://fonts.gstatic.com/', ['crossorigin' => 'anonymous']);
        $this->getPreloadManager()->preload($paramsFontScheme, ['as' => 'style', 'crossorigin' => 'anonymous']);
        $wa->registerAndUseStyle('fontscheme.current', $paramsFontScheme, [], ['rel' => 'lazy-stylesheet', 'crossorigin' => 'anonymous']);

        if (preg_match_all('/family=([^?:]*):/i', $paramsFontScheme, $matches) > 0) {
            $fontStyles = '--cassiopeia-font-family-body: "' . str_replace('+', ' ', $matches[1][0]) . '", sans-serif;
            --cassiopeia-font-family-headings: "' . str_replace('+', ' ', $matches[1][1] ?? $matches[1][0]) . '", sans-serif;
            --cassiopeia-font-weight-normal: 400;
            --cassiopeia-font-weight-headings: 700;';
        }
    } elseif ($paramsFontScheme === 'system') {
        $fontStylesBody    = $this->params->get('systemFontBody', '');
        $fontStylesHeading = $this->params->get('systemFontHeading', '');

        if ($fontStylesBody) {
            $fontStyles = '--cassiopeia-font-family-body: ' . $fontStylesBody . ';
            --cassiopeia-font-weight-normal: 400;';
        }
        if ($fontStylesHeading) {
            $fontStyles .= '--cassiopeia-font-family-headings: ' . $fontStylesHeading . ';
            --cassiopeia-font-weight-headings: 700;';
        }
    } else {
        $wa->registerAndUseStyle('fontscheme.current', $paramsFontScheme, ['version' => 'auto'], ['rel' => 'lazy-stylesheet']);
        $this->getPreloadManager()->preload($wa->getAsset('style', 'fontscheme.current')->getUri() . '?' . $this->getMediaVersion(), ['as' => 'style']);
    }
}

// Enable assets
$wa->usePreset('template.cassiopeia.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr'))
    ->useStyle('template.active.language')
    ->registerAndUseStyle($assetColorName, 'global/' . $paramsColorName . '.css')
    ->useStyle('template.user')
    ->useScript('template.user')
    ->addInlineStyle(":root {
        --hue: 214;
        --template-bg-light: #f0f4fb;
        --template-text-dark: #495057;
        --template-text-light: #ffffff;
        --template-link-color: var(--link-color);
        --template-special-color: #001B4C;
        $fontStyles
    }");

// Override 'template.active' asset to set correct ltr/rtl dependency
$wa->registerStyle('template.active', '', [], [], ['template.cassiopeia.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr')]);

// Logo file or site title param
if ($this->params->get('logoFile')) {
    $logo = HTMLHelper::_('image', Uri::root(false) . htmlspecialchars($this->params->get('logoFile'), ENT_QUOTES), $sitename, ['loading' => 'eager', 'decoding' => 'async'], false, 0);
} elseif ($this->params->get('siteTitle')) {
    $logo = '<span title="' . $sitename . '">' . htmlspecialchars($this->params->get('siteTitle'), ENT_COMPAT, 'UTF-8') . '</span>';
} else {
    $logo = HTMLHelper::_('image', 'logo.svg', $sitename, ['class' => 'logo d-inline-block', 'loading' => 'eager', 'decoding' => 'async'], true, 0);
}

$hasClass = '';

if ($this->countModules('sidebar-left', true)) {
    $hasClass .= ' has-sidebar-left';
}

if ($this->countModules('sidebar-right', true)) {
    $hasClass .= ' has-sidebar-right';
}

// Container
$wrapper = $this->params->get('fluidContainer') ? 'wrapper-fluid' : 'wrapper-static';

$this->setMetaData('viewport', 'width=device-width, initial-scale=1');

$stickyHeader = $this->params->get('stickyHeader') ? 'position-sticky sticky-top' : '';

// Defer fontawesome for increased performance.
$wa->getAsset('style', 'fontawesome')->setAttribute('rel', 'lazy-stylesheet');
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">

<head>
    <jdoc:include type="metas" />
    <jdoc:include type="styles" />
    <jdoc:include type="scripts" />
    <style>
        /* 1. Reset Grid for Full Width Control */
        .site-grid {
            display: grid !important; 
            grid-template-columns: [full-start] minmax(0, 1fr) [main-start] minmax(0, 1320px) [main-end] minmax(0, 1fr) [full-end] !important;
            gap: 0;
        }

        /* 2. Full Width Hero Section with MIT Background */
        .container-component {
            grid-column: full-start / full-end !important;
            background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('images/mit.jpg') !important;
            background-size: cover !important;
            background-position: center !important;
            min-height: 600px !important;
            display: flex;
            position: relative;
            padding: 0 !important;
            overflow: hidden;
        }

        /* 3. Glassmorphism Login Sidebar Overlay */
        .grid-child.container-sidebar-right {
            grid-column: main-end / full-end !important;
            z-index: 100;
            position: absolute;
            right: 5%;
            top: 10%;
            width: 320px;
        }

        .container-sidebar-right .card {
            background: rgba(255, 255, 255, 0.85) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            border-radius: 15px !important;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2) !important;
            padding: 20px;
        }

        /* 4. Interactive Group Display Styling */
        .group-display {
            position: absolute;
            top: 50%;
            left: 10%;
            transform: translateY(-50%);
            color: white;
            text-shadow: 2px 4px 10px rgba(0,0,0,0.9);
            font-family: 'Segoe UI', sans-serif;
            text-align: left;
            z-index: 10;
        }

        .group-display h2 {
            font-size: 1.8rem;
            font-weight: 300;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 0;
        }

        .member-name {
            font-size: 4.5rem;
            font-weight: 900;
            display: block;
            min-height: 100px;
            color: #ffd700; /* Gold */
            transition: all 0.5s ease;
        }

        /* Fade Animation Class */
        .fade-in-out {
            animation: memberFade 4s infinite;
        }

        @keyframes memberFade {
            0% { opacity: 0; transform: translateX(-20px); }
            15% { opacity: 1; transform: translateX(0); }
            85% { opacity: 1; transform: translateX(0); }
            100% { opacity: 0; transform: translateX(20px); }
        }

        /* Hide Default Breadcrumbs for cleaner Hero look */
        .mod-breadcrumbs { display: none; }
    </style>
</head>

<body class="site <?php echo $option . ' ' . $wrapper . ' view-' . $view . ($layout ? ' layout-' . $layout : ' no-layout') . ($task ? ' task-' . $task : ' no-task') . ($itemid ? ' itemid-' . $itemid : '') . ($pageclass ? ' ' . $pageclass : '') . $hasClass . ($this->direction == 'rtl' ? ' rtl' : ''); ?>">
    
    <header class="header container-header full-width<?php echo $stickyHeader ? ' ' . $stickyHeader : ''; ?>">
        <?php if ($this->countModules('topbar')) : ?>
            <div class="container-topbar">
                <jdoc:include type="modules" name="topbar" style="none" />
            </div>
        <?php endif; ?>

        <div class="grid-child">
            <div class="navbar-brand">
                <a class="brand-logo" href="<?php echo $this->baseurl; ?>/">
                    <?php echo $logo; ?>
                </a>
            </div>
        </div>

        <?php if ($this->countModules('menu', true)) : ?>
            <div class="grid-child container-nav">
                <jdoc:include type="modules" name="menu" style="none" />
            </div>
        <?php endif; ?>
    </header>

    <div class="site-grid">
        <div class="grid-child container-component">
            <div class="group-display">
                <h2>Project Team:</h2>
                <span id="member-target" class="member-name fade-in-out"></span>
            </div>
            <jdoc:include type="message" />
            <main>
                <jdoc:include type="component" />
            </main>
        </div>

        <?php if ($this->countModules('sidebar-right', true)) : ?>
            <div class="grid-child container-sidebar-right">
                <jdoc:include type="modules" name="sidebar-right" style="card" />
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <div class="container" style="padding: 40px 0; border-top: 1px solid #ddd; margin-top: 50px;">
            <p style="text-align: center; color: #444; font-weight: bold;">
                Mekele IT Student Portal | Customized by Alembrhan,Hagos, Hadgu, & Tesfom
            </p>
            <p style="text-align: center; font-size: 0.8rem; color: #888;">
                Built with Joomla 5.4-dev & Custom Glassmorphism UI
            </p>
        </div>
    </footer>

    <jdoc:include type="modules" name="debug" style="none" />

    <script>
        (function() {
            // LIST OF YOUR GROUP MEMBERS HERE
            const members = ["Alembrhan Gebremeskel", "Hagos fseha", "Hadgu tkue", "Tesfaom Gebremedhin"]; 
            let currentIndex = 0;
            const target = document.getElementById('member-target');

            function updateName() {
                if(!target) return;
                target.textContent = members[currentIndex];
                currentIndex = (currentIndex + 1) % members.length;
            }

            // Initial call
            updateName();

            // Cycle every 4 seconds (matches CSS animation)
            setInterval(updateName, 4000);
        })();
    </script>
</body>
</html>