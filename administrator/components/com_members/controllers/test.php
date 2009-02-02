<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Invalid Request.');

jimport( 'joomla.application.component.controller' );

/**
 * The Members Configuration Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_members
 * @version		1.6
 */
class MembersControllerTest extends JController
{
	function get_allowed_levels()
	{
		jimport('joomla.access.access');

		$user	= &JFactory::getUser();
		$acl	= new JAccess;
		$acl->quiet(false);
		$levels = $acl->getAuthorizedAccessLevels($user->id);
	}

	function assetgroups()
	{
		jimport('joomla.access.access');

		// Initialise test data
		$user	= &JFactory::getUser();
		$db = &JFactory::getDbo();
		$db->setQuery(
			'REPLACE INTO #__access_sections VALUES (1, "com_foobar", "Foo Bar", 0)'
		);
		if (!$db->query()) {
			return JError::raiseWarning(500, $db->getErrorMsg());
		}
		$db->setQuery(
			'REPLACE INTO #__access_assets VALUES (1, 1, "com_foobar", "com_foobar.1", "This is a foo bar")'
			.',(2, 1, "com_foobar", "com_foobar.2", "This is a registered foo bar")'
			.',(3, 1, "com_foobar", "com_foobar.3", "This is a special foo bar")'
		);
		if (!$db->query()) {
			return JError::raiseWarning(500, $db->getErrorMsg());
		}
		$db->setQuery(
			'REPLACE INTO #__access_asset_assetgroup_map VALUES (1, 1), (2, 2), (3, 3)'
		);
		if (!$db->query()) {
			return JError::raiseWarning(500, $db->getErrorMsg());
		}

		$user	= &JFactory::getUser();
		$acl	= new JAccess;
		$acl->quiet(false);
/*
		$acl->getAssetGroupMap(1);
		$acl->getAssetGroupMap(1, true);

		$acl->getAssetGroupMap(2);
		$acl->getAssetGroupMap(2, true);

		$acl->getAssetGroupMap(3);
		$acl->getAssetGroupMap(3, true);
*/
		$acl->check($user->id, 'core.view', 'com_foobar.1');

	}
}