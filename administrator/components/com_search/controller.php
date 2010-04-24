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
 * Search master display controller.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_search
 * @since		1.6
 */
class SearchController extends JController
{
	/**
	 * @var		string	The default view.
	 * @since	1.6
	 */
	protected $default_view = 'searches';

	/**
	 * Method to display a view.
	 */
	public function display()
	{
		require_once JPATH_COMPONENT.'/helpers/search.php';

		parent::display();

		// Load the submenu.
		SearchHelper::addSubmenu(JRequest::getWord('view', 'searches'));
	}
}