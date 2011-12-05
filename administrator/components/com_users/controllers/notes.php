<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		2.5.0
 */
class UsersControllerNotes extends JControllerAdmin
{
	/**
	 * Proxy for getModel
	 *
	 * @return	JModel
	 * @since	1.1
	 */
	function getModel()
	{
		return parent::getModel('Note', 'UsersModel', array('ignore_request' => true));
	}
}