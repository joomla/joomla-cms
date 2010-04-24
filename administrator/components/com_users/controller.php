<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Users master display controller.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersController extends JController
{
	/**
	 * Method to display a view.
	 */
	public function display()
	{
		require_once JPATH_COMPONENT.'/helpers/users.php';

		parent::display();

		// Load the submenu.
		UsersHelper::addSubmenu(JRequest::getWord('view', 'users'));
	}
}