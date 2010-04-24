<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Base controller class for Menu Manager.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @version		1.6
 */
class MenusController extends JController
{
	/**
	 * Method to display a view.
	 *
	 * @return	void
	 */
	function display()
	{
		require_once JPATH_COMPONENT.'/helpers/menus.php';

		parent::display();

		// Load the submenu.
		MenusHelper::addSubmenu(JRequest::getWord('view'));
	}
}