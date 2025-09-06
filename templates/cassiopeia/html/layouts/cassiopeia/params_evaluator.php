<?php

/**
 * @package     Joomla.Site
 * @subpackage  Templates.cassiopeia
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') || die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

extract($displayData);

/** @var Joomla\CMS\Document\HtmlDocument $doc */

$app       = Factory::getApplication();
$input     = $app->getInput();
$wa        = $doc->getWebAssetManager();
$option    = $input->getCmd('option', '');
$view      = $input->getCmd('view', '');
$layout    = $input->getCmd('layout', '');
$task      = $input->getCmd('task', '');
$itemid    = $input->getCmd('Itemid', '');
$sitename  = htmlspecialchars($app->get('sitename'), ENT_QUOTES, 'UTF-8');
$menu      = $app->getMenu()->getActive();
$pageclass = $menu !== null ? $menu->getParams()->get('pageclass_sfx', '') : '';

// Browsers support SVG favicons
$doc->addHeadLink(HTMLHelper::_('image', 'joomla-favicon.svg', '', [], true, 1), 'icon', 'rel', ['type' => 'image/svg+xml']);
$doc->addHeadLink(HTMLHelper::_('image', 'favicon.ico', '', [], true, 1), 'alternate icon', 'rel', ['type' => 'image/vnd.microsoft.icon']);

// Color Theme
$paramsColorName = $doc->params->get('colorName', 'colors_standard');
$assetColorName  = 'theme.' . $paramsColorName;

// Use a font scheme if set in the template style options
$paramsFontScheme = $doc->params->get('useFontScheme', false);
$fontStyles       = '';

if ($paramsFontScheme) {
    if (stripos($paramsFontScheme, 'https://') === 0) {
        $doc->getPreloadManager()->preconnect('https://fonts.googleapis.com/', ['crossorigin' => 'anonymous']);
        $doc->getPreloadManager()->preconnect('https://fonts.gstatic.com/', ['crossorigin' => 'anonymous']);
        $doc->getPreloadManager()->preload($paramsFontScheme, ['as' => 'style', 'crossorigin' => 'anonymous']);
        $wa->registerAndUseStyle('fontscheme.current', $paramsFontScheme, [], ['rel' => 'lazy-stylesheet', 'crossorigin' => 'anonymous']);

        if (preg_match_all('/family=([^?:]*):/i', $paramsFontScheme, $matches) > 0) {
            $fontStyles = '--cassiopeia-font-family-body: "' . str_replace('+', ' ', $matches[1][0]) . '", sans-serif;
            --cassiopeia-font-family-headings: "' . str_replace('+', ' ', $matches[1][1] ?? $matches[1][0]) . '", sans-serif;
            --cassiopeia-font-weight-normal: 400;
            --cassiopeia-font-weight-headings: 700;';
        }
    } elseif ($paramsFontScheme === 'system') {
        $fontStylesBody    = $doc->params->get('systemFontBody', '');
        $fontStylesHeading = $doc->params->get('systemFontHeading', '');

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
        $doc->getPreloadManager()->preload($wa->getAsset('style', 'fontscheme.current')->getUri() . '?' . $doc->getMediaVersion(), ['as' => 'style']);
    }
}

// Enable assets
$wa->usePreset('template.cassiopeia.' . ($doc->direction === 'rtl' ? 'rtl' : 'ltr'))
    ->useStyle('template.active.language');

if ($entry === 'offline') {
    $wa->useStyle('template.offline');
}

$wa->registerAndUseStyle($assetColorName, 'global/' . $paramsColorName . '.css')
    ->useStyle('template.user')
    ->useScript('template.user')
    ->addInlineStyle(
        <<<CSS
:root {
    --hue: 214;
    --template-bg-light: #f0f4fb;
    --template-text-dark: #495057;
    --template-text-light: #ffffff;
    --link-color: var(--link-color);
    --template-link-color: var(--link-color);
    --template-special-color: #001B4C;
    $fontStyles
}
CSS
    );

