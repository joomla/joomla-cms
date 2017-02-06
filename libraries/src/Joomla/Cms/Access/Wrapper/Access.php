<?php
/**
 * Joomla! Content Management System
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Access\Wrapper;

defined('JPATH_PLATFORM') or die;

use Joomla\Cms\Access\Access;

/**
 * Wrapper class for Access
 *
 * @package     Joomla.Platform
 * @subpackage  User
 * @since       3.4
 */
class Access
{
	/**
	 * Helper wrapper method for addUserToGroup
	 *
	 * @return void
	 *
	 * @see     Access::clearStatics
	 * @since   3.4
	 */
	public function clearStatics()
	{
		return Access::clearStatics();
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
	 * @see     Access::check()
	 * @since   3.4
	 */
	public function check($userId, $action, $asset = null)
	{
		return Access::check($userId, $action, $asset);
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
	 * @see     Access::checkGroup()
	 * @since   3.4
	 */
	public function checkGroup($groupId, $action, $asset = null)
	{
		return Access::checkGroup($groupId, $action, $asset);
	}

	/**
	 * Helper wrapper method for getAssetRules
	 *
	 * @param   mixed    $asset      Integer asset id or the name of the asset as a string.
	 * @param   boolean  $recursive  True to return the rules object with inherited rules.
	 *
	 * @return  AccessRules   AccessRules object for the asset.
	 *
	 * @see     Access::getAssetRules
	 * @since   3.4
	 */
	public function getAssetRules($asset, $recursive = false)
	{
		return Access::getAssetRules($asset, $recursive);
	}

	/**
	 * Helper wrapper method for getGroupsByUser
	 *
	 * @param   integer  $userId     Id of the user for which to get the list of groups.
	 * @param   boolean  $recursive  True to include inherited user groups.
	 *
	 * @return  array    List of user group ids to which the user is mapped.
	 *
	 * @see     Access::getGroupsByUser()
	 * @since   3.4
	 */
	public function getGroupsByUser($userId, $recursive = true)
	{
		return Access::getGroupsByUser($userId, $recursive);
	}

	/**
	 * Helper wrapper method for getUsersByGroup
	 *
	 * @param   integer  $groupId    The group Id
	 * @param   boolean  $recursive  Recursively include all child groups (optional)
	 *
	 * @return  array
	 *
	 * @see     Access::getUsersByGroup()
	 * @since   3.4
	 */
	public function getUsersByGroup($groupId, $recursive = false)
	{
		return Access::getUsersByGroup($groupId, $recursive);
	}

	/**
	 * Helper wrapper method for getAuthorisedViewLevels
	 *
	 * @param   integer  $userId  Id of the user for which to get the list of authorised view levels.
	 *
	 * @return  array    List of view levels for which the user is authorised.
	 *
	 * @see     Access::getAuthorisedViewLevels()
	 * @since   3.4
	 */
	public function getAuthorisedViewLevels($userId)
	{
		return Access::getAuthorisedViewLevels($userId);
	}

	/**
	 * Helper wrapper method for getActions
	 *
	 * @param   string  $component  The component from which to retrieve the actions.
	 * @param   string  $section    The name of the section within the component from which to retrieve the actions.
	 *
	 * @return array  List of actions available for the given component and section.
	 *
	 * @see     Access::getActions()
	 * @since   3.4
	 * @deprecated  12.3 (Platform) & 4.0 (CMS)  Use Access::getActionsFromFile or Access::getActionsFromData instead.
	 */
	public function getActions($component, $section = 'component')
	{
		return Access::getActions($component, $section);
	}

	/**
	 * Helper wrapper method for getActionsFromFile
	 *
	 * @param   string  $file   The path to the XML file.
	 * @param   string  $xpath  An optional xpath to search for the fields.
	 *
	 * @return  boolean|array   False if case of error or the list of actions available.
	 *
	 * @see     Access::getActionsFromFile()
	 * @since   3.4
	 */
	public function getActionsFromFile($file, $xpath = '/access/section[@name=\'component\']/')
	{
		return Access::getActionsFromFile($file, $xpath);
	}

	/**
	 * Helper wrapper method for getActionsFromData
	 *
	 * @param   string|SimpleXMLElement  $data   The XML string or an XML element.
	 * @param   string                   $xpath  An optional xpath to search for the fields.
	 *
	 * @return  boolean|array   False if case of error or the list of actions available.
	 *
	 * @see     Access::getActionsFromData()
	 * @since   3.4
	 */
	public function getActionsFromData($data, $xpath = '/access/section[@name=\'component\']/')
	{
		return Access::getActionsFromData($data, $xpath);
	}
}
