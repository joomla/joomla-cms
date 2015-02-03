<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.codemirror
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * CodeMirror Editor Plugin.
 *
 * @since  1.6
 */
class PlgEditorCodemirror extends JPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  12.3
	 */
	protected $autoloadLanguage = true;

	/**
	 * Base path for editor files.
	 *
	 * @var string
	 */
	protected $basePath = 'media/editors/codemirror/';

	/**
	 * Mapping of syntax to CodeMirror modes.
	 *
	 * @var array
	 */
	protected $modeAlias = array(
			'html' => 'htmlmixed',
			'ini'  => 'properties'
		);

	/**
	 * The key combo to start full-screen editing.
	 *
	 * @var string
	 */
	protected $fullScreenCombo;

	/**
	 * Initialises the Editor.
	 *
	 * @return	string	JavaScript Initialization string.
	 */
	public function onInit()
	{
		static $done = false;

		// Do this only once.
		if ($done)
		{
			return true;
		}

		$done = true;

		JHtml::_('behavior.framework');
		JHtml::_('script', $this->basePath . 'lib/codemirror.js');
		JHtml::_('script', $this->basePath . 'lib/addons.js');
		JHtml::_('stylesheet', $this->basePath . 'lib/codemirror.css');

		JFactory::getDocument()
			->addScriptDeclaration($this->getInitScript())
			->addStyleDeclaration($this->getExtraStyles());

		return '';
	}

	/**
	 * A script to set up some defaults for CodeMirror.
	 *
	 * @return  string
	 */
	protected function getInitScript()
	{
		$fskeys = $this->params->get('fullScreenMod', array());
		$fskeys[] = $this->params->get('fullScreen', 'F10');
		$this->fullScreenCombo = implode('-', $fskeys);

		$modeURL = JURI::root(true) . '/media/editors/codemirror/mode/%N/%N.js';

		$script = array(
			';(function (cm) {',
				// The legacy combo for fullscreen. Remove it later now there is a configurable one.
				'cm.keyMap["default"]["Ctrl-Q"] = function (cm) {',
					'cm.setOption("fullScreen", !cm.getOption("fullScreen"));',
				'};',
				'cm.keyMap["default"]["' . $this->fullScreenCombo . '"] = function (cm) {',
					'cm.setOption("fullScreen", !cm.getOption("fullScreen"));',
				'};',
				'cm.keyMap["default"]["Esc"] = function (cm) {',
					'cm.getOption("fullScreen") && cm.setOption("fullScreen", false);',
				'};',
				'cm.modeURL = ' . json_encode($modeURL) . ';',
			'}(CodeMirror));'
		);

		return implode(' ', $script);
	}

	/**
	 * Some styles not included in the usual codemirror.css.
	 *
	 * @return  string
	 */
	protected function getExtraStyles()
	{
		// Get our custom styles from a css file
		$styles = JFile::read(__DIR__ . (JDEBUG ? '/styles.css' : '/styles.min.css'));

		// Set the active line color.
		$color = $this->params->get('activeLineColor', '#a4c2eb');
		$r = hexdec($color{1} . $color{2});
		$g = hexdec($color{3} . $color{4});
		$b = hexdec($color{5} . $color{6});
		$styles .= ' .CodeMirror-activeline-background {background:rgba(' . $r . ', ' . $g . ', ' . $b . ', .5);}';

		// Set the color for matched tags.
		$color = $this->params->get('highlightMatchColor', '#fa542f');
		$r = hexdec($color{1} . $color{2});
		$g = hexdec($color{3} . $color{4});
		$b = hexdec($color{5} . $color{6});
		$styles .= ' .CodeMirror-matchingtag {background:rgba(' . $r . ', ' . $g . ', ' . $b . ', .5);}';

		// Set the font styles.
		$styles .= ' .CodeMirror {' . implode(' ', $this->getEditorStyles()) . '} ';

		return $styles;
	}

	/**
	 * Copy editor content to form field.
	 *
	 * @param   string  $id  The id of the editor field.
	 *
	 * @return  string  Javascript
	 */
	public function onSave($id)
	{
		return sprintf('document.getElementById(%1$s).value = Joomla.editors.instances[%1$s].getValue();', json_encode((string) $id));
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
		return sprintf('Joomla.editors.instances[%1$s].getValue();', json_encode((string) $id));
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
		return sprintf('Joomla.editors.instances[%1$s].setValue(%2$s);', json_encode((string) $id), json_encode((string) $content));
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
		if ($done)
		{
			return true;
		}

		$done = true;

		$js = ";function jInsertEditorText(text, editor) { Joomla.editors.instances[editor].replaceSelection(text); }\n";
		JFactory::getDocument()->addScriptDeclaration($js);

		return true;
	}

	/**
	 * Display the editor area.
	 *
	 * @param   string   $name     The control name.
	 * @param   string   $content  The contents of the text area.
	 * @param   string   $width    The width of the text area (px or %).
	 * @param   string   $height   The height of the text area (px or %).
	 * @param   int      $col      The number of columns for the textarea.
	 * @param   int      $row      The number of rows for the textarea.
	 * @param   boolean  $buttons  True and the editor buttons will be displayed.
	 * @param   string   $id       An optional ID for the textarea (note: since 1.6). If not supplied the name is used.
	 * @param   string   $asset    Not used.
	 * @param   object   $author   Not used.
	 * @param   array    $params   Associative array of editor parameters.
	 *
	 * @return  string  HTML
	 */
	public function onDisplay(
		$name, $content, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = array())
	{
		$id = empty($id) ? $name : $id;

		// Must pass the field id to the buttons in this editor.
		$buttons = $this->displayButtons($id, $buttons, $asset, $author);

		// Only add "px" to width and height if they are not given as a percentage.
		$width .= is_numeric($width) ? 'px' : '';
		$height .= is_numeric($height) ? 'px' : '';

		// Options for the CodeMirror constructor.
		$options = new stdClass;

		// Should we focus on the editor on load?
		$options->autofocus	= (boolean) $this->params->get('autoFocus', true);

		// Until there's a fix for the overflow problem, always wrap lines.
		$options->lineWrapping = true;

		// Add styling to the active line.
		$options->styleActiveLine = (boolean) $this->params->get('activeLine', true);

		// Do we use line numbering?
		if ($options->lineNumbers = (boolean) $this->params->get('lineNumbers', 0))
		{
			$options->gutters[] = 'CodeMirror-linenumbers';
		}

		// Do we use code folding?
		if ($options->foldGutter = (boolean) $this->params->get('codeFolding', 1))
		{
			$options->gutters[] = 'CodeMirror-foldgutter';
		}

		// Do we use a marker gutter?
		if ($options->foldGutter = (boolean) $this->params->get('markerGutter', $this->params->get('marker-gutter', 0)))
		{
			$options->gutters[] = 'CodeMirror-markergutter';
		}

		// Load the syntax mode.
		$syntax = $this->params->get('syntax', 'html');
		$options->mode = isset($this->modeAlias[$syntax]) ? $this->modeAlias[$syntax] : $syntax;

		// Load the theme if specified.
		if ($theme = $this->params->get('theme'))
		{
			$options->theme = $theme;
			$this->loadTheme($options->theme);
		}

		// Special options for tagged modes (xml/html).
		if (in_array($options->mode, array('xml', 'htmlmixed', 'htmlembedded', 'php')))
		{
			// Autogenerate closing tags (html/xml only).
			$options->autoCloseTags = (boolean) $this->params->get('autoCloseTags', true);

			// Highlight the matching tag when the cursor is in a tag (html/xml only).
			$options->matchTags = (boolean) $this->params->get('matchTags', true);
		}

		// Special options for non-tagged modes.
		if (!in_array($options->mode, array('xml', 'htmlmixed', 'htmlembedded')))
		{
			// Autogenerate closing brackets.
			$options->autoCloseBrackets = (boolean) $this->params->get('autoCloseBrackets', true);

			// Highlight the matching bracket.
			$options->matchBrackets = (boolean) $this->params->get('matchBrackets', true);
		}

		$options->scrollbarStyle = $this->params->get('scrollbarStyle', 'native');

		// Vim Keybindings.
		$options->vimMode = (boolean) $this->params->get('vimKeyBinding', 0);

		$html = array();
		$html[]	= '<p class="label">' . JText::sprintf('PLG_CODEMIRROR_TOGGLE_FULL_SCREEN', $this->fullScreenCombo) . '</p>';
		$html[]	= '<textarea name="' . $name . '" id="' . $id . '" cols="' . $col . '" rows="' . $row . '">' . $content . '</textarea>';
		$html[] = $buttons;
		$html[] = '<script type="text' . '/javascript">';
		$html[] = '(function (id, options) {';
		$html[] = '    Joomla.editors.instances[id] = CodeMirror.fromTextArea(document.getElementById(id), options);';
		$html[] = '    CodeMirror.autoLoadMode(Joomla.editors.instances[id], options.mode);';
		$html[] = '    Joomla.editors.instances[id].on("gutterClick", function (cm, n, gutter) {';
		$html[] = '        if (gutter != "CodeMirror-markergutter") { return; }';
		$html[] = '        var info = cm.lineInfo(n);';
		$html[] = '        var hasMarker = !!info.gutterMarkers && !!info.gutterMarkers["CodeMirror-markergutter"];';
		$html[] = '        var makeMarker = function () {';
		$html[] = '            var marker = document.createElement("div");';
		$html[] = '            marker.className = "CodeMirror-markergutter-mark";';
		$html[] = '            return marker;';
		$html[] = '        };';
		$html[] = '        cm.setGutterMarker(n, "CodeMirror-markergutter", hasMarker ? null : makeMarker());';
		$html[] = '    });';

		// Listen for Bootstrap's 'shown' event. If this editor was in a hidden element when created, it may need to be refreshed.
		$html[] = '		!!jQuery && jQuery(function ($) {';
		$html[] = '			$(document.body).on("shown shown.bs.tab shown.bs.modal", function () {';
		$html[] = '				Joomla.editors.instances[id].refresh();';
		$html[] = '			});';
		$html[] = '		});';

		$html[] = '}(' . json_encode($id) . ', ' . json_encode($options) . '));';
		$html[] = '</script>';

		return implode("\n", $html);
	}

	/**
	 * Loads a CodeMirror theme file.
	 *
	 * @param   string  $theme  The theme to load.
	 *
	 * @return  void
	 */
	protected function loadTheme($theme)
	{
		static $loaded = array();

		if (in_array($theme, $loaded))
		{
			return;
		}

		$loaded[] = $theme;

		JHtml::_('stylesheet', $this->basePath . 'theme/' . $theme . '.css');
	}

	/**
	 * Displays the editor buttons.
	 *
	 * @param   string  $name     Button name.
	 * @param   mixed   $buttons  [array with button objects | boolean true to display buttons]
	 * @param   mixed   $asset    Unused.
	 * @param   mixed   $author   Unused.
	 *
	 * @return  string  HTML
	 */
	protected function displayButtons($name, $buttons, $asset, $author)
	{
		$return = '';

		$args = array(
			'name'  => $name,
			'event' => 'onGetInsertMethod'
		);

		$results = (array) $this->update($args);

		if ($results)
		{
			foreach ($results as $result)
			{
				if (is_string($result) && trim($result))
				{
					$return .= $result;
				}
			}
		}

		if (is_array($buttons) || (is_bool($buttons) && $buttons))
		{
			$buttons = $this->_subject->getButtons($name, $buttons, $asset, $author);

			$return .= JLayoutHelper::render('joomla.editors.buttons', $buttons);
		}

		return $return;
	}

	/**
	 * Gets style declarations for using the selected font, size, and line-height from params
	 * returning as array for json encoding
	 *
	 * @return  array
	 */
	protected function getEditorStyles()
	{
		$font = $this->params->get('fontFamily', 0);
		$info = $this->getFontInfo($font);

		if (isset($info) && isset($info->url))
		{
			JFactory::getDocument()->addStylesheet($info->url);
		}

		$styles = array(
			'font-family: ' . ((isset($info) && isset($info->css)) ? $info->css . '!important' : 'monospace') . ';',
			'font-size: ' . $this->params->get('fontSize', 13) . 'px;',
			'line-height: ' . $this->params->get('lineHeight', 1.2) . 'em;',
			'border: ' . '1px solid #ccc;'
		);

		return $styles;
	}

	/**
	 * Gets font info from the json data file
	 *
	 * @param   string  $font  A key from the $fonts array.
	 *
	 * @return  object
	 */
	protected function getFontInfo($font)
	{
		static $fonts;

		if (!$fonts)
		{
			$fonts = json_decode(JFile::read(__DIR__ . '/fonts.json'), true);
		}

		return isset($fonts[$font]) ? (object) $fonts[$font] : null;
	}
}
