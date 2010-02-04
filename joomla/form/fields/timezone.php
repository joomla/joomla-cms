<?php

/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_BASE') or die;
jimport('joomla.html.html');

// Import joomla field list class
require_once dirname(__FILE__) . DS . 'groupedlist.php';

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldTimezone extends JFormFieldGroupedList
{

	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Timezone';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getGroups()
	{
		if (strlen($this->value) == 0)
		{
			$conf = & JFactory::getConfig();
			$value = $conf->getValue('config.offset');
		}
		$zones = DateTimeZone::listIdentifiers();
		foreach($zones as $zone)
		{

			// 0 => Continent, 1 => City
			$zone = explode('/', $zone);

			// Only use "friendly" continent names
			if ($zone[0] == 'Africa' || $zone[0] == 'America' || $zone[0] == 'Antarctica' || $zone[0] == 'Arctic' || $zone[0] == 'Asia' || $zone[0] == 'Atlantic' || $zone[0] == 'Australia' || $zone[0] == 'Europe' || $zone[0] == 'Indian' || $zone[0] == 'Pacific')
			{
				if (isset($zone[1]) != '')
				{

					// Creates array(DateTimeZone => 'Friendly name')
					$groups[$zone[0]][$zone[0] . '/' . $zone[1]] = str_replace('_', ' ', $zone[1]);
				}
			}
		}

		// Sort the arrays
		ksort($groups);
		foreach($groups as $zone => $location)
		{
			sort($location);
		}

		// Merge any additional options in the XML definition.
		$groups = array_merge(parent::_getGroups(), $groups);
		return $groups;
	}
}

