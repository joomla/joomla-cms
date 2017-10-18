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

$params   = $displayData->params;
$basePath = $params->get('basePath', 'media/editors/codemirror/');
$modePath = $params->get('modePath', 'media/editors/codemirror/mode/%N/%N');
$extJS    = JDEBUG ? '.js' : '.min.js';
$extCSS   = JDEBUG ? '.css' : '.min.css';

JHtml::_('script', $basePath . 'lib/codemirror' . $extJS, array('version' => 'auto'));
JHtml::_('script', $basePath . 'lib/addons' . $extJS, array('version' => 'auto'));
JHtml::_('stylesheet', $basePath . 'lib/codemirror' . $extCSS, array('version' => 'auto'));
JHtml::_('stylesheet', $basePath . 'lib/addons' . $extCSS, array('version' => 'auto'));

$fskeys          = $params->get('fullScreenMod', array());
$fskeys[]        = $params->get('fullScreen', 'F10');
$fullScreenCombo = implode('-', $fskeys);
$fsCombo         = json_encode($fullScreenCombo);
$modPath         = json_encode(JUri::root(true) . '/' . $modePath . $extJS);
JFactory::getDocument()->addScriptDeclaration(
<<<JS
		;(function (cm, $) {
			cm.keyMap.default["Ctrl-Q"] = toggleFullScreen;
			cm.keyMap.default[$fsCombo] = toggleFullScreen;
			cm.keyMap.default["Esc"] = closeFullScreen;
			// For mode autoloading.
			cm.modeURL = $modPath;
			// Fire this function any time an editor is created.
			cm.defineInitHook(function (editor)
			{
				// Try to set up the mode
				var mode = cm.findModeByName(editor.options.mode || '');

				if (mode)
				{
					cm.autoLoadMode(editor, mode.mode);
					editor.setOption('mode', mode.mime);
				}
				else
				{
					cm.autoLoadMode(editor, editor.options.mode);
				}

				// Handle gutter clicks (place or remove a marker).
				editor.on("gutterClick", function (ed, n, gutter) {
					if (gutter != "CodeMirror-markergutter") { return; }
					var info = ed.lineInfo(n),
						hasMarker = !!info.gutterMarkers && !!info.gutterMarkers["CodeMirror-markergutter"];
					ed.setGutterMarker(n, "CodeMirror-markergutter", hasMarker ? null : makeMarker());
				});

				// jQuery's ready function.
				$(function () {
					// Some browsers do something weird with the fieldset which doesn't work well with CodeMirror. Fix it.
					$(editor.getTextArea()).parent('fieldset').css('min-width', 0);
					// Listen for Bootstrap's 'shown' event. If this editor was in a hidden element when created, it may need to be refreshed.
					$(document.body).on("shown shown.bs.tab shown.bs.modal", function () { editor.refresh(); });
				});
			});
			function toggleFullScreen(cm)
			{
				cm.setOption("fullScreen", !cm.getOption("fullScreen"));
			}
			function closeFullScreen(cm)
			{
				cm.getOption("fullScreen") && cm.setOption("fullScreen", false);
			}
			function makeMarker()
			{
				var marker = document.createElement("div");
				marker.className = "CodeMirror-markergutter-mark";
				return marker;
			}
		}(CodeMirror, jQuery));
JS
);
