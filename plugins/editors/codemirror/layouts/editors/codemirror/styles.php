<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.codemirror
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

$params     = $displayData->params;
$fontFamily = $displayData->fontFamily ?? 'monospace';
$fontSize   = $params->get('fontSize', 13) . 'px;';
$lineHeight = $params->get('lineHeight', 1.2) . 'em;';

// Set the active line color.
$color           = $params->get('activeLineColor', '#a4c2eb');
$r               = hexdec($color{1} . $color{2});
$g               = hexdec($color{3} . $color{4});
$b               = hexdec($color{5} . $color{6});
$activeLineColor = 'rgba(' . $r . ', ' . $g . ', ' . $b . ', .5)';

// Set the color for matched tags.
$color               = $params->get('highlightMatchColor', '#fa542f');
$r                   = hexdec($color{1} . $color{2});
$g                   = hexdec($color{3} . $color{4});
$b                   = hexdec($color{5} . $color{6});
$highlightMatchColor = 'rgba(' . $r . ', ' . $g . ', ' . $b . ', .5)';

JHtml::_('stylesheet', 'editors/codemirror/codemirror.css', array('version' => 'auto', 'relative' => true));

JFactory::getDocument()->addStyleDeclaration(
<<<CSS
		.CodeMirror {
			font-family: $fontFamily;
			font-size: $fontSize;
			line-height: $lineHeight;
		}
		.CodeMirror-activeline-background { background: $activeLineColor; }
		.CodeMirror-matchingtag { background: $highlightMatchColor; }
		.cm-matchhighlight {background-color: $highlightMatchColor; }
		.CodeMirror-selection-highlight-scrollbar {background-color: $highlightMatchColor; }
CSS
);
