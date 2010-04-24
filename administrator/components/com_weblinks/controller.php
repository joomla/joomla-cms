<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Weblinks Weblink Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_weblinks
 * @since		1.5
 */
class WeblinksController extends JController
{
	/**
	 * Method to display a view.
	 *
	 * @since	1.6
	 */
	function display()
	{
		require_once JPATH_COMPONENT.'/helpers/weblinks.php';

		parent::display();

		// Load the submenu.
		WeblinksHelper::addSubmenu(JRequest::getWord('view', 'weblinks'));
	}
}