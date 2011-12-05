<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Category Subcontroller.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		2.5.0
 */
class UsersControllerNote extends JControllerForm
{
	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param	int		$recordId	The primary key id for the item.
	 * @param	string	$key		The name of the primary key variable.
	 *
	 * @return	string	The arguments to append to the redirect URL.
	 * @since	1.6
	 */
	protected function getRedirectToItemAppend($recordId = null, $key = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId, $key);

		$userId = JRequest::getInt('u_id');
		if ($userId) {
			$append .= '&u_id='.$userId;
		}

		return $append;
	}

}