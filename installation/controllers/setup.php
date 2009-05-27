<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die;

/**
 * Setup controller for the Joomla Core Installer.
 *
 * @package		Joomla.Installation
 * @since		1.6
 */
class JInstallationControllerSetup extends JController
{
	/**
	 * Method to set the setup language for the application.
	 *
	 * @access	public
	 * @return	void
	 * @since	1.0
	 */
	function setlanguage()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('Invalid_Token'));

		// Get the posted config options.
		$vars = JRequest::getVar('vars', array(), 'post', 'array');
		$session = & JFactory::getSession();

		// Get the setup model.
		$model = &$this->getModel('Setup');

		// Store the options in the session.
		$vars = $model->storeOptions($vars);

		// Ensure a language was set.
		if (!$vars || empty($vars['lang'])) {
			$this->setMessage(JText::_('Language_Invalid'), 'notice');
			$this->setRedirect('index.php?option=language');
		}
		else {
			$this->setRedirect('index.php?view=preinstall');
		}
	}

	function database()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('Invalid_Token'));

		// Get the posted config options.
		$vars = JRequest::getVar('vars', array(), 'post', 'array');
		$session = & JFactory::getSession();

		// Get the setup model.
		$model = & $this->getModel('Setup');

		// Store the options in the session.
		$vars = $model->storeOptions($vars);

		// Get the database model.
		$database = & $this->getModel('Database');

		// Attempt to initialize the database.
		$return = $database->initialize($vars);

		// Ensure a language was set.
		if (!$return) {
			$this->setMessage($database->getError(), 'notice');
			$this->setRedirect('index.php?view=database');
		}
		else {
			$this->setRedirect('index.php?view=filesystem');
		}
	}

	function filesystem()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('Invalid_Token'));

		// Get the posted config options.
		$vars = JRequest::getVar('vars', array(), 'post', 'array');
		$session = & JFactory::getSession();

		// Get the setup model.
		$model = & $this->getModel('Setup');

		// Store the options in the session.
		$vars = $model->storeOptions($vars);

		$this->setRedirect('index.php?view=site');
	}

	function saveconfig()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('Invalid_Token'));

		// Get the posted config options.
		$vars = JRequest::getVar('vars', array(), 'post', 'array');
		$session = & JFactory::getSession();

		// Get the setup model.
		$model = & $this->getModel('Setup');

		// Store the options in the session.
		$vars = $model->storeOptions($vars);

		// Get the configuration model.
		$configuration = & $this->getModel('Configuration');

		// Attempt to setup the configuration.
		$return = $configuration->setup($vars);

		// Ensure a language was set.
		if (!$return) {
			$this->setMessage($configuration->getError(), 'notice');
			$this->setRedirect('index.php?view=site');
		}
		else {
			$this->setRedirect('index.php?view=complete');
		}
	}
}