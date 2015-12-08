<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Media Component Files Model
 */
class MediaModelFiles extends JModelLegacy
{
	public function getFiles($folder)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('id', 'filename', 'path')));
		$query->from($db->quoteName('#__media_files'));
		$query->where($db->quoteName('path') . ' = '. $db->quote($folder));
		$query->order('ordering ASC');

		$db->setQuery($query);

		$results = $db->loadObjectList();

		return $results;
	}
}