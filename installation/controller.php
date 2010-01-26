<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');
jimport('joomla.application.component.helper');

/**
 * Base controller class for the Joomla Core Installer.
 *
 * @package		Joomla.Installation
 * @since		1.6
 */
class JInstallationController extends JController
{
	/**
	 * Method to display a view.
	 *
	 * @return	void
	 * @since	1.0
	 */
	public function display()
	{
		// Get the current URI to redirect to.
		$uri		= &JURI::getInstance();
		$redirect	= base64_encode($uri);

		// Get the document object.
		$document = &JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName		= JRequest::getWord('view', 'language');
		$vFormat	= $document->getType();
		$lName		= JRequest::getWord('layout', 'default');

		if ($view = &$this->getView($vName, $vFormat))
		{
			switch ($vName)
			{
				default:
					$model = &$this->getModel('Setup', 'JInstallationModel', array('dbo' => null));
					break;
			}

			// Push the model into the view (as default).
			$view->setModel($model, true);
			$view->setLayout($lName);

			// Push document object into the view.
			$view->assignRef('document', $document);

			$view->display();
		}
	}

	/**
	 * Method to get the appropriate controller.
	 *
	 * @return	object	JInstallation Controller
	 * @since	1.0
	 */
	public static function &getInstance()
	{
		static $instance;

		if (!empty($instance)) {
			return $instance;
		}

		$cmd = JRequest::getCmd('task', 'display');

		// Check for a controller.task command.
		if (strpos($cmd, '.') != false)
		{
			// Explode the controller.task command.
			list($type, $task) = explode('.', $cmd);

			// Define the controller name and path
			$protocol	= JRequest::getWord('protocol');
			$type		= strtolower($type);
			$file		= (!empty($protocol)) ? $type.'.'.$protocol.'.php' : $type.'.php';
			$path		= JPATH_COMPONENT.DS.'controllers'.DS.$file;

			// If the controller file path exists, include it ... else die with a 500 error.
			if (file_exists($path)) {
				require_once $path;
			} else {
				JError::raiseError(500, JText::sprintf('Invalid_Controller', $type));
			}

			JRequest::setVar('task', $task);
		} else {
			// Base controller, just set the task.
			$type = null;
			$task = $cmd;
		}

		// Set the name for the controller and instantiate it.
		$class = 'JInstallationController'.ucfirst($type);
		if (class_exists($class)) {
			$instance = new $class();
		} else {
			JError::raiseError(500, JText::sprintf('Invalid_Controller_Class', $class));
		}

		return $instance;
	}
}
