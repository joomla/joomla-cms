<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * An editarea field for content creation
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 * @see         JEditor
 * @since       1.6
 */
class JFormFieldEditor extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'Editor';

	/**
	 * The JEditor object.
	 *
	 * @var    JEditor
	 * @since  1.6
	 */
	protected $editor;

	/**
	 * Method to get the field input markup for the editor area
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$rows        = (int) $this->element['rows'];
		$cols        = (int) $this->element['cols'];
		$height      = ((string) $this->element['height']) ? (string) $this->element['height'] : '250';
		$width       = ((string) $this->element['width']) ? (string) $this->element['width'] : '100%';
		$assetField  = $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';
		$authorField = $this->element['created_by_field'] ? (string) $this->element['created_by_field'] : 'created_by';
		$asset       = $this->form->getValue($assetField) ? $this->form->getValue($assetField) : (string) $this->element['asset_id'];

		// Build the buttons array.
		$buttons = (string) $this->element['buttons'];

		if ($buttons == 'true' || $buttons == 'yes' || $buttons == '1')
		{
			$buttons = true;
		}
		elseif ($buttons == 'false' || $buttons == 'no' || $buttons == '0')
		{
			$buttons = false;
		}
		else
		{
			$buttons = explode(',', $buttons);
		}

		$hide = ((string) $this->element['hide']) ? explode(',', (string) $this->element['hide']) : array();

		// Get an editor object.
		$editor = $this->getEditor();

		return $editor
			->display(
			$this->name, htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8'), $width, $height, $cols, $rows,
			$buttons ? (is_array($buttons) ? array_merge($buttons, $hide) : $hide) : false, $this->id, $asset,
			$this->form->getValue($authorField)
		);
	}

	/**
	 * Method to get a JEditor object based on the form field.
	 *
	 * @return  JEditor  The JEditor object.
	 *
	 * @since   1.6
	 */
	protected function getEditor()
	{
		// Only create the editor if it is not already created.
		if (empty($this->editor))
		{
			$editor = null;

			// Get the editor type attribute. Can be in the form of: editor="desired|alternative".
			$type = trim((string) $this->element['editor']);

			if ($type)
			{
				// Get the list of editor types.
				$types = explode('|', $type);

				// Get the database object.
				$db = JFactory::getDbo();

				// Iterate over teh types looking for an existing editor.
				foreach ($types as $element)
				{
					// Build the query.
					$query = $db->getQuery(true)
						->select('element')
						->from('#__extensions')
						->where('element = ' . $db->quote($element))
						->where('folder = ' . $db->quote('editors'))
						->where('enabled = 1');

					// Check of the editor exists.
					$db->setQuery($query, 0, 1);
					$editor = $db->loadResult();

					// If an editor was found stop looking.
					if ($editor)
					{
						break;
					}
				}
			}

			// Create the JEditor instance based on the given editor.
			if (is_null($editor))
			{
				$conf = JFactory::getConfig();
				$editor = $conf->get('editor');
			}
			$this->editor = JEditor::getInstance($editor);
		}

		return $this->editor;
	}

	/**
	 * Method to get the JEditor output for an onSave event.
	 *
	 * @return  string  The JEditor object output.
	 *
	 * @since   1.6
	 */
	public function save()
	{
		return $this->getEditor()->save($this->id);
	}
}
