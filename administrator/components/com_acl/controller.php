<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * Component Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 */
class AccessController extends JController
{
	/**
	 * Default display method
	 */
	function display()
	{
		$document	= &JFactory::getDocument();

		// Set the default view name and format from the Request
		$vName		= JRequest::getWord('view', 'rules');
		$vFormat	= $document->getType();
		$lName		= JRequest::getWord('layout', 'default');
		$type		= JRequest::getInt('type', 1);

		if ($view = &$this->getView($vName, $vFormat))
		{
			switch ($vName)
			{
				case 'group':
					$model = $this->getModel('group');
					$model->setState('group_type',	'aro');
					break;

				case 'groups':
					$model = $this->getModel('groups');
					$model->setState('list.group_type',	'aro');
					$model->setState('list.tree',		true);
					$model->setState('list.parent_id',	28);
					break;

				case 'level':
					$model = $this->getModel('group');
					$model->setState('group_type',	'axo');
					break;

				case 'levels':
					$model = $this->getModel('groups');
					$model->setState('list.group_type',	'axo');
					$model->setState('list.tree',		false);
					$model->setState('list.parent_id',	1);
					break;

				case 'rule':
					$model = $this->getModel('acl');
					$model->setState('acl_type', $type);
					break;

				case 'rules':
				default:
					$model = $this->getModel('acls');
					$model->setState('list.acl_type', $type);
					break;
			}

			// Push the model into the view (as default)
			$view->setModel($model, true);
			$view->setLayout($lName);
			$view->assignRef('document', $document);
			$view->display();
		}

		// Set up the Linkbar
		JSubMenuHelper::addEntry(JText::_('ACL Link Rules Type 1'),		'index.php?option=com_acl&view=rules&type=1',	$vName == 'rules' AND $type == 1);
		JSubMenuHelper::addEntry(JText::_('ACL Link Rules Type 2'),		'index.php?option=com_acl&view=rules&type=2',	$vName == 'rules' AND $type == 2);
		JSubMenuHelper::addEntry(JText::_('ACL Link Rules Type 3'),		'index.php?option=com_acl&view=rules&type=3',	$vName == 'rules' AND $type == 3);
		JSubMenuHelper::addEntry(JText::_('ACL Link User Groups'),		'index.php?option=com_acl&view=groups',	$vName == 'groups');
		JSubMenuHelper::addEntry(JText::_('ACL Link Access Levels'),	'index.php?option=com_acl&view=levels',	$vName == 'levels');
	}

	/**
	 * Get an instance of the controller
	 */
	function &getInstance()
	{
		// Determine the request protocol
		$protocol = JRequest::getWord('protocol');

		// Get task command from the request
		$cmd = JRequest::getVar('task', null);

		// If it was a multiple option post get the selected option
		if (is_array($cmd)) {
			$cmd = array_pop(array_keys($cmd));
		}

		// Filter the command and instantiate the appropriate controller
		$cmd = JFilterInput::clean($cmd,'cmd');
		if (strpos($cmd, '.') != false) {
			// We have a defined controller/task pair -- lets split them out
			list($controllerName, $task) = explode('.', $cmd);

			// Define the controller name and path
			$controllerName	= strtolower($controllerName);
			$controllerFile = ($protocol) ? $controllerName.'.'.$protocol : $controllerName;
			$controllerPath	= JPATH_COMPONENT.DS.'controllers'.DS.$controllerFile.'.php';

			// If the controller file path exists, include it ... else lets die with a 500 error
			if (file_exists($controllerPath)) {
				require_once $controllerPath;
			}
			else {
				JError::raiseError(500, 'Invalid Controller');
			}

			JRequest::setVar('task', $task);
		}
		else {
			// Base controller, just set the task :)
			$controllerName = null;
			$task = $cmd;
		}

		// Set the name for the controller and instantiate it
		$controllerClass = 'AccessController'.ucfirst($controllerName);

		if (class_exists($controllerClass)) {
			$controller = new $controllerClass();
		}
		else {
			JError::raiseError(500, 'Invalid Controller Class');
			$controller = null;
		}

		return $controller;
	}
}