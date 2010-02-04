<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * CodeMirror Editor Plugin.
 *
 * @package		Joomla
 * @subpackage	Editors
 * @since		1.6
 */
class plgEditorCodemirror extends JPlugin
{
	/**
	 * Base path for editor files
	 */
	protected $_basePath = 'plugins/editors/codemirror/codemirror/';

	/**
	 * Initialises the Editor.
	 *
	 * @return	string	Returns nothing.
	 */
	public function onInit()
	{
		JHtml::_('core');
		JHtml::_('script', 'codemirror.js', $this->_basePath);
		JHtml::_('stylesheet', 'codemirror.css', $this->_basePath.'css/');

		return '';
	}

	/**
	 * Copy editor content to form field.
	 *
	 * @param	string	The id of the editor field.
	 */
	public function onSave($id)
	{
		return "document.getElementById('$id').value = Joomla.editors.instances['$id'].getCode();\n";
	}

	/**
	 * Get the editor content.
	 *
	 * @param	string	The id of the editor field.
	 */
	public function onGetContent($id)
	{
		return "Joomla.editors.instances['$id'].getCode();\n";
	}

	/**
	 * Set the editor content.
	 *
	 * @param	string	The id of the editor field.
	 * @param	string	The content to set.
	 */
	public function onSetContent($id, $content)
	{
		return "Joomla.editors.instances['$id'].setCode($content);\n";
	}

	/**
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
	 * @param	string	The name of the editor area.
	 * @param	string	The content of the field.
	 * @param	string	The width of the editor area.
	 * @param	string	The height of the editor area.
	 * @param	int		The number of columns for the editor area.
	 * @param	int		The number of rows for the editor area.
	 * @param	boolean	True and the editor buttons will be displayed.
	 * @param	string	An optional ID for the textarea (note: since 1.6). If not supplied the name is used.
	 */
	public function onDisplay($name, $content, $width, $height, $col, $row, $buttons = true, $id = null)
	{
		if (empty($id)) {
			$id = $name;
		}

		// Only add "px" to width and height if they are not given as a percentage
		if (is_numeric($width)) {
			$width .= 'px';
		}
		if (is_numeric($height)) {
			$height .= 'px';
		}

		// Must pass the field id to the buttons in this editor.
		$buttons	= $this->_displayButtons($id, $buttons);

		$options	= new stdClass;
		$compressed	= JFactory::getApplication()->getCfg('debug') ? '-uncompressed' : '';

		$options->basefiles		= array('basefiles'.$compressed.'.js');
		$options->path			= JURI::root(true).'/'.$this->_basePath;
		$options->parserfile	= 'parsexml.js';
		$options->stylesheet	= JURI::root(true).'/'.$this->_basePath.'css/xmlcolors.css';
		$options->height		= $height;
		$options->width			= $width;
		$options->continuousScanning = 500;
		if ($this->params->get('linenumbers', 0))
		{
			$options->lineNumbers	= true;
			$options->textWrapping	= false;
		}
		if ($this->params->get('tabmode', '') == 'shift') {
			$options->tabMode = 'shift';
		}

		$html = array();
		$html[]	= "<textarea name=\"$name\" id=\"$id\" cols=\"$col\" rows=\"$row\">$content</textarea>";
		$html[] = $buttons;
		$html[] = '<script type="text/javascript">';
		$html[] = '(function() {';
		$html[] = 'var editor = CodeMirror.fromTextArea("'.$id.'", '.json_encode($options).');';
		$html[] = 'Joomla.editors.instances[\''.$id.'\'] = editor;';
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
		foreach ($results as $result)
		{
			if (is_string($result) && trim($result)) {
				$return .= $result;
			}
		}

		if (!empty($buttons))
		{
			$results = $this->_subject->getButtons($name, $buttons);

			// This will allow plugins to attach buttons or change the behavior on the fly using AJAX
			$return .= "\n<div id=\"editor-xtd-buttons\">\n";
			foreach ($results as $button)
			{
				// Results should be an object
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