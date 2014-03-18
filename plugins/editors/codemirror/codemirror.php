<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.codemirror
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * CodeMirror Editor Plugin.
 *
 * @package     Joomla.Plugin
 * @subpackage  Editors.codemirror
 * @since       1.6
 */
class PlgEditorCodemirror extends JPlugin
{
	/**
	 * Base path for editor files
	 */
	protected $_basePath = 'media/editors/codemirror/';

	/**
	 * Initialises the Editor.
	 *
	 * @return  string  JavaScript Initialization string.
	 */
	public function onInit()
	{
		JHtml::_('behavior.framework');
		JHtml::_('script', $this->_basePath . 'js/codemirror.js', false, false, false, false);
		JHtml::_('script', $this->_basePath . 'js/fullscreen.js', false, false, false, false);
		JHtml::_('stylesheet', $this->_basePath . 'css/codemirror.css');
		JHtml::_('stylesheet', $this->_basePath . 'css/configuration.css');

		return '';
	}

	/**
	 * Copy editor content to form field.
	 *
	 * @param   string  $id  The id of the editor field.
	 *
	 * @return  string Javascript
	 */
	public function onSave($id)
	{
		return "document.getElementById('$id').value = Joomla.editors.instances['$id'].getValue();\n";
	}

	/**
	 * Get the editor content.
	 *
	 * @param   string  $id  The id of the editor field.
	 *
	 * @return  string  Javascript
	 */
	public function onGetContent($id)
	{
		return "Joomla.editors.instances['$id'].getValue();\n";
	}

	/**
	 * Set the editor content.
	 *
	 * @param   string  $id       The id of the editor field.
	 * @param   string  $content  The content to set.
	 *
	 * @return  string  Javascript
	 */
	public function onSetContent($id, $content)
	{
		return "Joomla.editors.instances['$id'].setValue($content);\n";
	}

	/**
	 * Adds the editor specific insert method.
	 *
	 * @return  boolean
	 */
	public function onGetInsertMethod()
	{
		static $done = false;

		// Do this only once.
		if (!$done)
		{
			$done = true;
			$doc = JFactory::getDocument();
			$js = "\tfunction jInsertEditorText(text, editor)
				{
					Joomla.editors.instances[editor].replaceSelection(text);\n
			}";
			$doc->addScriptDeclaration($js);
		}

		return true;
	}

