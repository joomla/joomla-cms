<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined ( '_JEXEC' ) or die ();

/**
 * CodeMirror Editor Plugin.
 *
 * @package Joomla.Plugin
 * @subpackage Editors.codemirror
 * @since 1.6
 */
class plgEditorCodemirror extends JPlugin {
	/**
	 * Base path for editor files
	 */
	protected $_basePath = 'media/editors/codemirror/';
	
	/**
	 * Initialises the Editor.
	 *
	 * @return string Initialization string.
	 */
	public function onInit() {
		JHtml::_ ( 'core' );
		$uncompressed = JFactory::getApplication ()->getCfg ( 'debug' ) ? '-uncompressed' : '';
		JHtml::_ ( 'script', $this->_basePath . 'js/codemirror' . $uncompressed . '.js', false, false, false, false );
		JHtml::_ ( 'stylesheet', $this->_basePath . 'css/codemirror.css' );
		
		return '';
	}
	
	/**
	 * Copy editor content to form field.
	 *
	 * @param $id string
	 *       	 of the editor field.
	 *       	
	 * @return string Javascript
	 */
	public function onSave($id) {
		return "document.getElementById('$id').value = Joomla.editors.instances['$id'].getValue();\n";
	}
	
	/**
	 * Get the editor content.
	 *
	 * @param $id string
	 *       	 of the editor field.
	 *       	
	 * @return string Javascript
	 */
	public function onGetContent($id) {
		return "Joomla.editors.instances['$id'].getValue();\n";
	}
	
	/**
	 * Set the editor content.
	 *
	 * @param $id string
	 *       	 of the editor field.
	 * @param $content string
	 *       	 to set.
	 *       	
	 * @return string Javascript
	 */
	public function onSetContent($id, $content) {
		return "Joomla.editors.instances['$id'].getValue($content);\n";
	}
	
	/**
	 * Adds the editor specific insert method.
	 *
	 * @return boolean
	 */
	public function onGetInsertMethod() {
		static $done = false;
		
		// Do this only once.
		if (! $done) {
			$done = true;
			$doc = JFactory::getDocument ();
			$js = "\tfunction jInsertEditorText(text, editor) {
					Joomla.editors.instances[editor].replaceSelection(text);\n
			}";
			$doc->addScriptDeclaration ( $js );
		}
		
