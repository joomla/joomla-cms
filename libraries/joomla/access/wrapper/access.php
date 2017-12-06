<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Access
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Wrapper class for JAccess
 *
 * @package     Joomla.Platform
 * @subpackage  User
 * @since       3.4
 */
class JAccessWrapperAccess
{
	/**
	 * Helper wrapper method for addUserToGroup
	 *
	 * @return void
	 *
	 * @see     JAccess::clearStatics
	 * @since   3.4
	 */
	public function clearStatics()
	{
		return JAccess::clearStatics();
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
	 * @see     JAccess::check()
	 * @since   3.4
	 */
	public function check($userId, $action, $asset = null)
	{
		return JAccess::check($userId, $action, $asset);
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
	 * @see     JAccess::checkGroup()
	 * @since   3.4
	 */
	public function checkGroup($groupId, $action, $asset = null)
	{
		return JAccess::checkGroup($groupId, $action, $asset);
	}

	/**
	 * Helper wrapper method for getAssetRules
	 *
	 * @param   mixed    $asset      Integer asset id or the name of the asset as a string.
	 * @param   boolean  $recursive  True to return the rules object with inherited rules.
	 *
	 * @return  JAccessRules   JAccessRules object for the asset.
	 *
	 * @see     JAccess::getAssetRules
	 * @since   3.4
	 */
	public function getAssetRules($asset, $recursive = false)
	{
		return JAccess::getAssetRules($asset, $recursive);
	}

	/**
	 * Helper wrapper method for getGroupsByUser
	 *
	 * @param   integer  $userId     Id of the user for which to get the list of groups.
	 * @param   boolean  $recursive  True to include inherited user groups.
	 *
	 * @return  array    List of user group ids to which the user is mapped.
	 *
	 * @see     JAccess::getGroupsByUser()
	 * @since   3.4
	 */
	public function getGroupsByUser($userId, $recursive = true)
	{
		return JAccess::getGroupsByUser($userId, $recursive);
	}

	/**
	 * Helper wrapper method for getUsersByGroup
	 *
	 * @param   integer  $groupId    The group Id
	 * @param   boolean  $recursive  Recursively include all child groups (optional)
	 *
	 * @return  array
	 *
	 * @see     JAccess::getUsersByGroup()
	 * @since   3.4
	 */
	public function getUsersByGroup($groupId, $recursive = false)
	{
		return JAccess::getUsersByGroup($groupId, $recursive);
	}

	/**
	 * Helper wrapper method for getAuthorisedViewLevels
	 *
	 * @param   integer  $userId  Id of the user for which to get the list of authorised view levels.
	 *
	 * @return  array    List of view levels for which the user is authorised.
	 *
	 * @see     JAccess::getAuthorisedViewLevels()
	 * @since   3.4
	 */
	public function getAuthorisedViewLevels($userId)
	{
		return JAccess::getAuthorisedViewLevels($userId);
	}

	/**
	 * Helper wrapper method for getActions
	 *
	 * @param   string  $component  The component from which to retrieve the actions.
	 * @param   string  $section    The name of the section within the component from which to retrieve the actions.
	 *
	 * @return array  List of actions available for the given component and section.
	 *
	 * @see     JAccess::getActions()
	 * @since   3.4
	 */
	public function getActions($component, $section = 'component')
	{
		return JAccess::getActions($component, $section);
	}

	/**
	 * Helper wrapper method for getActionsFromFile
	 *
	 * @param   string  $file   The path to the XML file.
	 * @param   string  $xpath  An optional xpath to search for the fields.
	 *
	 * @return  boolean|array   False if case of error or the list of actions available.
	 *
	 * @see     JAccess::getActionsFromFile()
	 * @since   3.4
	 */
	public function getActionsFromFile($file, $xpath = '/access/section[@name=\'component\']/')
	{
		return JAccess::getActionsFromFile($file, $xpath);
	}

	/**
	 * Helper wrapper method for getActionsFromData
	 *
	 * @param   string|SimpleXMLElement  $data   The XML string or an XML element.
	 * @param   string                   $xpath  An optional xpath to search for the fields.
	 *
	 * @return  boolean|array   False if case of error or the list of actions available.
	 *
	 * @see     JAccess::getActionsFromData()
	 * @since   3.4
	 */
	public function getActionsFromData($data, $xpath = '/access/section[@name=\'component\']/')
	{
		return JAccess::getActionsFromData($data, $xpath);
	}
}
