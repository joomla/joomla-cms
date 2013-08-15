<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.codemirror
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
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
	protected $basePath = 'media/editors/codemirror/';

	/**
	 * Initialises the Editor.
	 *
	 * @return  string	JavaScript Initialization string.
	 */
	public function onInit()
	{
		JHtml::_('behavior.framework');
		$uncompressed	= JFactory::getApplication()->getCfg('debug') ? '-uncompressed' : '';
		JHtml::_('script', $this->basePath . 'js/codemirror' . $uncompressed . '.js', false, false, false, false);
		JHtml::_('stylesheet', $this->basePath . 'css/codemirror.css');

		return '';
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
		return "document.getElementById('$id').value = Joomla.editors.instances['$id'].getCode();\n";
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
		return "Joomla.editors.instances['$id'].getCode();\n";
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
		return "Joomla.editors.instances['$id'].setCode($content);\n";
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
	 * @param   string   $asset    Not used.
	 * @param   object   $author   Not used.
	 * @param   array    $params   Associative array of editor parameters.
	 *
	 * @return  string HTML
	 */
	public function onDisplay(
		$name, $content, $width, $height, $col, $row,
		$buttons = true, $id = null, $asset = null, $author = null, $params = array())
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
		$buttons = $this->_displayButtons($id, $buttons);

		$compressed	= JFactory::getApplication()->getCfg('debug') ? '-uncompressed' : '';

		// Default syntax
		$parserFile = 'parsexml.js';
		$styleSheet = array('xmlcolors.css');

		// Look if we need special syntax coloring.
		$syntax = JFactory::getApplication()->getUserState('editor.source.syntax');

		if ($syntax)
		{
			switch ($syntax)
			{
				case 'css':
					$parserFile = 'parsecss.js';
					$styleSheet = array('csscolors.css');
					break;

				case 'js':
					$parserFile = array('tokenizejavascript.js', 'parsejavascript.js');
					$styleSheet = array('jscolors.css');
					break;

				case 'html':
					$parserFile = array('parsexml.js', 'parsecss.js', 'tokenizejavascript.js', 'parsejavascript.js', 'parsehtmlmixed.js');
					$styleSheet = array('xmlcolors.css', 'jscolors.css', 'csscolors.css');
					break;

				case 'php':
					$parserFile = array(
						'parsexml.js', 'parsecss.js', 'tokenizejavascript.js', 'parsejavascript.js',
						'tokenizephp.js', 'parsephp.js', 'parsephphtmlmixed.js');
					$styleSheet = array('xmlcolors.css', 'jscolors.css', 'csscolors.css', 'phpcolors.css');
					break;

				default:
					break;
			}
		}

		foreach ($styleSheet as &$style)
		{
			$style = JURI::root(true) . '/' . $this->basePath . 'css/' . $style;
		}

		// This will be a style sheet hosted by fonts.googleapis.com which loads the selected web font.
		$styleSheet[] = $this->getFontStyleSheet();

		$options	= new stdClass;

		$options->basefiles		= array('basefiles' . $compressed . '.js');
		$options->path			= JURI::root(true) . '/' . $this->basePath . 'js/';
		$options->parserfile	= $parserFile;
		$options->stylesheet	= $styleSheet;
		$options->height		= $height;
		$options->width			= $width;
		$options->continuousScanning = 500;

		if ($this->params->get('linenumbers', 0))
		{
			$options->lineNumbers	= true;
			$options->textWrapping	= false;
		}

		if ($this->params->get('tabmode', '') == 'shift')
		{
			$options->tabMode = 'shift';
		}

		// The css rules to display as the selected font and size.
		$editorStyles = $this->getEditorStyles();

		$html = array();
		$html[] = '<style type="text/css">';
		$html[] = '.CodeMirror-line-numbers {';

		foreach ($editorStyles as $p => $v)
		{
			$html[] = $p . ': ' . $v . ';';
		}

		$html[] = '}';
		$html[] = '</style>';
		$html[]	= "<textarea name=\"$name\" id=\"$id\" cols=\"$col\" rows=\"$row\">$content</textarea>";
		$html[] = $buttons;
		$html[] = '<script type="text/javascript">';
		$html[] = '(function() {';
		$html[] = 'var setStyles = function(cm) { Object.append( cm.editor.container.style, ' . json_encode($editorStyles) . '); }';
		$html[] = 'var options = Object.merge(' . json_encode($options) . ', {"onLoad": setStyles});';
		$html[] = 'var editor = CodeMirror.fromTextArea(' . json_encode($id) . ', options);';
		$html[] = 'Joomla.editors.instances[' . json_encode($id) . '] = editor;';
		$html[] = '})();';
		$html[] = '</script>';

		return implode("\n", $html);
	}

	/**
	 * Displays the editor buttons.
	 *
	 * @param   string  $name     The name (actually id) of the control.
	 * @param   mixed   $buttons  [array with button objects | boolean true to display buttons]
	 *
	 * @return  string  HTML
	 */
	protected function _displayButtons($name, $buttons)
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
			$results = $this->_subject->getButtons($name, $buttons);

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

	/**
	 * Gets the url of a font stylesheet (from google web fonts) based on param values
	 *
	 * @return	string	$styleSheet a url (or empty string)
	 */
	protected function getFontStyleSheet()
	{
		$google = 'http://fonts.googleapis.com/css?';

		$key = $this->params->get('font_family', 0);
		$styleSheets = array(
			'anonymous_pro'		=> 'family=Anonymous+Pro',
			'cousine'			=> 'family=Cousine',
			'cutive_mono'		=> 'family=Cutive+Mono',
			'droid_sans_mono'	=> 'family=Droid+Sans+Mono',
			'inconsolata'		=> 'family=Inconsolata',
			'lekton'			=> 'family=Lekton',
			'nova_mono'			=> 'family=Nova+Mono',
			'oxygen_mono'		=> 'family=Oxygen+Mono',
			'press_start_2p'	=> 'family=Press+Start+2P',
			'pt_mono'			=> 'family=PT+Mono',
			'share_tech_mono'	=> 'family=Share+Tech+Mono',
			'source_code_pro'	=> 'family=Source+Code+Pro',
			'ubuntu_mono'		=> 'family=Ubuntu+Mono',
			'vt323'				=> 'family=VT323'
		);

		return isset($styleSheets[$key]) ? $google . $styleSheets[$key] : '';
	}

	/**
	 * Gets style declarations for using the selected font, size, and line-height from params
	 * returning as array for json encoding
	 *
	 * @return	array
	 */
	protected function getEditorStyles()
	{
		$key = $this->params->get('font_family', 0);
		$fonts = array(
			'anonymous_pro'		=> '\'Anonymous Pro\', monospace',
			'cousine'			=> 'Cousine, monospace',
			'cutive_mono'		=> '\'Cutive Mono\', monospace',
			'droid_sans_mono'	=> '\'Droid Sans Mono\', monospace',
			'inconsolata'		=> 'Inconsolata, monospace',
			'lekton'			=> 'Tekton, monospace',
			'nova_mono'			=> '\'Nova Mono\', monospace',
			'oxygen_mono'		=> '\'Oxygen Mono\', monospace',
			'press_start_2p'	=> '\'Press Start 2P\', monospace',
			'pt_mono'			=> '\'PT Mono\', monospace',
			'share_tech_mono'	=> '\'Share Tech Mono\', monospace',
			'source_code_pro'	=> '\'Source Code Pro\', monospace',
			'ubuntu_mono'		=> '\'Ubuntu Mono\', monospace',
			'vt323'				=> 'VT323, monospace'
		);

		$size = (int) $this->params->get('font_size', 10);

		$line = $this->params->get('line_height', 1.2);

		$styles = array(
			'font-family' => isset($fonts[$key]) ? $fonts[$key] : 'monospace',
			'font-size' => $size . 'px',
			'line-height' => $line . 'em'
		);

		return $styles;
	}
}