	/**
	 * Display the editor area.
	 *
	 * @param   string   $name     The control name.
	 * @param   string   $content  The contents of the text area.
	 * @param   string   $width    The width of the text area (px or %).
	 * @param   string   $height   The height of the text area (px or %).
	 * @param   integer  $col      The number of columns for the textarea.
	 * @param   integer  $row      The number of rows for the textarea.
	 * @param   boolean  $buttons  True and the editor buttons will be displayed.
	 * @param   string   $id       An optional ID for the textarea (note: since 1.6). If not supplied the name is used.
	 * @param   string   $asset    Unused
	 * @param   object   $author   Unused
	 * @param   array    $params   Associative array of editor parameters.
	 *
	 * @return  string  HTML Output
	 */
	public function onDisplay($name, $content, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = array())
	{
		if (empty($id))
		{
			$id = $name;
		}

		// Only add "px" to width and height if they are not given as a percentage
		if (is_numeric($width))
		{
			$width .= 'px';
		}

		if (is_numeric($height))
		{
			$height .= 'px';
		}

		// Must pass the field id to the buttons in this editor.
		$buttons = $this->_displayButtons($id, $buttons, $asset, $author);

		// Look if we need special syntax coloring.
		$syntax = $this->params->get('syntax', 'php');

		if ($syntax)
		{
			switch ($syntax)
			{
				case 'css':
					$parserFile        = array('css.js', 'closebrackets.js');
					$mode              = 'text/css';
					$autoCloseBrackets = true;
					$autoCloseTags     = false;
					$fold              = true;
					$matchTags         = false;
					$matchBrackets     = true;
					JHtml::_('script', $this->_basePath . 'js/brace-fold.js', false, false, false, false);
					break;

				case 'ini':
					$parserFile        = array('css.js');
					$mode              = 'text/css';
					$autoCloseBrackets = false;
					$autoCloseTags     = false;
					$fold              = false;
					$matchTags         = false;
					$matchBrackets     = false;
					break;

				case 'xml':
					$parserFile        = array('xml.js', 'closetag.js');
					$mode              = 'application/xml';
					$fold              = true;
					$autoCloseBrackets = false;
					$autoCloseTags     = true;
					$matchTags         = true;
					$matchBrackets     = false;
					JHtml::_('script', $this->_basePath . 'js/xml-fold.js', false, false, false, false);
					break;

				case 'js':
					$parserFile        = array('javascript.js', 'closebrackets.js');
					$mode              = 'text/javascript';
					$autoCloseBrackets = true;
					$autoCloseTags     = false;
					$fold              = true;
					$matchTags         = false;
					$matchBrackets     = true;
					JHtml::_('script', $this->_basePath . 'js/brace-fold.js', false, false, false, false);
					break;

				case 'less':
					$parserFile        = array('less.js', 'css.js', 'closebrackets.js');
					$mode              = 'text/x-less';
					$autoCloseBrackets = true;
					$autoCloseTags     = false;
					$fold              = true;
					$matchTags         = false;
					$matchBrackets     = true;
					JHtml::_('script', $this->_basePath . 'js/brace-fold.js', false, false, false, false);
					break;

				case 'php':
					$parserFile        = array('xml.js', 'clike.js', 'css.js', 'javascript.js', 'htmlmixed.js', 'php.js', 'closebrackets.js', 'closetag.js');
					$mode              = 'application/x-httpd-php';
					$autoCloseBrackets = true;
					$autoCloseTags     = true;
					$fold              = true;
					$matchTags         = true;
					$matchBrackets     = true;
					JHtml::_('script', $this->_basePath . 'js/brace-fold.js', false, false, false, false);
					JHtml::_('script', $this->_basePath . 'js/xml-fold.js', false, false, false, false);
					break;

				default:
					$parserFile        = false;
					$mode              = 'text/plain';
					$autoCloseBrackets = false;
					$autoCloseTags     = false;
					$fold              = false;
					$matchTags         = false;
					$matchBrackets     = false;
					break;
			}
		}

		if ($parserFile)
		{
			foreach ($parserFile as $file)
			{
				JHtml::_('script', $this->_basePath . 'js/' . $file, false, false, false, false);
			}
		}

		$options	= new stdClass;

		$options->mode = $mode;
		$options->smartIndent = true;

		// Enabled the line numbers.
		if ($this->params->get('lineNumbers') == "1")
		{
			$options->lineNumbers = true;
		}

		if ($this->params->get('autoFocus') == "1")
		{
			$options->autofocus	= true;
		}

		if ($this->params->get('autoCloseBrackets') == "1")
		{
			$options->autoCloseBrackets	= $autoCloseBrackets;
		}

		if ($this->params->get('autoCloseTags') == "1")
		{
			$options->autoCloseTags	= $autoCloseTags;
		}

		if ($this->params->get('matchTags') == "1")
		{
			$options->matchTags = $matchTags;
			JHtml::_('script', $this->_basePath . 'js/matchtags.js', false, false, false, false);
		}

		if ($this->params->get('matchBrackets') == "1")
		{
			$options->matchBrackets = $matchBrackets;
			JHtml::_('script', $this->_basePath . 'js/matchbrackets.js', false, false, false, false);
		}

		if ($this->params->get('marker-gutter') == "1")
		{
			$options->foldGutter = $fold;
			$options->gutters = array('CodeMirror-linenumbers', 'CodeMirror-foldgutter', 'breakpoints');
			JHtml::_('script', $this->_basePath . 'js/foldcode.js', false, false, false, false);
			JHtml::_('script', $this->_basePath . 'js/foldgutter.js', false, false, false, false);
		}

		if ($this->params->get('theme', '') == 'ambiance')
		{
			$options->theme	= 'ambiance';
			JHtml::_('stylesheet', $this->_basePath . 'css/ambiance.css');
		}

		if ($this->params->get('lineWrapping') == "1")
		{
			$options->lineWrapping = true;
		}

		if ($this->params->get('tabmode', '') == 'shift')
		{
			$options->tabMode = 'shift';
		}

		$html = array();
		$html[]	= "<textarea name=\"$name\" id=\"$id\" cols=\"$col\" rows=\"$row\">$content</textarea>";
		$html[] = $buttons;
		$html[] = '<script type="text/javascript">';
		$html[] = '(function() {';
		$html[] = '		var editor = CodeMirror.fromTextArea(document.getElementById("' . $id . '"), ' . json_encode($options) . ');';
		$html[] = '		editor.setOption("extraKeys", {';
		$html[] = '			"Ctrl-Q": function(cm) {';
		$html[] = '				setFullScreen(cm, !isFullScreen(cm));';
		$html[] = '			},';
		$html[] = '			"Esc": function(cm) {';
		$html[] = '				if (isFullScreen(cm)) setFullScreen(cm, false);';
		$html[] = '			}';
		$html[] = '		});';
		$html[] = '		editor.on("gutterClick", function(cm, n) {';
		$html[] = '			var info = cm.lineInfo(n)';
		$html[] = '			cm.setGutterMarker(n, "breakpoints", info.gutterMarkers ? null : makeMarker())';
		$html[] = '		})';
		$html[] = '		function makeMarker() {';
		$html[] = '			var marker = document.createElement("div")';
		$html[] = '			marker.style.color = "#822"';
		$html[] = '			marker.innerHTML = "●"';
		$html[] = '			return marker';
		$html[] = '		}';
		$html[] = '		Joomla.editors.instances[\'' . $id . '\'] = editor;';
		$html[] = '})()';
		$html[] = '</script>';

		return implode("\n", $html);
	}

