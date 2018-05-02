<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.form.helper');

JFormHelper::loadFieldClass('folderlist');

/**
 * Generates the list of directories  available for drag and drop upload.
 *
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 * @since       3.7.0
 */
class JFormFieldUploaddirs extends JFormFieldFolderList
{
	protected $type = 'uploaddirs';

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.7.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		// Get the path in which to search for file options.
		$this->directory   = JComponentHelper::getParams('com_media')->get('image_path');
		$this->recursive   = true;
		$this->hideDefault = true;

		return $return;
	}

	/**
	 * Method to get the directories options.
	 *
	 * @return  array  The dirs option objects.
	 *
	 * @since   3.7.0
	 */
	public function getOptions()
	{
		return parent::getOptions();
	}

	/**
	 * Method to get the field input markup for the list of directories.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.7.0
	 */
	protected function getInput()
	{
		$html = array();

		// Get the field options.
		$options = (array) $this->getOptions();

		// Reset the non selected value to null
		if ($options[0]->value === '-1')
		{
			$options[0]->value = '';
		}

		// Create a regular list.
		$html[] = JHtml::_('select.genericlist', $options, $this->name, '', 'value', 'text', $this->value, $this->id);

		return implode($html);
	}
}