		return true;
	}
	
	/**
	 * Display the editor area.
	 *
	 * @param $name string
	 *       	 name.
	 * @param $html string
	 *       	 of the text area.
	 * @param $width string
	 *       	 of the text area (px or %).
	 * @param $height string
	 *       	 of the text area (px or %).
	 * @param $col int
	 *       	 of columns for the textarea.
	 * @param $row int
	 *       	 of rows for the textarea.
	 * @param $buttons boolean
	 *       	 the editor buttons will be displayed.
	 * @param $id string
	 *       	 ID for the textarea (note: since 1.6). If not supplied the
	 *       	 name is used.
	 * @param $asset string       	
	 * @param $author object       	
	 * @param $params array
	 *       	 of editor parameters.
	 *       	
	 * @return string HTML
	 */
	public function onDisplay($name, $content, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = array()) {
		if (empty ( $id )) {
			$id = $name;
		}
		
		// Only add "px" to width and height if they are not given as a
		// percentage
		if (is_numeric ( $width )) {
			$width .= 'px';
		}
		
		if (is_numeric ( $height )) {
			$height .= 'px';
		}
		
		// Must pass the field id to the buttons in this editor.
		$buttons = $this->_displayButtons ( $id, $buttons, $asset, $author );
		
		$compressed = JFactory::getApplication ()->getCfg ( 'debug' ) ? '-uncompressed' : '';
		
		// Default syntax
		$parserFile = 'xml/xml';
		$mode = 'text/html';
		// Look if we need special syntax coloring.
		$syntax = JFactory::getApplication ()->getUserState ( 'editor.source.syntax' );
		
		if ($syntax) {
			switch ($syntax) {
				case 'css' :
					$parserFile = 'css/css';
					$mode = 'text/css';
					break;
				
				case 'js' :
					$parserFile = 'javascript/javascript';
					$mode = 'text/javascript';
					break;
				
				case 'html' :
					$parserFile = 'xml/xm';
					$mode = 'text/html';
					break;
				case 'php' :
					$parserFile = array ('xml/xml', 'javascript/javascript', 'css/css', 'clike/clike', 'php/php');
					$mode = 'application/x-httpd-php';
					break;
				
				default :
					break;
			} // switch
		}
		JHtml::_ ( 'core' );
		if (is_string ( $parserFile )) {
			JHtml::_ ( 'script', $this->_basePath . 'js/mode/' . $parserFile . $compressed . '.js', false, false, false, false );
		} else {
			foreach ( $parserFile as $parser ) {
				JHtml::_ ( 'script', $this->_basePath . 'js/mode/' . $parser  . $compressed . '.js', false, false, false, false );
			}
		}
		
		// inline style Width & Height
		$document = JFactory::getDocument ();
		$styleFormat = '.CodeMirror-scroll {height:"%1$s";%3$swidth:"%2$s"}';
		$document->addStyleDeclaration ( sprintf ( $styleFormat, $height, $width, PHP_EOL ) );
		
		// CodeMirror options
		$options = new stdClass ();
		$options->mode = $mode;
		$options->lineWrapping = true;
		// theme
		switch ($this->params->get ( 'theme', 'monokai' )) {
			case 'cobalt' :
				$theme = 'cobalt';
				break;
			case 'eclipse' :
				$theme = 'eclipse';
				break;
			case 'elegant' :
				$theme = 'elegant';
				break;
			case 'monokai' :
				$theme = 'monokai';
				break;
			case 'neat' :
				$theme = 'neat';
				break;
			case 'night' :
				$theme = 'night';
				break;
			case 'rubyblue' :
				$theme = 'rubyblue';
				break;
			default :
				$theme = NULL;
				break;
		}
		if (is_null ( $theme ) === false) {
			JHtml::_ ( 'stylesheet', $this->_basePath . 'css/theme/' . $theme . '.css' );
			$options->theme = $theme;
		}
		if ($this->params->get ( 'linenumbers', 0 )) {
			$options->lineNumbers = true;
			$options->textWrapping = false;
		}
		
		// tabmode codemirror 2.2 is property name indentWithTabs 
		if ($this->params->get('tabmode', '') === 'shift') {
		  $options->indentWithTabs = true;
		  $options->tabSize = 4;
		  $options->indentUnit = 4;
		  // add visibletabs
		  $visibletabs = array();
		  $visibletabs[] = '.cm-tab:after {';
		  $visibletabs[] = ' content: "\21e5";';
		  $visibletabs[] = ' display: -moz-inline-block;';
		  $visibletabs[] = ' display: -webkit-inline-block;';
		  $visibletabs[] = ' display: inline-block;';
		  $visibletabs[] = ' width: 0px;';
		  $visibletabs[] = ' position: relative;';
		  $visibletabs[] = ' overflow: visible;';
		  $visibletabs[] = ' left: -1.4em;';
		  $visibletabs[] = ' color: #aaa;';
		  $visibletabs[] = '}';
		  $document->addStyleDeclaration ( implode ( "\n", $visibletabs ));
		  
		}
		$html = array ();
		$html [] = "<textarea name=\"$name\" id=\"$id\" cols=\"$col\" rows=\"$row\">$content</textarea>";
		$html [] = $buttons;
		$html [] = '<script type="text/javascript">';
		$html [] = '(function() {';
		$html [] = 'var editor = CodeMirror.fromTextArea(document.getElementById("' . $id . '"), '. json_encode ( $options ).');';
		$html [] = 'Joomla.editors.instances[\'' . $id . '\'] = editor;';
		$html [] = '})()';
		$html [] = '</script>';
		
		return implode ( "\n", $html );
	}
	
	/**
	 * Displays the editor buttons.
	 *
	 * @param $name string       	
	 * @param $buttons mixed
	 *       	 [array with button objects | boolean true to display buttons]
	 *       	
	 * @return string HTML
	 */
	protected function _displayButtons($name, $buttons, $asset, $author) {
		// Load modal popup behavior
		JHtml::_ ( 'behavior.modal', 'a.modal-button' );
		
		$args ['name'] = $name;
		$args ['event'] = 'onGetInsertMethod';
		
		$html = array ();
		$results [] = $this->update ( $args );
		
		foreach ( $results as $result ) {
			if (is_string ( $result ) && trim ( $result )) {
				$html [] = $result;
			}
		}
		
		if (is_array ( $buttons ) || (is_bool ( $buttons ) && $buttons)) {
			$results = $this->_subject->getButtons ( $name, $buttons, $asset, $author );
			
			// This will allow plugins to attach buttons or change the behavior
			// on the fly using AJAX
			$html [] = '<div id="editor-xtd-buttons">';
			//
			// var_dump($results);
			// exit;
			foreach ( $results as $button ) {
				// Results should be an object
				if ($button->get ( 'name' )) {
					$modal = ($button->get ( 'modal' )) ? 'class="modal-button"' : null;
					$href = ($button->get ( 'link' )) ? 'href="' . JURI::base () . $button->get ( 'link' ) . '"' : null;
					$onclick = ($button->get ( 'onclick' )) ? 'onclick="' . $button->get ( 'onclick' ) . '"' : null;
					$title = ($button->get ( 'title' )) ? $button->get ( 'title' ) : $button->get ( 'text' );
					$html [] = '<div class="button2-left"><div class="' . $button->get ( 'name' ) . '">';
					$html [] = '<a ' . $modal . ' title="' . $title . '" ' . $href . ' ' . $onclick . ' rel="' . $button->get ( 'options' ) . '">';
					$html [] = $button->get ( 'text' ) . '</a></div></div>';
				}
			}
			$html [] = '</div>';
		}
		
		return implode ( "\n", $html );
	}
}
