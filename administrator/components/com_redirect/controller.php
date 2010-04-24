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
 * Redirect master display controller.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @since		1.6
 */
class RedirectController extends JController
{
	/**
	 * @var		string	The default view.
	 * @since	1.6
	 */
	protected $default_view = 'links';

	/**
	 * Method to display a view.
	 *
	 * @since	1.6
	 */
	public function display()
	{
		require_once JPATH_COMPONENT.'/helpers/redirect.php';

		parent::display();

		// Load the submenu.
		RedirectHelper::addSubmenu(JRequest::getWord('view', 'links'));
	}
}