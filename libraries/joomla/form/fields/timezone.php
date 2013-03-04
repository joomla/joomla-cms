<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('groupedlist');

/**
 * Form Field class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldTimezone extends JFormFieldGroupedList
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Timezone';

	/**
	 * The list of available timezone groups to use.
	 *
	 * @var    array
	 *
	 * @since  11.1
	 */
	protected static $zones = array('Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific');

	/**
	 * Method to get the time zone field option groups.
	 *
	 * @return  array  The field option objects as a nested array in groups.
	 *
	 * @since   11.1
	 */
	protected function getGroups()
	{
		// Initialize variables.
		$groups = array();

		$keyField = $this->element['key_field'] ? (string) $this->element['key_field'] : 'id';
		$keyValue = $this->form->getValue($keyField);

		// If the timezone is not set use the server setting.
		if (strlen($this->value) == 0 && empty($keyValue))
		{
			$this->value = JFactory::getConfig()->get('offset');
		}

		// Get the list of time zones from the server.
		$zones = DateTimeZone::listIdentifiers();

		// Build the group lists.
		foreach ($zones as $zone)
		{

			// Time zones not in a group we will ignore.
			if (strpos($zone, '/') === false)
			{
				continue;
			}

			// Get the group/locale from the timezone.
			list ($group, $locale) = explode('/', $zone, 2);

			// Only use known groups.
			if (in_array($group, self::$zones))
			{

				// Initialize the group if necessary.
				if (!isset($groups[$group]))
				{
					$groups[$group] = array();
				}

				// Only add options where a locale exists.
				if (!empty($locale))
				{
					$groups[$group][$zone] = JHtml::_('select.option', $zone, str_replace('_', ' ', $locale), 'value', 'text', false);
				}
			}
		}

		// Sort the group lists.
		ksort($groups);
		foreach ($groups as $zone => & $location)
		{
			sort($location);
		}

		// Merge any additional groups in the XML definition.
		$groups = array_merge(parent::getGroups(), $groups);

		return $groups;
	}
}
