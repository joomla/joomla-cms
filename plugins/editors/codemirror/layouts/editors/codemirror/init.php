<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.codemirror
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

$params  = $displayData->params;

$basePath = $params->get('basePath', 'media/editors/codemirror/');
$modePath = $params->get('modePath', 'media/editors/codemirror/mode/%N/%N');

$extJS = JFactory::getConfig()->get('debug') ? '.js' : '.min.js';
$extCSS = JFactory::getConfig()->get('debug') ? '.css' : '.min.css';

JHtml::_('script', $basePath . 'lib/codemirror' . $extJS);
JHtml::_('script', $basePath . 'lib/addons' . $extJS);
JHtml::_('stylesheet', $basePath . 'lib/codemirror' . $extCSS);
JHtml::_('stylesheet', $basePath . 'lib/addons' . $extCSS);

$fskeys = $params->get('fullScreenMod', array());
$fskeys[] = $params->get('fullScreen', 'F10');
$fullScreenCombo = implode('-', $fskeys);

?>
<?php // We don't actually want a script tag but this will cause text editors to switch to javascript mode ?>
<?php if (false) : ?><script type="text/javascript"><?php endif; ?>

<?php // Requires CodeMirror and jQuery ?>
;(function (cm, $) {
	<?php // The legacy combo for fullscreen. Remove it later now there is a configurable one. ?>
	cm.keyMap.default["Ctrl-Q"] = toggleFullScreen;
	cm.keyMap.default[<?php echo json_encode($fullScreenCombo); ?>] = toggleFullScreen;
	cm.keyMap.default["Esc"] = closeFullScreen;
	<?php // For mode autoloading. ?>
	cm.modeURL = <?php echo json_encode(JUri::root(true) . '/' . $modePath . $extJS); ?>;

	<?php // Fire this function any time an editor is created. ?>
	cm.defineInitHook(function (editor)
	{
		<?php // Load the editor mode (typically 'htmlmixed'). ?>
		cm.autoLoadMode(editor, editor.options.mode);

		<?php // Handle gutter clicks (place or remove a marker). ?>
		editor.on("gutterClick", function (ed, n, gutter) {
			if (gutter != "CodeMirror-markergutter") { return; }
			var info = ed.lineInfo(n),
				hasMarker = !!info.gutterMarkers && !!info.gutterMarkers["CodeMirror-markergutter"];

			ed.setGutterMarker(n, "CodeMirror-markergutter", hasMarker ? null : makeMarker());
		});

		<?php // jQuery's ready function. ?>
		$(function () {
			<?php // Some browsers do something weird with the fieldset which doesn't work well with CodeMirror. Fix it. ?>
			$(editor.getTextArea()).parent('fieldset').css('min-width', 0);

			<?php // Listen for Bootstrap's 'shown' event. If this editor was in a hidden element when created, it may need to be refreshed. ?>
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

<?php // And now switch it off ?>
<?php if (false) : ?></script><?php endif; ?>
