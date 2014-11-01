<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

/**
 * Contenttypetag Field class for the Joomla Framework.
 *
 * @since  3.1
 */
class JFormFieldContenttypeTag extends JFormFieldList
{
	/**
	 * A flexible tag list that respects access controls
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $type = 'Contenttypetag';

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
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true)
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
			$comp = $parts[0];

			// Cope with content_types which are not used by tags
			if ($comp != 'com_banners' && $comp != 'com_users' && $comp != 'com_tags')
			{
				// Make sure the component sys.ini is loaded
				$lang->load($comp . '.sys', JPATH_ADMINISTRATOR, null, false, true)
				|| $lang->load($comp . '.sys', JPATH_ADMINISTRATOR . '/components/' . $comp, null, false, true);

				$option->string = mb_strtoupper(str_replace(' ', '_', $option->text), 'UTF-8');
				$option->string = $comp . '_CONTENT_TYPE_' . $option->string;

				if ($lang->hasKey($option->string))
				{
					$option->text = JText::_($option->string);
				}
				else
				{
					$option->text = $option->text;
				}
			}
			else
			{
				$option->text = "";
			}
		}

		return $options;
	}
}
