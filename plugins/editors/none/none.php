<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.none
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Event\Event;

/**
 * Plain Textarea Editor Plugin
 *
 * @since  1.5
 */
class PlgEditorNone extends JPlugin
{
	/**
	 * Method to handle the onInitEditor event.
	 *  - Initialises the Editor
	 *
	 * @return  void
	 *
	 * @since 1.5
	 */
	public function onInit()
	{
		JHtml::_('script', 'media/editors/none/none.min.js', array('version' => 'auto'));

		return null;
	}

	/**
	 * Copy editor content to form field.
	 *
	 * Not applicable in this editor.
	 *
	 * @return  void
	 */
	public function onSave()
	{
		return;
	}

	/**
	 * Get the editor content.
	 *
	 * @param   string  $id  The id of the editor field.
	 *
	 * @return  string
	 */
	public function onGetContent($id)
	{
		return "document.getElementById('$id').value;\n";
	}

	/**
	 * Set the editor content.
	 *
	 * @param   string  $id    The id of the editor field.
	 * @param   string  $html  The content to set.
	 *
	 * @return  string
	 */
	public function onSetContent($id, $html)
	{
		return "document.getElementById('$id').value = $html;\n";
	}

	/**
	 * Inserts html code into the editor
	 *
	 * @param   string  $id  The id of the editor field
	 *
	 * @return  void
	 */
	public function onGetInsertMethod($id)
	{
		return null;
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
	 * @param   string   $asset    The object asset
	 * @param   object   $author   The author.
	 * @param   array    $params   Associative array of editor parameters.
	 *
	 * @return  string
	 */
	public function onDisplay($name, $content, $width, $height, $col, $row, $buttons = true,
		$id = null, $asset = null, $author = null, $params = array())
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

		$editor = '<textarea name="' . $name . '" id="' . $id . '" cols="' . $col . '" rows="' . $row
				. '" style="width: ' . $width . '; height: ' . $height . ';">' . $content . '</textarea>'
				. $this->_displayButtons($id, $buttons, $asset, $author);

		return $editor;
	}

	/**
	 * Displays the editor buttons.
	 *
	 * @param   string  $name     The control name.
	 * @param   mixed   $buttons  [array with button objects | boolean true to display buttons]
	 * @param   string  $asset    The object asset
	 * @param   object  $author   The author.
	 *
	 * @return  string HTML
	 */
	public function _displayButtons($name, $buttons, $asset, $author)
	{
		$return = '';

		$onGetInsertMethodEvent = new Event(
			'onGetInsertMethod',
			['name' => $name]
		);

		$rawResults = $this->getDispatcher()->dispatch('onGetInsertMethod', $onGetInsertMethodEvent);
		$results    = $rawResults['result'];

		if (is_array($results) && !empty($results))
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
			$buttonsEvent = new Event(
				'getButtons',
				[
					'name'    => $this->_name,
					'buttons' => $buttons,
				]
			);

			$buttonsResult = $this->getDispatcher()->dispatch('getButtons', $buttonsEvent);
			$buttons       = $buttonsResult['result'];

			$return .= JLayoutHelper::render('joomla.editors.buttons', $buttons);
		}

		return $return;
	}
}
