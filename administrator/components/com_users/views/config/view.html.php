<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * The HTML Users configuration view.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @version		1.0
 */
class UsersViewConfig extends JView
{
	/**
	 * Method to display the view.
	 *
	 * @access	public
	 * @param	string	A template file to load.
	 * @return	mixed	JError object on failure, void on success.
	 * @throws	object	JError
	 * @since	1.0
	 */
	function display($tpl = null)
	{
		// Initialize variables.
		$user		= &JFactory::getUser();

		// Load the view data.
		$state		= &$this->get('State');
		$params		= &$state->get('config');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Push out the view data.
		$this->assignRef('state',	$state);
		$this->assignRef('config',	$params);


		// Render the layout.
		parent::display($tpl);
	}
}