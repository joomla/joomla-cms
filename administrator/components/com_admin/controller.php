<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Admin
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * Admin Controller
 *
 * @package		Joomla
 * @subpackage	Admin
 * @since 1.5
 */
class AdminController extends JController
{
	/**
	 * Constructor
	 *
	 * @param	array	Configuration array
	 */
	function __construct($config = array())
	{
		parent::__construct($config);

		// Register Extra tasks
		$this->registerTask('sysinfo',		'display');
		$this->registerTask('help',		'display');
		$this->registerTask('changelog',	'display');
	}

	/**
	 * Display the view
	 */
	function display()
	{
		// Intercept URL's using the old task format
		$task	= $this->getTask();

		switch ($task)
		{
			case 'sysinfo':
			case 'help':
			case 'changelog':
				JRequest::setVar('view', $task);
				break;
		}

		if (JRequest::getVar('view')) {
			parent::display();
		}
	}

	/**
	 * TODO: Description?
	 */
	function keepalive()
	{
		return;
	}
}