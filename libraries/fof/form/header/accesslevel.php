<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * Access level field header
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFFormHeaderAccesslevel extends FOFFormHeaderFieldselectable
{
	/**
	 * Method to get the list of access levels
	 *
	 * @return  array	A list of access levels.
	 *
	 * @since   2.0
	 */
	protected function getOptions()
	{
		$db    = FOFPlatform::getInstance()->getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id AS value, a.title AS text');
		$query->from('#__viewlevels AS a');
		$query->group('a.id, a.title, a.ordering');
		$query->order('a.ordering ASC');
		$query->order($query->qn('title') . ' ASC');

		// Get the options.
		$db->setQuery($query);
		$options = $db->loadObjectList();

		return $options;
	}
}