	/**
	 * Displays the editor buttons.
	 *
	 * @param   string  $name     The editor name
	 * @param   mixed   $buttons  [array with button objects | boolean true to display buttons]
	 * @param   string  $asset    The object asset
	 * @param   object  $author   The author.
	 *
	 * @return  string HTML
	 */
	protected function _displayButtons($name, $buttons, $asset, $author)
	{
		// Load modal popup behavior
		JHtml::_('behavior.modal', 'a.modal-button');

		$args['name'] = $name;
		$args['event'] = 'onGetInsertMethod';

		$html = array();
		$results[] = $this->update($args);

		foreach ($results as $result)
		{
			if (is_string($result) && trim($result))
			{
				$html[] = $result;
			}
		}

		if (is_array($buttons) || (is_bool($buttons) && $buttons))
		{
			$results = $this->_subject->getButtons($name, $buttons, $asset, $author);

			// This will allow plugins to attach buttons or change the behavior on the fly using AJAX
			$html[] = '<div id="editor-xtd-buttons">';
			$html[] = '<div class="btn-toolbar">';

			foreach ($results as $button)
			{
				// Results should be an object
				if ($button->get('name'))
				{
					$modal		= ($button->get('modal')) ? 'class="modal-button btn"' : null;
					$href		= ($button->get('link')) ? ' class="btn" href="' . JUri::base() . $button->get('link') . '"' : null;
					$onclick	= ($button->get('onclick')) ? 'onclick="' . $button->get('onclick') . '"' : null;
					$title      = ($button->get('title')) ? $button->get('title') : $button->get('text');
					$html[] = '<a ' . $modal . ' title="' . $title . '" ' . $href . ' ' . $onclick . ' rel="' . $button->get('options') . '">';
					$html[] = '<i class="icon-' . $button->get('name') . '"></i> ';
					$html[] = $button->get('text') . '</a>';
				}
			}

			$html[] = '</div>';
			$html[] = '</div>';
		}

		return implode("\n", $html);
	}
}
