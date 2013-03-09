<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The Tags List Controller
 *
 * @package     Joomla.Site
 * @subpackage  com_tags
 * @since       3.1
 */
class TagsControllerTags extends JControllerLegacy
{

	/**
	 * Method to search tags with AJAX
	 *
	 * @return  void
	 */
	public function searchAjax()
	{
		// Required objects
		$app = JFactory::getApplication();
		$db  = JFactory::getDbo();

		// Receive request data
		$like     = trim($app->input->get('like', null));
		$title     = trim($app->input->get('title', null));
		$language  = $app->input->get('language', null);
		$published = $app->input->get('published', 1, 'integer');

		$query	= $db->getQuery(true)
			->select('a.id AS value, a.title AS text')
			->from('#__tags AS a')
			->join('LEFT', $db->quoteName('#__tags') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt');

		// Filter language
		if (!empty($language))
		{
			$query->where('a.language = ' . $db->q($language));
		}

		$query->where($db->quoteName('a.alias') . ' <> ' . $db->quote('root'));

		// Search in title
		if (!empty($like))
		{
			$query->where($db->quoteName('a.title') . ' LIKE ' . $db->quote('%' . $like . '%'));
		}

		// Filter title
		if (!empty($title))
		{
			$query->where($db->quoteName('a.title') . '=' . $db->quote($title));
		}

		// Filter on the published state
		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}

		$query->group('a.id, a.title, a.level, a.lft, a.rgt, a.parent_id, a.published');
		$query->order('a.lft ASC');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$result = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		// We will output a JSON object
		echo json_encode($result);

		$app->close();
	}

}
