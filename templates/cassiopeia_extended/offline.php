<?php

/**
 * @package     Joomla.Site
 * @subpackage  Templates.cassiopeia_extended
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var Joomla\CMS\Document\HtmlDocument $this */

require JPATH_THEMES . '/cassiopeia/offline.php';

$wa = $this->getWebAssetManager();

// Advanced Color Settings
$wa->registerAndUseStyle('colors_custom', 'global/colors.css')
    ->addInlineStyle(':root {
        --body-bg: ' . $this->params->get('bodybg') . ';
        --body-color: ' . $this->params->get('bodycolor') . ';
        --btnbg: ' . $this->params->get('btnbg') . ';
        --btnbgh: ' . $this->params->get('btnbgh') . ';
        --btncolor: ' . $this->params->get('btncolor') . ';
        --btncolorh: ' . $this->params->get('btncolorh') . ';
        --footerbg: ' . $this->params->get('footerbg') . ';
        --footercolor: ' . $this->params->get('footercolor') . ';
        --headerbg: ' . $this->params->get('headerbg') . ';
        --headercolor: ' . $this->params->get('headercolor') . ';
        --link-color: ' . $this->params->get('linkcolor') . ';
        --link-hover-color: ' . $this->params->get('linkcolorh') . ';
    }')

    // Advanced Font Settings
    ->registerAndUseStyle('font_advanced', 'global/fonts.css')
    ->addInlineStyle(':root {
        --body-font-size: ' . $this->params->get('bodysize') . 'rem;
        --h1size: ' . $this->params->get('h1size') . 'rem;
        --h2size: ' . $this->params->get('h2size') . 'rem;
        --h3size: ' . $this->params->get('h3size') . 'rem;
    }');
