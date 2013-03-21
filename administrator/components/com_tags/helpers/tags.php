<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Tags helper.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 * @since       3.1
 */
class TagsHelper
{
	/**
	 * Configure the Submenu links.
	 *
	 * @param   string  The extension.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public static function addSubmenu($extension)
	{
		$parts = explode('.', $extension);
		$component = $parts[0];

		if (count($parts) > 1)
		{
			$section = $parts[1];
		}

		// Try to find the component helper.
		$file = JPath::clean(JPATH_ADMINISTRATOR . '/components/com_tags/helpers/tags.php');

		if (file_exists($file))
		{
			require_once $file;

			$cName = 'TagsHelper';

			if (class_exists($cName))
			{
				if (is_callable(array($cName, 'addSubmenu')))
				{
					$lang = JFactory::getLanguage();
					// loading language file from the administrator/language directory then
					// loading language file from the administrator/components/*extension*/language directory
						$lang->load($component, JPATH_BASE, null, false, false)
					||	$lang->load($component, JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component), null, false, false)
					||	$lang->load($component, JPATH_BASE, $lang->getDefault(), false, false)
					||	$lang->load($component, JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component), $lang->getDefault(), false, false);

				}
			}
		}
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return  JObject
	 *
	 * @since   3.1
	 */
	public static function getActions()
	{
		$user   = JFactory::getUser();
		$result = new JObject;

		$assetName = 'com_tags';
		$level     = 'component';
		$actions   = JAccess::getActions('com_tags', $level);

		foreach ($actions as $action)
		{
			$result->set($action->name, $user->authorise($action->name, $assetName));
		}

		return $result;
	}

	public static function getAssociations($pk)
	{
		$associations = array();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->from('#__tags as c')
			->innerJoin('#__associations as a ON a.id = c.id AND a.context=' . $db->q('com_tags.item'))
			->innerJoin('#__associations as a2 ON a.key = a2.key')
			->innerJoin('#__tags as c2 ON a2.id = c2.id')
			->where('c.id =' . (int) $pk);
		$select = array(
				'c2.language',
				$query->concatenate(array('c2.id', 'c2.alias'), ':') . ' AS id',
		);
		$query->select($select);
		$db->setQuery($query);
		$contactitems = $db->loadObjectList('language');

		// Check for a database error.
		if ($error = $db->getErrorMsg())
		{
			JError::raiseWarning(500, $error);
			return false;
		}

		foreach ($contactitems as $tag => $item)
		{
			$associations[$tag] = $item;
		}

		return $associations;
	}
}
