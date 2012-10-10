<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.codemirror
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
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
class plgEditorCodemirror extends JPlugin
{
	/**
	 * Base path for editor files
	 */
	protected $_basePath = 'media/editors/codemirror/';

	/**
	 * Initialises the Editor.
	 *
	 * @return	string	JavaScript Initialization string.
	 */
	public function onInit()
	{
		JHtml::_('behavior.framework');
		$uncompressed	= JFactory::getApplication()->getCfg('debug') ? '-uncompressed' : '';
		JHtml::_('script', $this->_basePath . 'js/codemirror'.$uncompressed.'.js', false, false, false, false);
		JHtml::_('stylesheet', $this->_basePath . 'css/codemirror.css');

		return '';
	}

	/**
	 * Copy editor content to form field.
	 *
	 * @param	string	$id	The id of the editor field.
	 *
	 * @return string Javascript
	 */
	public function onSave($id)
	{
		return "document.getElementById('$id').value = Joomla.editors.instances['$id'].getCode();\n";
	}

	/**
	 * Get the editor content.
	 *
	 * @param	string	$id	The id of the editor field.
	 *
	 * @return string Javascript
	 */
	public function onGetContent($id)
	{
		return "Joomla.editors.instances['$id'].getCode();\n";
	}

	/**
	 * Set the editor content.
	 *
	 * @param	string	$id			The id of the editor field.
	 * @param	string	$content	The content to set.
	 *
	 * @return string Javascript
	 */
	public function onSetContent($id, $content)
	{
		return "Joomla.editors.instances['$id'].setCode($content);\n";
	}

	/**
	 * Adds the editor specific insert method.
	 *
	 * @return boolean
	 */
	public function onGetInsertMethod()
	{
		static $done = false;

		// Do this only once.
		if (!$done)
		{
			$done = true;
			$doc = JFactory::getDocument();
			$js = "\tfunction jInsertEditorText(text, editor) {
					Joomla.editors.instances[editor].replaceSelection(text);\n
			}";
			$doc->addScriptDeclaration($js);
		}

