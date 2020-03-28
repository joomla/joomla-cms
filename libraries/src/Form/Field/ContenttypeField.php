<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Content Type field.
 *
 * @since  3.1
 */
class ContenttypeField extends ListField
{
	/**
	 * A flexible tag list that respects access controls
	 *
	 * @var    string
	 * @since  3.1
	 */
	public $type = 'Contenttype';

	/**
	 * If true the uses the type_alias instead of the content type primary key as the field value.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $useAliasAsValue = false;

	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     FormField::setup()
	 * @since   3.2
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->useAliasAsValue = (boolean) $this->element['useAliasAsValue'];
		}

		return $return;
	}

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to get the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'useAliasAsValue':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to set the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'useAliasAsValue':
				$this->$name = (boolean) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to get the field input for a list of content types.
	 *
	 * @return  string  The field input.
	 *
	 * @since   3.1
	 */
	protected function getInput()
	{
		if (!\is_array($this->value))
		{
			if (\is_object($this->value))
			{
				$this->value = $this->value->tags;
			}

			if (\is_string($this->value))
			{
				$this->value = explode(',', $this->value);
			}
		}

		return parent::getInput();
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
		$lang = Factory::getLanguage();
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(
				[
					$db->quoteName($this->useAliasAsValue ? 'a.type_alias' : 'a.type_id', 'value'),
					$db->quoteName('a.type_title', 'text'),
					$db->quoteName('a.type_alias', 'alias'),
				]
			)
			->from($db->quoteName('#__content_types', 'a'))
			->order($db->quoteName('a.type_title') . ' ASC');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			return array();
		}

		foreach ($options as $option)
		{
			// Make up the string from the component sys.ini file
			$parts = explode('.', $option->alias);
			$comp = array_shift($parts);

			// Make sure the component sys.ini is loaded
			$lang->load($comp . '.sys', JPATH_ADMINISTRATOR)
			|| $lang->load($comp . '.sys', JPATH_ADMINISTRATOR . '/components/' . $comp);

			$option->string = implode('_', $parts);
			$option->string = $comp . '_CONTENT_TYPE_' . $option->string;

			if ($lang->hasKey($option->string))
			{
				$option->text = Text::_($option->string);
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
