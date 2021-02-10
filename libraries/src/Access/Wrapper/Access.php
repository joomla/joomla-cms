<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Access\Wrapper;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Access\Access as StaticAccess;
use Joomla\CMS\Access\Rules as AccessRules;

/**
 * Wrapper class for Access
 *
 * @since       3.4
 * @deprecated  4.0  Use `Joomla\CMS\Access\Access` directly
 */
class Access
{
	/**
	 * Helper wrapper method for addUserToGroup
	 *
	 * @return void
	 *
	 * @see     StaticAccess::clearStatics
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Access\Access` directly
	 */
	public function clearStatics()
	{
		return StaticAccess::clearStatics();
	}

	/**
	 * Helper wrapper method for check
	 *
	 * @param   integer  $userId  Id of the user for which to check authorisation.
	 * @param   string   $action  The name of the action to authorise.
	 * @param   mixed    $asset   Integer asset id or the name of the asset as a string.  Defaults to the global asset node.
	 *
	 * @return boolean  True if authorised.
	 *
	 * @see     StaticAccess::check()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Access\Access` directly
	 */
	public function check($userId, $action, $asset = null)
	{
		return StaticAccess::check($userId, $action, $asset);
	}

	/**
	 * Helper wrapper method for checkGroup
	 *
	 * @param   integer  $groupId  The path to the group for which to check authorisation.
	 * @param   string   $action   The name of the action to authorise.
	 * @param   mixed    $asset    Integer asset id or the name of the asset as a string.  Defaults to the global asset node.
	 *
	 * @return  boolean  True if authorised.
	 *
	 * @see     StaticAccess::checkGroup()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Access\Access` directly
	 */
	public function checkGroup($groupId, $action, $asset = null)
	{
		return StaticAccess::checkGroup($groupId, $action, $asset);
	}

	/**
	 * Helper wrapper method for getAssetRules
	 *
	 * @param   mixed    $asset      Integer asset id or the name of the asset as a string.
	 * @param   boolean  $recursive  True to return the rules object with inherited rules.
	 *
	 * @return  AccessRules   AccessRules object for the asset.
	 *
	 * @see     StaticAccess::getAssetRules
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Access\Access` directly
	 */
	public function getAssetRules($asset, $recursive = false)
	{
		return StaticAccess::getAssetRules($asset, $recursive);
	}

	/**
	 * Helper wrapper method for getGroupsByUser
	 *
	 * @param   integer  $userId     Id of the user for which to get the list of groups.
	 * @param   boolean  $recursive  True to include inherited user groups.
	 *
	 * @return  array    List of user group ids to which the user is mapped.
	 *
	 * @see     StaticAccess::getGroupsByUser()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Access\Access` directly
	 */
	public function getGroupsByUser($userId, $recursive = true)
	{
		return StaticAccess::getGroupsByUser($userId, $recursive);
	}

	/**
	 * Helper wrapper method for getUsersByGroup
	 *
	 * @param   integer  $groupId    The group Id
	 * @param   boolean  $recursive  Recursively include all child groups (optional)
	 *
	 * @return  array
	 *
	 * @see     StaticAccess::getUsersByGroup()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Access\Access` directly
	 */
	public function getUsersByGroup($groupId, $recursive = false)
	{
		return StaticAccess::getUsersByGroup($groupId, $recursive);
	}

	/**
	 * Helper wrapper method for getAuthorisedViewLevels
	 *
	 * @param   integer  $userId  Id of the user for which to get the list of authorised view levels.
	 *
	 * @return  array    List of view levels for which the user is authorised.
	 *
	 * @see     StaticAccess::getAuthorisedViewLevels()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Access\Access` directly
	 */
	public function getAuthorisedViewLevels($userId)
	{
		return StaticAccess::getAuthorisedViewLevels($userId);
	}

	/**
	 * Helper wrapper method for getActions
	 *
	 * @param   string  $component  The component from which to retrieve the actions.
	 * @param   string  $section    The name of the section within the component from which to retrieve the actions.
	 *
	 * @return array  List of actions available for the given component and section.
	 *
	 * @see     StaticAccess::getActions()
	 * @since   3.4
	 * @deprecated  4.0  Use StaticAccess::getActionsFromFile or StaticAccess::getActionsFromData instead.
	 */
	public function getActions($component, $section = 'component')
	{
		return StaticAccess::getActions($component, $section);
	}

	/**
	 * Helper wrapper method for getActionsFromFile
	 *
	 * @param   string  $file   The path to the XML file.
	 * @param   string  $xpath  An optional xpath to search for the fields.
	 *
	 * @return  boolean|array   False if case of error or the list of actions available.
	 *
	 * @see     StaticAccess::getActionsFromFile()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Access\Access` directly
	 */
	public function getActionsFromFile($file, $xpath = '/access/section[@name=\'component\']/')
	{
		return StaticAccess::getActionsFromFile($file, $xpath);
	}

	/**
	 * Helper wrapper method for getActionsFromData
	 *
	 * @param   string|\SimpleXMLElement  $data   The XML string or an XML element.
	 * @param   string                    $xpath  An optional xpath to search for the fields.
	 *
	 * @return  boolean|array   False if case of error or the list of actions available.
	 *
	 * @see     StaticAccess::getActionsFromData()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Access\Access` directly
	 */
	public function getActionsFromData($data, $xpath = '/access/section[@name=\'component\']/')
	{
		return StaticAccess::getActionsFromData($data, $xpath);
	}
}
