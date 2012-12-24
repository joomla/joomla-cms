<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Tags helper.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @since       3.1
 */
class TagsHelper
{

	/**
	 * Configure the Submenu links.
	 *
	 * @param	string	The extension.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public static function addSubmenu($extension)
	{

		$parts = explode('.', $extension);
		$component = $parts[0];

		if (count($parts) > 1) {
			$section = $parts[1];
		}

		// Try to find the component helper.
		$file	= JPath::clean(JPATH_ADMINISTRATOR.'/components/com_tags/helpers/tags.php');

		if (file_exists($file)) {
			require_once $file;

			$cName	= 'TagsHelper';

			if (class_exists($cName)) {

				if (is_callable(array($cName, 'addSubmenu'))) {
					$lang = JFactory::getLanguage();
					// loading language file from the administrator/language directory then
					// loading language file from the administrator/components/*extension*/language directory
						$lang->load($component, JPATH_BASE, null, false, false)
					||	$lang->load($component, JPath::clean(JPATH_ADMINISTRATOR.'/components/'.$component), null, false, false)
					||	$lang->load($component, JPATH_BASE, $lang->getDefault(), false, false)
					||	$lang->load($component, JPath::clean(JPATH_ADMINISTRATOR.'/components/'.$component), $lang->getDefault(), false, false);

				}
			}
		}
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param   integer  $tagId  The tag ID.
	 *
	 * @return  JObject
	 *
	 * @since   3.1
	 */
	public static function getActions($tagId = 0)
	{
		$user		= JFactory::getUser();
		$result		= new JObject;

			$assetName = 'com_tags';
			$level = 'component';
			$actions = JAccess::getActions('com_tags', $level);

		foreach ($actions as $action) {
			$result->set($action->name, $user->authorise($action->name, $assetName));
		}

		return $result;
	}

	/**
	 * Method to add or update tags associated with an item. Generally used as a postSaveHook.
	 *
	 * @param   integer  $id      The id (primary key) of the item to be tagged.
	 * @param   string   $prefix  Dot separated string with the option and view for a url.
	 * @params  array    $tags    Array of tags to be applied.
	 *
	 * @return  void
	 * @since   3.1
	 */
	 public static function tagItem($id, $prefix, $tags)
	 {
		// Delete the old tag maps.
		$db		= JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->delete();
		$query->from($db->quoteName('#__contentitem_tag_map'));
		$query->where($db->quoteName('item_name') . ' = ' .  $db->quote($prefix . '.' . (int) $id));
		$db->setQuery($query);
		$db->execute();

		// Set the new tag maps.
		// Have to break this up into individual queries for cross-database support.
		foreach ($tags as $tag)
		{
			$query2 = $db->getQuery(true);

			$query2->insert($db->quoteName('#__contentitem_tag_map'));
			$query2->columns(array($db->quoteName('item_name'), $db->quoteName('tag_id')));

			$query2->clear('values');
			$query2->values($db->quote($prefix . '.' . $id) . ', ' . $tag);
			$db->setQuery($query2);
			$db->execute();
		}

		return;
	}

	/**
	 * Method to get a lit of tags for a given item.
	 *
	 * @param   integer  $id      The id (primary key) of the item to be tagged.
	 * @param   string   $prefix  Dot separated string with the option and view for a url.
	 * 
	 * @return  string    Comma separated list of tag Ids.
	 *
	 * @return  void
	 * @since   3.1
	 */

	public static function getTagIds($id, $prefix)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Load the tags.
		$query->clear();
		$query->select($db->quoteName('t.id') );

		$query->from($db->quoteName('#__tags') . ' AS t');
		$query->join('INNER', $db->quoteName('#__contentitem_tag_map') . ' AS m ' .
			' ON ' . $db->quoteName('m.tag_id') . ' = ' .  $db->quoteName('t.id'));
		$query->where($db->quoteName('m.item_name') . ' = ' . $db->quote($prefix . '.' . $id));
		$db->setQuery($query);

		// Add the tags to the content data.
		$tagsList = $db->loadColumn();
		$tags = implode(',', $tagsList);

		return $tags;
	}

}

