<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @since  2.5.0
 */
class JFormFieldPlugins extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var    string
	 * @since  2.5.0
	 */
	protected $type = 'Plugins';

	/**
	 * The path to folder for plugins.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $folder;

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
			case 'folder':
				return $this->folder;
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
			case 'folder':
				$this->folder = (string) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

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
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->folder = (string) $this->element['folder'];
		}

		return $return;
	}

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array  An array of JHtml options.
	 *
	 * @since   2.5.0
	 */
	protected function getOptions()
	{
		$folder        = $this->folder;
		$parentOptions = parent::getOptions();

		if (!empty($folder))
		{
			// Get list of plugins
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('element AS value, name AS text')
				->from('#__extensions')
				->where('folder = ' . $db->quote($folder))
				->where('enabled = 1')
				->order('ordering, name');

			if ((string) $this->element['useaccess'] === 'true')
			{
				$groups = implode(',', JFactory::getUser()->getAuthorisedViewLevels());
				$query->where($db->quoteName('access') . ' IN (' . $groups . ')');
			}

			$options   = $db->setQuery($query)->loadObjectList();
			$lang      = JFactory::getLanguage();
			$useGlobal = $this->element['useglobal'];

			if ($useGlobal)
			{
				$globalValue = JFactory::getConfig()->get($this->fieldname);
			}

			foreach ($options as $i => $item)
			{
				$source    = JPATH_PLUGINS . '/' . $folder . '/' . $item->value;
				$extension = 'plg_' . $folder . '_' . $item->value;
				$lang->load($extension . '.sys', JPATH_ADMINISTRATOR, null, false, true) || $lang->load($extension . '.sys', $source, null, false, true);
				$options[$i]->text = JText::_($item->text);

				// If we are using useglobal update the use global value text with the plugin text.
				if ($useGlobal && isset($parentOptions[0]) && $item->value === $globalValue)
				{
					$text                   = JText::_($extension);
					$parentOptions[0]->text = JText::sprintf('JGLOBAL_USE_GLOBAL_VALUE', ($text === '' || $text === $extension ? $item->value : $text));
				}
			}
		}
		else
		{
			JLog::add(JText::_('JFRAMEWORK_FORM_FIELDS_PLUGINS_ERROR_FOLDER_EMPTY'), JLog::WARNING, 'jerror');
		}

		return array_merge($parentOptions, $options);
	}
}
