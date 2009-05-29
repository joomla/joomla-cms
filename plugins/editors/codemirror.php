<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * CodeMirror Editor Plugin
 *
 * @package Editors
 * @since 1.6
 */
class plgEditorCodemirror extends JPlugin
{
	/**
	 * Base path for editor files
	 */
	protected $_basePath = 'plugins/editors/codemirror/';
	/**
	 * Method to handle the onInitEditor event.
	 *  - Initializes the Editor
	 *
	 * @access public
	 * @return string JavaScript Initialization string
	 */
	public function onInit()
	{
		JHtml::_('core');
		JHtml::_('script', 'codemirror.js', $this->_basePath);
		JHtml::_('stylesheet', 'codemirror.css', $this->_basePath.'css/');

		return '';
	}

	/**
	 * Copy editor content to form field
	 *
	 * @param string 	The name of the editor
	 */
	public function onSave($editor) {
		return "document.getElementById('$editor').value = Joomla.editors.instances['$editor'].getCode();\n";
	}

	/**
	 * Get the editor content
	 *
	 * @param string 	The name of the editor
	 */
	public function onGetContent($editor) {
		return "Joomla.editors.instances['$editor'].getCode();\n";
	}

	/**
	 * Set the editor content
	 *
	 * @param string 	The name of the editor
	 */
	public function onSetContent($editor, $html) {
		return "Joomla.editors.instances['$editor'].setCode($html);\n";
	}

	public function onGetInsertMethod($name)
	{
		$doc = &JFactory::getDocument();

		$js= "\tfunction jInsertEditorText(text, editor) {
				Joomla.editors.instances[editor].replaceSelection(text);\n
		}";
		$doc->addScriptDeclaration($js);

		return true;
	}

	/**
	 * No WYSIWYG Editor - display the editor
	 *
	 * @param string The name of the editor area
	 * @param string The content of the field
	 * @param string The name of the form field
	 * @param string The width of the editor area
	 * @param string The height of the editor area
	 * @param int The number of columns for the editor area
	 * @param int The number of rows for the editor area
	 */
	public function onDisplay($name, $content, $width, $height, $col, $row, $buttons = true)
	{
		// Only add "px" to width and height if they are not given as a percentage
		if (is_numeric($width)) {
			$width .= 'px';
		}
		if (is_numeric($height)) {
			$height .= 'px';
		}

		$buttons = $this->_displayButtons($name, $buttons);
		
		$options = new stdClass();
		$options->height = $height;
		$options->width = $width;
		$options->path = JURI::root(true).'/'.$this->_basePath;
		$options->parserfile = 'parsexml.js';
		$options->stylesheet = JURI::root(true).'/'.$this->_basePath.'css/xmlcolors.css';
		$options->continuousScanning = 500;
		if ($this->params->get('linenumbers', 0)) {
			$options->lineNumbers = true;
			$options->textWrapping = false;
		}
		if ($this->params->get('tabmode', '') == 'shift') {
			$options->tabMode = 'shift';
		}
		
		$html = array();

		$html[]	= "<textarea name=\"$name\" id=\"$name\" cols=\"$col\" rows=\"$row\">$content</textarea>";
		$html[] = $buttons;
		$html[] = '<script type="text/javascript">';
		$html[] = '(function() {';
		$html[] = 'var editor = CodeMirror.fromTextArea("'.$name.'", '.json_encode($options).');';
		$html[] = 'Joomla.editors.instances[\''.$name.'\'] = editor;';
		$html[] = '})()';
		$html[] = '</script>';

		return implode("\n", $html);
	}

	protected function _displayButtons($name, $buttons)
	{
		// Load modal popup behavior
		JHtml::_('behavior.modal', 'a.modal-button');

		$args['name'] = $name;
		$args['event'] = 'onGetInsertMethod';

		$return = '';
		$results[] = $this->update($args);
		foreach ($results as $result) {
			if (is_string($result) && trim($result)) {
				$return .= $result;
			}
		}

		if (!empty($buttons))
		{
			$results = $this->_subject->getButtons($name, $buttons);

			/*
			 * This will allow plugins to attach buttons or change the behavior on the fly using AJAX
			 */
			$return .= "\n<div id=\"editor-xtd-buttons\">\n";
			foreach ($results as $button)
			{
				/*
				 * Results should be an object
				 */
				if ($button->get('name'))
				{
					$modal		= ($button->get('modal')) ? 'class="modal-button"' : null;
					$href		= ($button->get('link')) ? 'href="'.$button->get('link').'"' : null;
					$onclick	= ($button->get('onclick')) ? 'onclick="'.$button->get('onclick').'"' : null;
					$return .= "<div class=\"button2-left\"><div class=\"".$button->get('name')."\"><a ".$modal." title=\"".$button->get('text')."\" ".$href." ".$onclick." rel=\"".$button->get('options')."\">".$button->get('text')."</a></div></div>\n";
				}
			}
			$return .= "</div>\n";
		}

		return $return;
	}
}