		return true;
	}

	/**
	 * Display the editor area.
	 *
	 * @param	string	$name		The control name.
	 * @param	string	$html		The contents of the text area.
	 * @param	string	$width		The width of the text area (px or %).
	 * @param	string	$height		The height of the text area (px or %).
	 * @param	int		$col		The number of columns for the textarea.
	 * @param	int		$row		The number of rows for the textarea.
	 * @param	boolean	$buttons	True and the editor buttons will be displayed.
	 * @param	string	$id			An optional ID for the textarea (note: since 1.6). If not supplied the name is used.
	 * @param	string	$asset
	 * @param	object	$author
	 * @param	array	$params		Associative array of editor parameters.
	 *
	 * @return string HTML
	 */
	public function onDisplay($name, $content, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = array())
	{
		if (empty($id)) {
			$id = $name;
		}

		// Only add "px" to width and height if they are not given as a percentage
		if (is_numeric($width)) {
			$width .= 'px';
		} elseif ($width == '100%') {
			$width = 'auto';
		}

		// if height is given in pixels, compare it to the minheight param and keep the bigger of the two
		$height = trim($height, 'px');
		$minheight = (int) $this->params->get('editor_minheight', 100);
		if (is_numeric($height)) {
			$height = max($height, $minheight);
			$height .= 'px';
		}

		// Must pass the field id to the buttons in this editor.
		$buttons = $this->_displayButtons($id, $buttons, $asset, $author);

		$compressed	= JFactory::getApplication()->getCfg('debug') ? '-uncompressed' : '';

		// Default syntax
		$parserFile = 'parsexml.js';
		$styleSheet = array('xmlcolors.css');

		// Look if we need special syntax coloring.
		$syntax = JFactory::getApplication()->getUserState('editor.source.syntax');

		if ($syntax) {
			switch($syntax)
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
					$parserFile = array('parsexml.js', 'parsecss.js', 'tokenizejavascript.js', 'parsejavascript.js', 'tokenizephp.js', 'parsephp.js', 'parsephphtmlmixed.js');
					$styleSheet = array('xmlcolors.css', 'jscolors.css', 'csscolors.css', 'phpcolors.css');
					break;

				default:
					break;
			} //switch
		}

		foreach ($styleSheet as &$style)
		{
			$style = JURI::root(true).'/'.$this->_basePath.'css/'.$style;
		}
		$styleSheet[] = $this->getFontStyleSheet();

		$options	= new stdClass;

		$options->basefiles		= array('basefiles'.$compressed.'.js');
		$options->path			= JURI::root(true).'/'.$this->_basePath.'js/';
		$options->parserfile	= $parserFile;
		$options->stylesheet	= $styleSheet;
		$options->height		= $height;
		$options->width			= $width;
		$options->continuousScanning = 500;
		$options->iframeClass	= $this->params->get('editor_shadow') ? 'CodeMirror-frame-shadow' : 'CodeMirror-frame';

		if ($this->params->get('linenumbers', 0)) {
			$options->lineNumbers	= true;
			$options->textWrapping	= false;
		}

		if ($this->params->get('tabmode', '') == 'shift') {
			$options->tabMode = 'shift';
		}

		$editorStyles = $this->getEditorStyles();

		$html = array();
		$html[]	= "<textarea name=\"$name\" id=\"$id\" cols=\"$col\" rows=\"$row\">$content</textarea>";
		$html[] = $buttons;
		$html[] = '<script type="text/javascript">';
		$html[] = '(function() {';
		$html[] = '	var setStyles = function() { Object.append( editor.editor.container.style, '.json_encode($editorStyles).'); }';
		$html[] = '	var editor = CodeMirror.fromTextArea("'.$id.'", Object.merge('.json_encode($options).',{onLoad:setStyles}));';
		$html[] = '	Joomla.editors.instances[\''.$id.'\'] = editor;';
		$html[] = '})()';
		$html[] = '</script>';

		return implode("\n", $html);
	}

	/**
	 * Displays the editor buttons.
	 *
	 * @param string $name
	 * @param mixed $buttons [array with button objects | boolean true to display buttons]
	 *
	 * @return string HTML
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
			if (is_string($result) && trim($result)) {
				$html[] = $result;
			}
		}

		if (is_array($buttons) || (is_bool($buttons) && $buttons)) {
			$results = $this->_subject->getButtons($name, $buttons, $asset, $author);

			// This will allow plugins to attach buttons or change the behavior on the fly using AJAX
			$html[] = '<div id="editor-xtd-buttons">';
			$html[] = '<div class="btn-toolbar">';

			foreach ($results as $button)
			{
				// Results should be an object
				if ($button->get('name')) {
					$modal		= ($button->get('modal')) ? 'class="modal-button btn"' : null;
					$href		= ($button->get('link')) ? ' class="btn" href="'.JURI::base().$button->get('link').'"' : null;
					$onclick	= ($button->get('onclick')) ? 'onclick="'.$button->get('onclick').'"' : null;
					$title      = ($button->get('title')) ? $button->get('title') : $button->get('text');
					$html[] = '<a '.$modal.' title="'.$title.'" '.$href.' '.$onclick.' rel="'.$button->get('options').'">';
					$html[] = '<i class="icon-' . $button->get('name'). '"></i> ';
					$html[] = $button->get('text').'</a>';
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
	 * @access	protected
	 */
	protected function getFontStyleSheet()
	{
		$key = $this->params->get('font_family', 0);
		$styleSheets = array(
			'anonymous_pro'		=> 'http://fonts.googleapis.com/css?family=Anonymous+Pro:400,700,400italic,700italic&subset=latin,latin-ext&v2',
			'cousine'			=> 'http://fonts.googleapis.com/css?family=Cousine:400,700italic,700,400italic&subset=latin,latin-ext&v2',
			'droid_sans_mono'	=> 'http://fonts.googleapis.com/css?family=Droid+Sans+Mono&subset=latin,latin-ext&v2',
			'inconsolata'		=> 'http://fonts.googleapis.com/css?family=Inconsolata&subset=latin,latin-ext&v2',
			'lekton'			=> 'http://fonts.googleapis.com/css?family=Lekton:400,400italic,700&subset=latin,latin-ext&v2',
			'nova_mono'			=> 'http://fonts.googleapis.com/css?family=Nova+Mono&subset=latin,latin-ext&v2',
			'ubuntu_mono'		=> 'http://fonts.googleapis.com/css?family=Ubuntu+Mono&subset=latin,latin-ext&v2',
			'vt323'				=> 'http://fonts.googleapis.com/css?family=VT323&subset=latin,latin-ext&v2'
		);
		
		return isset($styleSheets[$key]) ? $styleSheets[$key] : '';
	}
	
	/**
	 * Gets style declarations for using the select font and size from params
	 * returning as array for json encoding
	 *
	 * @return	array
	 * @access	protected
	 */
	protected function getEditorStyles()
	{
		$key = $this->params->get('font_family', 0);
		$fonts = array(
			'anonymous_pro'		=> 'Anonymous Pro, monospace',
			'cousine'			=> 'Cousine, monospace',
			'droid_sans_mono'	=> 'Droid Sans Mono, monospace',
			'inconsolata'		=> 'Inconsolata, monospace',
			'lekton'			=> 'Tekton, monospace',
			'nova_mono'			=> 'Nova Mono, monospace',
			'ubuntu_mono'		=> 'Ubuntu Mono, monospace',
			'vt323'				=> 'VT323, monospace'
		);
		
		$size = (int) $this->params->get('font_size', 10);
		
		$styles = array(
			'font-family' => isset($fonts[$key]) ? $fonts[$key] : 'monospace',
			'font-size' => $size.'px'
		);
		return $styles;
	}
}
