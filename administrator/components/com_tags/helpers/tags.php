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
}
