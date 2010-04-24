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
 * Newsfeeds master display controller.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_newsfeeds
 * @since		1.6
 */
class NewsfeedsController extends JController
{
	/**
	 * Method to display a view.
	 */
	public function display()
	{
		require_once JPATH_COMPONENT.'/helpers/newsfeeds.php';

		parent::display();

		// Load the submenu.
		NewsfeedsHelper::addSubmenu(JRequest::getWord('view', 'newsfeeds'));
	}
}