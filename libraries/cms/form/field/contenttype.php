<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @since  3.1
 */
class JFormFieldContenttype extends JFormFieldList
{
	/**
	 * A flexible tag list that respects access controls
	 *
	 * @var    string
	 * @since  3.1
	 */
	public $type = 'Contenttype';

	/**
	 * Method to get the field input for a list of content types.
	 *
	 * @return  string  The field input.
	 *
	 * @since   3.1
	 */
	protected function getInput()
	{
		if (!is_array($this->value))
		{
			if (is_object($this->value))
			{
				$this->value = $this->value->tags;
			}

			if (is_string($this->value))
			{
				$this->value = explode(',', $this->value);
			}
		}

		$input = parent::getInput();

		return $input;
	}

	/**
	 * Method to get a list of content types
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.1
	 */
	protected function getOptions()
	{
		$lang = JFactory::getLanguage();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.type_id AS value, a.type_title AS text, a.type_alias AS alias')
			->from('#__content_types AS a')

			->order('a.type_title ASC');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		foreach ($options as $option)
		{
			// Make up the string from the component sys.ini file
			$parts = explode('.', $option->alias);
			$comp = array_shift($parts);

			// Make sure the component sys.ini is loaded
			$lang->load($comp . '.sys', JPATH_ADMINISTRATOR, null, false, true)
			|| $lang->load($comp . '.sys', JPATH_ADMINISTRATOR . '/components/' . $comp, null, false, true);

			$option->string = implode('_', $parts);
			$option->string = $comp . '_CONTENT_TYPE_' . $option->string;

			if ($lang->hasKey($option->string))
			{
				$option->text = JText::_($option->string);
			}

		}

		return $options;
	}
}
