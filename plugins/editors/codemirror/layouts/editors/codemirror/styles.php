<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.codemirror
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

$params     = $displayData->params;
$fontFamily = $displayData->fontFamily ?? 'monospace';
$fontSize   = $params->get('fontSize', 13) . 'px;';
$lineHeight = $params->get('lineHeight', 1.2) . 'em;';

// Set the active line color.
$color           = $params->get('activeLineColor', '#a4c2eb');
$r               = hexdec($color[1] . $color[2]);
$g               = hexdec($color[3] . $color[4]);
$b               = hexdec($color[5] . $color[6]);
$activeLineColor = 'rgba(' . $r . ', ' . $g . ', ' . $b . ', .5)';

// Set the color for matched tags.
$color               = $params->get('highlightMatchColor', '#fa542f');
$r                   = hexdec($color[1] . $color[2]);
$g                   = hexdec($color[3] . $color[4]);
$b                   = hexdec($color[5] . $color[6]);
$highlightMatchColor = 'rgba(' . $r . ', ' . $g . ', ' . $b . ', .5)';

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('plg_editors_codemirror', 'plg_editors_codemirror/codemirror.css');
$wa->addInlineStyle(
<<<CSS
		.CodeMirror {
			font-family: $fontFamily;
			font-size: $fontSize;
			line-height: $lineHeight;
			height: calc(100vh - 600px);
			min-height: 400px;
			max-height: 800px;
		}
		.CodeMirror-activeline-background { background: $activeLineColor; }
		.CodeMirror-matchingtag { background: $highlightMatchColor; }
		.cm-matchhighlight {background-color: $highlightMatchColor; }
		.CodeMirror-selection-highlight-scrollbar {background-color: $highlightMatchColor; }
CSS
);