// Override 'template.active' asset to set correct ltr/rtl dependency
$wa->registerStyle('template.active', '', [], [], ['template.cassiopeia.' . ($doc->direction === 'rtl' ? 'rtl' : 'ltr')]);

// Advanced Color Settings
if ($doc->params->get('colorSettings', false)) {
    $wa->registerAndUseStyle('colors_custom', 'global/' . 'colors_custom.css')
        ->addInlineStyle(':root {
        --body-bg: ' . $doc->params->get('bodybg') . ';
        --body-color: ' . $doc->params->get('bodycolor') . ';
        --btnbg: ' . $doc->params->get('btnbg') . ';
        --btnbgh: ' . $doc->params->get('btnbgh') . ';
        --btncolor: ' . $doc->params->get('btncolor') . ';
        --btncolorh: ' . $doc->params->get('btncolorh') . ';
        --footerbg: ' . $doc->params->get('footerbg') . ';
        --footercolor: ' . $doc->params->get('footercolor') . ';
        --headerbg: ' . $doc->params->get('headerbg') . ';
        --headercolor: ' . $doc->params->get('headercolor') . ';
        --link-color: ' . $doc->params->get('linkcolor') . ';
        --link-hover-color: ' . $doc->params->get('linkcolorh') . ';
    }');
}

// Advanced Font Settings
if ($doc->params->get('fontSettings', false)) {
    $wa->registerAndUseStyle('font_advanced', 'global/' . 'font_advanced.css')
        ->addInlineStyle(':root {
        --body-font-size: ' . $doc->params->get('bodysize') . 'rem;
        --h1size: ' . $doc->params->get('h1size') . 'rem;
        --h2size: ' . $doc->params->get('h2size') . 'rem;
        --h3size: ' . $doc->params->get('h3size') . 'rem;
    }');
}

// Logo file or site title param
if ($doc->params->get('logoFile')) {
    $doc->params->logo = HTMLHelper::_('image', Uri::root(false) . htmlspecialchars($doc->params->get('logoFile'), ENT_QUOTES), $sitename, ['loading' => 'eager', 'decoding' => 'async'], false, 0);
} elseif ($doc->params->get('siteTitle')) {
    $doc->params->logo = '<span title="' . $sitename . '">' . htmlspecialchars($doc->params->get('siteTitle'), ENT_COMPAT, 'UTF-8') . '</span>';
} else {
    $doc->params->logo = HTMLHelper::_('image', 'logo.svg', $sitename, ['class' => 'logo d-inline-block', 'loading' => 'eager', 'decoding' => 'async'], true, 0);
}

$doc->setMetaData('viewport', 'width=device-width, initial-scale=1');

// Defer fontawesome for increased performance. Once the page is loaded javascript changes it to a stylesheet.
$wa->getAsset('style', 'fontawesome')->setAttribute('rel', 'lazy-stylesheet');

$doc->params->hasClass = '';

if ($doc->countModules('sidebar-left', true)) {
    $doc->params->hasClass .= ' has-sidebar-left';
}

if ($doc->countModules('sidebar-right', true)) {
    $doc->params->hasClass .= ' has-sidebar-right';
}

// Container
$doc->params->wrapper = $doc->params->get('fluidContainer') ? 'wrapper-fluid' : 'wrapper-static';

$doc->params->stickyHeader = $doc->params->get('stickyHeader') ? 'position-sticky sticky-top' : '';

$doc->params->htmlTagAttributes = [
    'lang' => $doc->language,
    'dir'  => $doc->direction,
];

if ($entry === 'component') {
    $doc->params->bodyTagAttributes = [
        'class' => trim('contentpane component' . ($doc->direction === 'rtl' ? ' rtl' : ''))
    ];
} else {
    $doc->params->bodyTagAttributes = [
        'class' => trim('site body ' . $option . ' view-' . $view . ($layout ? ' layout-' . $layout : '') . ($task ? ' task-' . $task : '') . ($itemid ? ' itemid-' . $itemid : '') . ($pageclass ? ' ' . $pageclass : '') . $doc->params->hasClass),
    ];
}
